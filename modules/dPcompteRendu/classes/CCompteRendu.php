<?php

/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;
use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\Logger\LoggerLevels;
use Ox\Core\Module\CModule;
use Ox\Core\Mutex\CMbMutex;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\Rgpd\CRGPDManager;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Moebius\CRisque;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\IGroupRelated;
use Ox\Mediboard\Patients\IPatientRelated;
use Ox\Mediboard\Patients\MedecinFieldService;
use Ox\Mediboard\Printing\CPrinter;
use Ox\Mediboard\Search\CSearchIndexing;
use Ox\Mediboard\Search\IIndexableObject;
use Ox\Mediboard\System\CContentHTML;
use Ox\Tamm\Cabinet\TAMMSIHFieldsReplacer;

/**
 * Gestion de documents / modèles avec marges, entêtes et pieds de pages.
 * Un modèle est associé à un utilisateur, une fonction ou un établissement.
 * Le document est une utilisation d'un modèle (référencé par modele_id)
 */
class CCompteRendu extends CDocumentItem implements IIndexableObject
{
    /** @var string */
    public const RESOURCE_TYPE = 'medicalReports';

    /** @var string */
    public const FIELDSET_AUTHOR = 'author';
    /** @var string */
    public const FIELDSET_CONTENT = 'content';

    public const CACHE_KEY_OPENER = 'edit_compte_rendu';
    public const CACHE_TTL_OPENER = 1200;

    // DB Table key
    public $compte_rendu_id;

    // DB References
    public $user_id; // not null when is a template associated to a user
    public $function_id; // not null when is a template associated to a function
    public $group_id; // not null when is a template associated to a group
    public $content_id;
    public $locker_id;
    public $header_id;
    public $footer_id;
    public $preface_id;
    public $ending_id;
    public $modele_id;
    public $signataire_id;
    public $printer_id;

    // DB fields
    public $nom;
    public $actif;
    public $type;
    public $factory;
    public $language;
    public $font;
    public $size;
    public $nb_print;
    public $valide;
    public $height;
    public $margin_top;
    public $margin_bottom;
    public $margin_left;
    public $margin_right;
    public $page_height;
    public $page_width;
    public $fast_edit;
    public $fast_edit_pdf;
    public $date_print;
    public $purge_field;
    public $purgeable;
    public $fields_missing;
    public $version;

    /** @var string */
    public $creation_date;

    /** @var string */
    public $modification_date;

    /** @var string */
    public $validation_date;

    public $signature_mandatory;
    public $alert_creation;
    public $signe;
    public $duree_lecture;
    public $duree_ecriture;

    // Form fields
    public $_is_document    = false;
    public $_is_modele      = false;
    public $_is_auto_locked = false;
    public $_is_locked      = false;
    public $_utilisations   = 0;
    public $_owner;
    public $_page_format;
    public $_orientation;
    public $_list_classes;
    public $_count_utilisation;
    public $_special_modele;
    public $_date_min;
    public $_date_max;
    public $_status;
    public $_nom;
    public $_nb_print;
    public $_date_last_use;
    public $_is_for_instance;
    public $_add_duree_lecture;
    public $_add_duree_ecriture;

    // Distant field
    public $_source;

    /** @var CMediusers */
    public $_ref_user;

    /** @var CFunctions */
    public $_ref_function;

    /** @var CGroups */
    public $_ref_group;

    /** @var CCompteRendu */
    public $_ref_header;

    /** @var CCompteRendu */
    public $_ref_preface;

    /** @var CCompteRendu */
    public $_ref_ending;

    /** @var CCompteRendu */
    public $_ref_footer;

    /** @var CFile */
    public $_ref_file;

    /** @var CCompteRendu */
    public $_ref_modele;

    /** @var CContentHTML */
    public $_ref_content;

    /** @var CMediusers */
    public $_ref_locker;

    /** @var CPrinter */
    public $_ref_printer;

    /** @var CCorrespondantCourrier[] */
    public $_refs_correspondants_courrier;
    public $_refs_correspondants_courrier_by_tag_guid = [];

    /** @var CPatient */
    public $_ref_patient;

    /** @var CStatutCompteRendu[] */
    public $_ref_statut_compte_rendu;

    /** @var CStatutCompteRendu */
    public $_ref_last_statut_compte_rendu;
    // Other fields
    public $_entire_doc;
    public $_ids_corres;
    public $_page_ordonnance;
    public $_is_dompdf;

    public static $import = false;

    static $_page_formats = [
        "a3"      => [29.7, 42],
        "a4"      => [21, 29.7],
        "a5"      => [14.8, 21],
        "a6"      => [10.5, 14.8],
        "letter"  => [21.6, 27.9],
        "legal"   => [21.6, 35.6],
        "tabloid" => [27.9, 43.2],
    ];

    static $templated_classes = null;

    static $fonts = [
        ""             => "", // empty font
        "arial"        => "Arial",
        "carlito"      => "Carlito",
        "comic"        => "Comic Sans MS",
        "courier"      => "Courier New",
        "georgia"      => "Georgia",
        "lucida"       => "Lucida Sans Unicode",
        "symbol"       => "Symbol",
        "tahoma"       => "Tahoma",
        "times"        => "Times New Roman",
        "trebuchet"    => "Trebuchet MS",
        "verdana"      => "Verdana",
        "zapfdingbats" => "ZapfDingBats",
    ];

    // Liste des chapitres concernés par l'impression des bons
    static $_chap_bons = ["anapath", "biologie", "imagerie", "consult", "kine"];

    /**
     * Noms de modèles réservés
     *
     * Use static::getSpecialNames
     *
     * @var array
     */
    static private $special_names = [
        "CConsultation"         => [
            "[ENTETE RDV FUTURS]"       => "header",
            "[PIED DE PAGE RDV FUTURS]" => "footer",
        ],
        "CConsultAnesth"        => [
            "[FICHE ANESTH]" => "body",
        ],
        "COperation"            => [
            "[FICHE DHE]"    => "body",
            "[BON ANAPATH]"  => "body",
            "[BON BACTERIO]" => "body",
        ],
        "CPrescription"         => [
            "[ENTETE ORDONNANCE]"           => "header",
            "[PIED DE PAGE ORDONNANCE]"     => "footer",
            "[ENTETE ORDONNANCE ALD]"       => "header",
            "[PIED DE PAGE ORDONNANCE ALD]" => "footer",
            "[ENTETE BON]"                  => "header",
            "[PIED DE PAGE BON]"            => "footer",
        ],
        "CSejour"               => [
            "[ENTETE OBJECTIFS SOINS]"       => "header",
            "[PIED DE PAGE OBJECTIFS SOINS]" => "footer",
            "[AFFICHAGE PRESTATIONS]"        => "body",
        ],
        "CFactureCabinet"       => [
            "[ENTETE FACTURE CABINET]"    => "header",
            "[PIED DE PAGE FACT CABINET]" => "footer",
            "[FACTURE BVR]"               => "body",
        ],
        "CFactureEtablissement" => [
            "[ENTETE FACTURE ETAB]"    => "header",
            "[PIED DE PAGE FACT ETAB]" => "footer",
            "[FACTURE BVR]"            => "body",
        ],
        "CFactureAvoir"         => [
            "[AVOIR]" => "body",
        ],
        "CPatient"              => [
            "[ENTETE MOZAIC]"       => "header",
            "[PIED DE PAGE MOZAIC]" => "footer",
        ],
        'CDevisCodage'          => [
            '[DEVIS]' => 'body',
        ],
        "CRelance"              => [
            "[ENTETE RELANCE]"       => "header",
            "[PIED DE PAGE RELANCE]" => "footer",
        ],
        "CGroups"               => [
            "[ENTETE CODE ACTIVATION]"       => "header",
            "[PIED DE PAGE CODE ACTIVATION]" => "footer",
        ],
    ];

    static $fields_exclude_export = [
        "compte_rendu_id",
        "user_id",
        "function_id",
        "group_id",
        "author_id",
        "content_id",
        "listes",
    ];


    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec              = parent::getSpec();
        $spec->table       = 'compte_rendu';
        $spec->key         = 'compte_rendu_id';
        $spec->measureable = true;

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    public function getProps(): array
    {
        $props                        = parent::getProps();
        $props["user_id"]             = "ref class|CMediusers purgeable show|0 back|modeles fieldset|author";
        $props["function_id"]         = "ref class|CFunctions purgeable back|compte_rendu fieldset|author";
        $props["group_id"]            = "ref class|CGroups purgeable back|modeles fieldset|author";
        $props["author_id"]           .= " back|documents_crees";
        $props["object_id"]           = "ref class|CMbObject cascade meta|object_class purgeable show|1 back|documents";
        $props["content_id"]          = "ref class|CContentHTML show|0 back|compte_rendus fieldset|content";
        $props["file_category_id"]    .= " back|categorized_documents";
        $props["object_class"]        = "str notNull class show|0";
        $props["nom"]                 = "str notNull show|0 seekable fieldset|default";
        $props["actif"]               = "bool default|1 show|0 fieldset|default";
        $props["font"]                = "enum list|arial|carlito|comic|courier|georgia|lucida|symbol|" .
            "tahoma|times|trebuchet|verdana|zapfdingbats show|0";
        $props["size"]                = "enum list|xx-small|x-small|small|medium|large|x-large|xx-large|" .
            "8pt|9pt|10pt|11pt|12pt|14pt|16pt|18pt|20pt|22pt|24pt|26pt|28pt|36pt|48pt|72pt show|0";
        $props["type"]                = "enum list|header|preface|body|ending|footer default|body show|0 fieldset|default";
        $props["factory"]             = "enum list|CDomPDFConverter|CWkHtmlToPDFConverter default|CWkHtmlToPDFConverter show|0";
        $props["language"]            = "enum list|en-EN|es-ES|fr-CH|fr-FR default|fr-FR show|0";
        $props["locker_id"]           = "ref class|CMediusers purgeable back|compte_rendu";
        $props["header_id"]           = "ref class|CCompteRendu show|0 back|modeles_headed";
        $props["footer_id"]           = "ref class|CCompteRendu show|0 back|modeles_footed";
        $props["preface_id"]          = "ref class|CCompteRendu show|0 back|modeles_prefaced";
        $props["ending_id"]           = "ref class|CCompteRendu show|0 back|modeles_ended";
        $props["modele_id"]           = "ref class|CCompteRendu nullify show|0 back|documents_generated";
        $props["signataire_id"]       = "ref class|CMediusers purgeable back|docs_signataires";
        $props["printer_id"]          = "ref class|CPrinter back|documents";
        $props["signature_mandatory"] = "bool default|0 show|0";
        $props["alert_creation"]      = "bool default|0 show|0";
        $props["height"]              = "float min|0 show|0";
        $props["margin_top"]          = "float notNull min|0 default|2 show|0";
        $props["margin_bottom"]       = "float notNull min|0 default|2 show|0";
        $props["margin_left"]         = "float notNull min|0 default|2 show|0";
        $props["margin_right"]        = "float notNull min|0 default|2 show|0";
        $props["page_height"]         = "float notNull min|1 default|29.7 show|0";
        $props["page_width"]          = "float notNull min|1 default|21 show|0";
        $props["nb_print"]            = "num default|1 min|0 show|0";
        $props["valide"]              = "bool show|0";
        $props["fast_edit"]           = "bool default|0 show|0";
        $props["fast_edit_pdf"]       = "bool default|0 show|0";
        $props["date_print"]          = "dateTime show|0";
        $props["purge_field"]         = "str show|0";
        $props["purgeable"]           = "bool default|0 show|0";
        $props["fields_missing"]      = "num default|0 show|0";
        $props["version"]             = "num default|0 show|0";
        $props["creation_date"]       = "dateTime";
        $props["modification_date"]   = "dateTime";
        $props["validation_date"]     = "dateTime show|0";
        $props["signe"]               = "bool show|0";
        $props['duree_lecture']       = 'num min|0 default|0';
        $props['duree_ecriture']      = 'num min|0 default|0';
        // Form fields
        $props["_list_classes"]  = "enum list|" . implode("|", array_keys(self::getTemplatedClasses()));
        $props["_is_locked"]     = "bool default|0";
        $props["_owner"]         = "enum list|prat|func|etab";
        $props["_orientation"]   = "enum list|portrait|landscape";
        $props["_page_format"]   = "enum list|" . implode("|", array_keys(self::$_page_formats));
        $props["_source"]        = "html helped|_list_classes";
        $props["_entire_doc"]    = "html";
        $props["_ids_corres"]    = "str";
        $props["_file_size"]     = "str show|0";
        $props["_date_min"]      = "date";
        $props["_date_max"]      = "date";
        $props["_status"]        = "enum list|signe|non_signe|sent";
        $props["_nom"]           = "str";
        $props["_date_last_use"] = "date";

        return $props;
    }

    static function getSpecialNames()
    {
        // Ajout des en-têtes de bons pour chacun des chapitres
        foreach (CCompteRendu::$_chap_bons as $chapitre) {
            $maj_chap                                                                     = strtoupper($chapitre);
            CCompteRendu::$special_names["CPrescription"]["[ENTETE BON $maj_chap]"]       = "header";
            CCompteRendu::$special_names["CPrescription"]["[PIED DE PAGE BON $maj_chap]"] = "footer";
        }

        foreach (CRGPDManager::getCompliantClasses() as $_class) {
            CCompteRendu::$special_names[$_class][CRGPDManager::SPECIAL_MODEL_NAME] = 'body';
        }

        if (CModule::getActive("psl")) {
            CCompteRendu::$special_names["CPrescription"]["[ENTETE ORDONNANCE PSL]"]       = "header";
            CCompteRendu::$special_names["CPrescription"]["[PIED DE PAGE ORDONNANCE PSL]"] = "footer";
        }

        return static::$special_names;
    }

    /**
     * Génère et retourne le fichier PDF si possible,
     * la source html sinon.
     *
     * @param bool $load_content load content
     * @param bool $auto_print   auto print
     *
     * @return string
     */
    public function getBinaryContent($load_content = true, $auto_print = true): ?string
    {
        // Content from PDF preview
        $this->makePDFpreview(null, $auto_print, $load_content);
        $file = $this->_ref_file;

        if (isset($file) && ($file->_id || !$load_content)) {
            return $file->getBinaryContent();
        }

        // Or actual HTML source
        if ($load_content) {
            $this->loadContent();
        }

        return $this->_source;
    }

    /**
     * Retourne le nom du fichier associé
     *
     * @return string
     */
    public function getExtensioned(): ?string
    {
        $file = $this->loadFile();
        if ($file->_id) {
            $this->_extensioned = $file->_extensioned;
        }

        return parent::getExtensioned();
    }

    /**
     * @see parent::updateFormFields()
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();
        $this->_extensioned = "$this->nom.htm";
        $this->_view        = $this->object_id ? "" : "Modèle : ";
        $this->_view        .= $this->nom;

        $this->_file_date = $this->creation_date;

        if (CAppUI::pref("show_creation_date")) {
            $this->_view .= " (" . CMbDT::transform(null, $this->creation_date, "%d/%m/%y") . ")";
        }

        $this->_is_dompdf = $this->factory === "CDomPDFConverter";

        if ($this->object_id) {
            $modele = $this->loadModele();

            if ($modele->_id && $modele->purgeable) {
                $this->_view = "[temp] " . $this->_view;
            }
        }

        if ($this->object_id && $this->fields_missing) {
            $this->_view = " [" . $this->fields_missing . "] $this->_view";
        }

        if ($this->user_id) {
            $this->_owner = "prat";
        }
        if ($this->function_id) {
            $this->_owner = "func";
        }
        if ($this->group_id) {
            $this->_owner = "etab";
        }
        if (!$this->object_id && !$this->user_id && !$this->function_id && !$this->group_id) {
            $this->_owner = "instance";
        }

        $this->isForInstance();

        $this->_page_format = "";

        foreach (self::$_page_formats as $_key => $_format) {
            if (($_format[0] == $this->page_width && $_format[1] == $this->page_height)
                || ($_format[1] == $this->page_width && $_format[0] == $this->page_height)
            ) {
                $this->_page_format = $_key;
                break;
            }
        }

        // Formatage de la page
        if (!$this->_page_format) {
            $page_width         = round((72 / 2.54) * $this->page_width, 2);
            $page_height        = round((72 / 2.54) * $this->page_height, 2);
            $this->_page_format = [0, 0, $page_width, $page_height];
        }

        $this->_orientation = "portrait";

        if ($this->page_width > $this->page_height) {
            $this->_orientation = "landscape";
        }

        // Le champ valide stocke le user_id de la personne qui l'a verrouillé
        if ($this->_id && $this->valide && !$this->locker_id) {
            $log             = $this->loadLastLogForField("valide");
            $this->locker_id = $log->user_id;
        }

        $special_names         = static::getSpecialNames();
        $this->_special_modele = isset($special_names[$this->object_class][$this->nom]);

        $this->_version = $this->version;

        $this->_is_document = $this->object_id ? true : false;
        $this->_is_modele   = !$this->_is_document;
    }

    /**
     * Load locker
     *
     * @return CMediusers
     */
    function loadRefLocker()
    {
        return $this->_ref_locker = $this->loadFwdRef("locker_id", true);
    }

    /**
     * Charge le contenu html
     *
     * @param boolean $field_source [optional]
     *
     * @return CContentHTML
     */
    function loadContent($field_source = true)
    {
        /** @var  CContentHTML $content */
        $content            = $this->loadFwdRef("content_id", true);
        $this->_ref_content = $content;

        if (is_string($content->content)) {
            $html = $content->content;
            $html = preg_replace("/#body\s*{\s*padding/", "body { margin", $html);
            $html = preg_replace("/#39/", "#039", $html);

            // Supprimer les sauts de pages dans les entêtes et pieds de pages
            if (in_array($this->type, ['header', 'footer'])) {
                $html = str_ireplace('<hr class="pagebreak" />', '', $html);
            }

            $content->content = $html;
        }

        // Passage de la date de dernière modification du content dans la table compte_rendu
        if (!$content->last_modified && $content->_id) {
            $last_log = $content->loadLastLog();
            if (!$last_log->_id) {
                $last_log = $this->loadLastLog();
            }
            $content->last_modified = $last_log->date;
            $content->store();
        }

        if ($field_source) {
            $this->_source = $content->content;

            // Suppression des commentaires, provenant souvent de Word
            $this->_source = preg_replace("/<!--.+?-->/s", "", $this->_source ?? '');
            if (preg_match("/mso-style/", $this->_source)) {
                $xml = new DOMDocument('1.0', 'iso-8859-1');
                $str = "<div>" . CMbString::convertHTMLToXMLEntities($this->_source) . "</div>";
                @$xml->loadXML(utf8_encode($str));

                $xpath    = new DOMXpath($xml);
                $elements = $xpath->query("*/style");

                if ($elements != null) {
                    foreach ($elements as $_element) {
                        if (preg_match("/(header|footer)/", $_element->nodeValue) == 0) {
                            $_element->parentNode->removeChild($_element);
                        }
                    }
                }
                $this->_source = substr($xml->saveHTML(), 5, -7);

                // La fonction saveHTML ne ferme pas les tags br, hr, img et input
                $this->_source = preg_replace("/<(br|hr|img|input)([^>]*)>/", "<$1$2 />", $this->_source);
            }
        }

        return $this->_ref_content;
    }

    /**
     * Charge les composants d'un modèle
     *
     * @return void
     */
    function loadComponents()
    {
        $this->_ref_header = $this->loadFwdRef("header_id", true);
        $this->_ref_footer = $this->loadFwdRef("footer_id", true);
        $this->loadIntroConclusion();
    }

    /**
     * Charge l'introduction et la conclusion
     *
     * @return void
     */
    function loadIntroConclusion()
    {
        $this->_ref_preface = $this->loadFwdRef("preface_id", true);
        $this->_ref_ending  = $this->loadFwdRef("ending_id", true);
    }

    /**
     * Charge le modèle de référence du document
     *
     * @return CCompteRendu
     */
    function loadModele($cache = true)
    {
        return $this->_ref_modele = $this->loadFwdRef("modele_id", $cache);
    }

    /**
     * Charge le fichier unique d'un document / modèle
     *
     * @return CFile
     */
    function loadFile()
    {
        return $this->_ref_file = $this->loadUniqueBackRef("files");
    }

    /**
     * Charge l'utilisateur associé au modèle
     *
     * @return CMediusers
     */
    function loadRefUser()
    {
        return $this->_ref_user = $this->loadFwdRef("user_id", true);
    }

    /**
     * Charge la fonction associée au modèle
     *
     * @return CFunctions
     */
    function loadRefFunction()
    {
        return $this->_ref_function = $this->loadFwdRef("function_id", true);
    }

    /**
     * Charge l'établissement associé au modèle
     *
     * @return CGroups
     */
    function loadRefGroup()
    {
        return $this->_ref_group = $this->loadFwdRef("group_id", true);
    }

    /**
     * Charge l'imprimante associée au modèle
     */
    function loadRefPrinter()
    {
        return $this->_ref_printer = $this->loadFwdRef("printer_id", true);
    }

    function loadRefStatutCompteRendu()
    {
        return $this->_ref_statut_compte_rendu = $this->loadBackRefs("statut_compte_rendu");
    }

    /**
     * @return CStatutCompteRendu|null
     * @throws Exception
     */
    public function loadLastRefStatutCompteRendu(): ?CMbObject
    {
        return $this->_ref_last_statut_compte_rendu = $this->loadLastBackRef("statut_compte_rendu","datetime ASC");
    }
    /**
     * @see parent::loadRefsFwd()
     */
    public function loadRefsFwd(): void
    {
        parent::loadRefsFwd();

        $object = $this->_ref_object;

        // Utilisateur
        if ($this->user_id) {
            $this->loadRefUser();
        } elseif ($this->object_id) {
            switch ($this->object_class) {
                case "CConsultation":
                case "CSejour":
                case "COperation":
                case "CFactureCabinet":
                case "CFactureEtablissement":
                    $this->_ref_user = $object->loadRefPraticien();
                    break;
                case "CConsultAnesth":
                    $this->_ref_user = $object->loadRefConsultation()->loadRefPraticien();
            }
        } else {
            $this->_ref_user = new CMediusers();
        }

        // Fonction
        $this->loadRefFunction();

        // Etablissement
        $this->loadRefGroup();
    }

    function getNbPrint()
    {
        $this->_nb_print = $this->date_print ?
            0 : (($this->_ref_modele && $this->_ref_modele->nb_print) ? $this->_ref_modele->nb_print : 1);
    }

    /**
     * Charge les modèles par catégorie
     *
     * @param string  $catName nom de la catégorie
     * @param array   $where1  [optional]
     * @param string  $order   [optional]
     * @param boolean $horsCat [optional]
     *
     * @return array
     */
    static function loadModeleByCat($catName, $where1 = null, $order = "nom", $horsCat = null)
    {
        $ds    = CSQLDataSource::get("std");
        $where = [
            "actif" => "= '1'",
        ];
        if (is_array($catName)) {
            $where = array_merge($where, $catName);
        } elseif (is_string($catName)) {
            $where["nom"] = $ds->prepare("= %", $catName);
        }
        $category       = new CFilesCategory;
        $resultCategory = $category->loadList($where);
        $documents      = [];

        if (count($resultCategory) || $horsCat) {
            $where = [];
            if ($horsCat) {
                $resultCategory[0] = "";
                $where[]           = "file_category_id IS NULL OR file_category_id " .
                    CSQLDataSource::prepareIn(array_keys($resultCategory));
            } else {
                $where["file_category_id"] = CSQLDataSource::prepareIn(array_keys($resultCategory));
            }
            $where["object_id"] = " IS NULL";
            if ($where1) {
                if (is_array($where1)) {
                    $where = array_merge($where, $where1);
                } elseif (is_string($where1)) {
                    $where[] = $where1;
                }
            }
            $resultDoc = new self();
            $documents = $resultDoc->loadList($where, $order);
        }

        return $documents;
    }

    /**
     * Charge les correspondants d'un document
     *
     * @param array $where
     *
     * @return array
     */
    function loadRefsCorrespondantsCourrier(array $where = [])
    {
        return $this->_refs_correspondants_courrier = $this->loadBackRefs(
            "correspondants_courrier", null, null, 'correspondant_courrier.correspondant_courrier_id', null, null, 'correspondants_courrier', $where
        );
    }

    /**
     * Charge les correspondants d'un document triés par tag puis par cible
     *
     * @return void
     */
    function loadRefsCorrespondantsCourrierByTagGuid()
    {
        if (!$this->_refs_correspondants_courrier) {
            $this->loadRefsCorrespondantsCourrier();
        }
        foreach ($this->_refs_correspondants_courrier as $_corres) {
            $guid                                                                  = "$_corres->object_class-$_corres->object_id";
            $this->_refs_correspondants_courrier_by_tag_guid[$_corres->tag][$guid] = $_corres;
        }
    }

    /**
     * Fusion de correspondants
     *
     * @param array $destinataires           tableau de destinataires
     * @param array $medecin_exercice_places tableau des lieux d'exercices
     * @return void
     */
    function mergeCorrespondantsCourrier(&$destinataires, $medecin_exercice_places = [])
    {
        $this->loadRefsCorrespondantsCourrierByTagGuid();

        if (!isset($this->_refs_correspondants_courrier_by_tag_guid["correspondant"])) {
            return;
        }

        /** @var CCorrespondantCourrier[] $correspondants */
        $correspondants = $this->_refs_correspondants_courrier_by_tag_guid["correspondant"];

        if (!isset($destinataires["CMedecin"])) {
            $destinataires["CMedecin"] = [];
        }

        foreach ($this->_refs_correspondants_courrier_by_tag_guid as $_correspondants_by_tag) {
            foreach ($_correspondants_by_tag as $_correspondant) {
                // Mise à jour du lieu d'exercice par rapport aux correpondants qui ont été cochés
                // dans la modale de gestion des correspondants d'un document
                if (isset($destinataires['CMedecin'][$_correspondant->object_id])) {
                    $medecin_exercice_place_id = $_correspondant->medecin_exercice_place_id;
                    if (
                        isset($medecin_exercice_places[$_correspondant->_id])
                        && $medecin_exercice_places[$_correspondant->_id]
                    ) {
                        $medecin_exercice_place_id = $medecin_exercice_places[$_correspondant->_id];
                    }

                    $_correspondant->medecin_exercice_place_id = $medecin_exercice_place_id;
                    $destinataires['CMedecin'][$_correspondant->object_id]->medecin_exercice_place_id =
                        $medecin_exercice_place_id;

                    $_medecin_service = new MedecinFieldService(
                        $_correspondant->loadTargetObject(),
                        $_correspondant->loadRefMedecinExercicePlace()
                    );

                    $destinataires['CMedecin'][$_correspondant->object_id]->adresse = $_medecin_service->getAdresse();
                    $destinataires['CMedecin'][$_correspondant->object_id]->cpville =
                        "{$_medecin_service->getCP()} {$_medecin_service->getVille()}";
                }
            }
        }

        $keys_corres = array_keys($destinataires["CMedecin"]);

        foreach ($correspondants as $key => $_correspondant) {
            if (!array_key_exists($key, $keys_corres)) {
                /** @var CMedecin $_medecin */
                $_medecin = $_correspondant->loadTargetObject();
                $_medecin->loadSalutations();
                $_medecin->getExercicePlaces();

                $_medecin_service = new MedecinFieldService(
                    $_medecin,
                    $_correspondant->loadRefMedecinExercicePlace()
                );

                $dest                   = new CDestinataire("correspondant");
                $dest->nom              = $_medecin->_view;
                $dest->adresse          = $_medecin_service->getAdresse();
                $dest->cpville          = "{$_medecin_service->getCP()} {$_medecin_service->getVille()}";
                $dest->email            = $_medecin->email;
                $dest->object_guid      = $_medecin->_guid;
                $dest->starting_formula = $_medecin->_starting_formula;
                $dest->closing_formula  = $_medecin->_closing_formula;
                $dest->tutoiement       = $_medecin->_tutoiement;
                $dest->medecin_exercice_place_id = $_correspondant->medecin_exercice_place_id;
                $dest->_ref_medecin     = $_medecin;

                $destinataires["CMedecin"][$_medecin->_id] = $dest;
            }
        }
    }

    /**
     * Charge tous les modèles pour une classe d'objets associés à un utilisateur
     *
     * @param integer $id           Identifiant du propriétaire
     * @param string  $owner        Type de propriétaire du modèle: prat, func ou etab
     * @param string  $object_class Nom de la classe d'objet, optionnel. Doit être un CMbObject
     * @param string  $type         Type de composant, optionnel
     * @param bool    $fast_edit    Inclue les modèles en édition rapide
     * @param string  $order        Ordre de tri de la liste
     * @param bool    $actif        Statut des modèles
     *
     * @return CCompteRendu[][] Par propriétaire: prat => CCompteRendu[], func => CCompteRendu[], etab => CCompteRendu[]
     */
    static function loadAllModelesFor(
        $id,
        $owner = 'prat',
        $object_class = null,
        $type = null,
        $fast_edit = true,
        $order = "",
        $actif = ""
    ) {
        // Accès aux modèles de la fonction et de l'établissement
        $module          = CModule::getActive("dPcompteRendu");
        $is_admin        = $module && $module->canAdmin();
        $is_praticien    = CMediusers::get()->isPraticien();
        $access_function = $is_admin || CAppUI::gconf(
                "dPcompteRendu CCompteRenduAcces access_function"
            ) || ($type && $type !== "body") || $is_praticien;
        $access_group    = $is_admin || CAppUI::gconf(
                "dPcompteRendu CCompteRenduAcces access_group"
            ) || ($type && $type !== "body") || $is_praticien;
        $modeles         = [
            "prat" => [],
        ];
        if ($access_function) {
            $modeles["func"] = [];
        }
        if ($access_group) {
            $modeles["etab"] = [];
        }

        if (!$id) {
            return $modeles;
        }

        // Clauses de recherche
        $modele             = new self();
        $where              = [];
        $where["object_id"] = "IS NULL";

        if ($object_class) {
            $where["object_class"] = "= '$object_class'";
        }
        if ($type) {
            $where["type"] = "= '$type'";
        }
        if (!$fast_edit) {
            $where["fast_edit"]     = " = '0'";
            $where["fast_edit_pdf"] = " = '0'";
        }
        if (!$order) {
            $order = "object_class, type, nom";
        }
        if ($actif !== "") {
            $where["actif"] = "= '$actif'";
        }

        switch ($owner) {
            case "prat": // Modèle du praticien
                $prat = new CMediusers();
                if (!$prat->load($id)) {
                    return $modeles;
                }
                $prat->loadRefFunction();

                $where["user_id"]     = "= '$prat->_id'";
                $where["function_id"] = "IS NULL";
                $where["group_id"]    = "IS NULL";
                $modeles["prat"]      = $modele->loadListWithPerms(PERM_READ, $where, $order);

                if ($access_function) {
                    $sec_func = $prat->loadRefsSecondaryFunctions();
                    foreach ($sec_func as $_func) {
                        $where["user_id"]              = "IS NULL";
                        $where["function_id"]          = "= '$_func->_id'";
                        $where["group_id"]             = "IS NULL";
                        $modeles["func"] = array_merge(
                            $modeles['func'],
                            $modele->loadListWithPerms(PERM_READ, $where, $order)
                        );
                    }
                }

            case "func": // Modèle de la fonction
                if (isset($modeles["func"])) {
                    if (isset($prat)) {
                        $func_id = $prat->function_id;
                    } else {
                        $func = new CFunctions();
                        if (!$func->load($id)) {
                            return $modeles;
                        }
                        $func_id = $func->_id;
                    }

                    $where["user_id"]     = "IS NULL";
                    $where["function_id"] = "= '$func_id'";
                    $where["group_id"]    = "IS NULL";
                    $modeles["func"]      = $modele->loadListWithPerms(PERM_READ, $where, $order);
                }

            case "etab": // Modèle de l'établissement
                if (isset($modeles["etab"])) {
                    $etab_id = CGroups::loadCurrent()->_id;
                    if ($owner == 'etab') {
                        $etab = new CGroups();
                        if (!$etab->load($id)) {
                            return $modeles;
                        }
                        $etab_id = $etab->_id;
                    } elseif (isset($func)) {
                        $etab_id = $func->group_id;
                    } elseif (isset($func_id)) {
                        $func = new CFunctions();
                        $func->load($func_id);

                        $etab_id = $func->group_id;
                    }

                    $where["user_id"]     = "IS NULL";
                    $where["function_id"] = "IS NULL";
                    $where["group_id"]    = " = '$etab_id'";
                    $modeles["etab"]      = $modele->loadListWithPerms(PERM_READ, $where, $order);
                }

            case "instance":
                $modeles["instance"] = [];

                $where["user_id"]     = "IS NULL";
                $where["function_id"] = "IS NULL";
                $where["group_id"]    = "IS NULL";
                $modeles["instance"]  = $modele->loadListWithPerms(PERM_READ, $where, $order);
                break;

            default:
                trigger_error("Wrong type '$owner'", E_WARNING);
        }

        return $modeles;
    }

    /**
     * @see parent::loadView()
     */
    function loadView()
    {
        parent::loadView();
        $this->loadContent();
        $this->loadLastRefStatutCompteRendu();
        $this->loadFileDeliveriesMessage();
    }

    /**
     * @inheritdoc
     */
    function getPerm($permType): bool
    {
        if (!($this->_ref_user || $this->_ref_function || $this->_ref_group) || !$this->_ref_object) {
            $this->loadRefsFwd();
        }

        $parentPerm = parent::getPerm($permType);

        if (!$this->_id) {
            return $parentPerm;
        }

        if ($this->_id && ($this->author_id == CMediusers::get()->_id)) {
            return $parentPerm;
        }

        if ($this->_ref_object->_id) {
            $parentPerm = $parentPerm && $this->_ref_object->getPerm($permType);
        } else {
            if ($this->_ref_user->_id) {
                $parentPerm = $parentPerm && $this->_ref_user->getPerm($permType);
            }
            if ($this->_ref_function->_id) {
                $parentPerm = $parentPerm && $this->_ref_function->getPerm($permType);
            }
            if ($this->_ref_group->_id) {
                $parentPerm = $parentPerm && $this->_ref_group->getPerm($permType);
            }
        }

        return $parentPerm;
    }

    /**
     * Vérification du droit de créer un document au sein d'un contexte donné
     *
     * @param CMbObject $object Contexte de création du Document
     *
     * @return bool Droit de création d'un document
     */
    static function canCreate(CMbObject $object)
    {
        $cr = new self();

        return $object->canRead() && $cr->canClass()->edit;
    }

    /**
     * Vérification du droit de duplication d'un modèle
     *
     * @return bool
     */
    function canDuplicate()
    {
        $this->loadTargetObject();

        return self::canCreate($this->_ref_object);
    }

    /**
     * Vérification du droit de verouillage du document
     *
     * @return bool Droit de verrouillage
     */
    function canLock()
    {
        if (!$this->_id) {
            return false;
        }

        return $this->canEdit();
    }

    /**
     * Vérification du droit de déverouillage du document
     *
     * @return bool Droit de déverrouillage
     */
    function canUnLock()
    {
        if (!$this->_id) {
            return false;
        }
        if ($this->isAutoLock()) {
            return false;
        }
        if (CMediusers::get()->isAdmin()) {
            return true;
        }
        if (CMediusers::get()->_id == $this->locker_id) {
            return true;
        }

        return false;
    }

    /**
     * Vérification de l'état de verrouillage automatique
     *
     * @return bool Etat de verrouillage automatique du document
     */
    function isAutoLock()
    {
        $this->_is_auto_locked = false;
        switch ($this->object_class) {
            case "CConsultation":
                $fix_edit_doc = CAppUI::gconf("dPcabinet CConsultation fix_doc_edit");
                if ($fix_edit_doc) {
                    $consult = $this->loadTargetObject();
                    $consult->loadRefPlageConsult();
                    $this->_is_auto_locked = CMbDT::dateTime(
                            "+ 24 HOUR",
                            "{$consult->_date} {$consult->heure}"
                        ) < CMbDT::dateTime();
                }
                break;
            case "CConsultAnesth":
                $fix_edit_doc = CAppUI::gconf("dPcabinet CConsultation fix_doc_edit");
                if ($fix_edit_doc) {
                    $consult = $this->loadTargetObject()->loadRefConsultation();
                    $consult->loadRefPlageConsult();
                    $this->_is_auto_locked = CMbDT::dateTime(
                            "+ 24 HOUR",
                            "{$consult->_date} {$consult->heure}"
                        ) < CMbDT::dateTime();
                }
                break;
            default:
                $this->_is_auto_locked = false;
        }
        if (!$this->_is_auto_locked && $this->object_class !== 'CElementPrescription') {
            $this->loadContent();
            $days                  = CAppUI::gconf("dPcompteRendu CCompteRendu days_to_lock");
            $this->_is_auto_locked = CMbDT::daysRelative($this->_ref_content->last_modified, CMbDT::dateTime()) > $days;
        }

        return $this->_is_auto_locked;
    }

    /**
     * Vérification de l'état de verrouillage du document
     *
     * @return bool Etat de verrouillage du document
     */
    function isLocked()
    {
        if (!$this->_id) {
            return false;
        }

        return $this->_is_locked = $this->isAutoLock() || $this->valide;
    }

    /**
     * Vérifie si l'enregistrement du modèle est possible.
     *
     * @return string
     */
    function check()
    {
        $this->completeField("type", "header_id", "footer_id", "object_class");
        // Si c'est un entête ou pied, et utilisé dans des documents dont le type ne correspond pas au nouveau
        // alors pas d'enregistrement
        if (in_array($this->type, ["footer", "header"])) {
            $doc   = new self();
            $where = 'object_class != "' . $this->object_class .
                '" and (header_id ="' . $this->_id .
                '" or footer_id ="' . $this->_id . '")' .
                '  and object_id IS NULL';
            if ($doc->countList($where)) {
                return "Des documents sont rattachés à ce pied de page (ou entête) et ils ont un type différent";
            }
        }
        // Si c'est un document dont le type de l'en-tête, de l'introduction, de la conclusion
        // ou du pied de page ne correspond pas à son nouveau type, alors pas d'enregistrement
        if (!$this->object_id && $this->type == "body") {
            $this->loadComponents();
            if ($this->header_id) {
                if ($this->_ref_header->object_class != $this->object_class) {
                    return "Le document n'est pas du même type que son entête";
                }
            }

            if ($this->footer_id) {
                if ($this->_ref_footer->object_class != $this->object_class) {
                    return "Le document n'est pas du même type que son pied de page";
                }
            }

            if ($this->preface_id) {
                if ($this->_ref_preface->object_class != $this->object_class) {
                    return "Le document n'est pas du même type que son introduction";
                }
            }

            if ($this->ending_id) {
                if ($this->_ref_ending->object_class != $this->object_class) {
                    return "Le document n'est pas du même type que sa conclusion";
                }
            }
        }

        return parent::check();
    }

    /**
     * @inheritdoc
     */
    public function store(): ?string
    {
        $this->completeField(
            "content_id",
            "_source",
            "language",
            "version",
            "factory",
            "object_id",
            'object_class',
            'duree_lecture',
            'duree_ecriture',
            'file_category_id',
            'creation_date'
        );

        if (!$this->object_id && $msg = self::checkOwner($this)) {
            return $msg;
        }

        if (!self::$import || !$this->creation_date) {
            $this->creation_date     = CMbDT::dateTime();

            if ($this->_id) {
                $this->creation_date     = $this->loadFirstLog()->date;
            }
        }

        $is_new = (!!!$this->_id);
        $field_valide = $this->fieldModified("valide");
        // Prevent source modification when not editing the doc
        $this->loadContent($this->_send || $this->_source === null);

        $this->_source = (new CompteRenduFieldReplacer($this->_source))->getSource();

        $content_modified = $this->_ref_content->content != $this->_source;

        $source_modified = $content_modified ||
            $this->fieldModified("margin_top") ||
            $this->fieldModified("margin_left") ||
            $this->fieldModified("margin_right") ||
            $this->fieldModified("margin_bottom") ||
            $this->fieldModified("page_height") ||
            $this->fieldModified("page_width") ||
            $this->fieldModified("header_id") ||
            $this->fieldModified("preface_id") ||
            $this->fieldModified("ending_id") ||
            $this->fieldModified("footer_id");

        if ($source_modified || $this->fieldModified("valide")) {
            // Remove PDF File
            /** @var CFile $_file */
            foreach ($this->loadBackRefs("files") as $_file) {
                $_file->fileEmpty();
            }
        }

        if ($source_modified) {
            // Bug IE : delete id attribute
            $this->_source  = self::restoreId($this->_source);
            $this->doc_size = strlen($this->_source);

            // Send status to obsolete
            $this->completeField("etat_envoi");
            if ($source_modified && $this->etat_envoi == "oui") {
                $this->etat_envoi = "obsolete";
            }
        }

        if (!$this->_id || $content_modified) {
            $this->version++;

            if ($this->_id) {
                $this->modification_date = CMbDT::dateTime();

                if ($content_modified) {
                    (new CCompteRenduService($this))->manageCancelAndReplaceMotion();
                }
            }
        }

        $this->_ref_content->content = $this->_source;

        if (!$this->_id) {
            $parent_modele = $this->loadModele(false);
            $parent_modele->loadContent(false);
            // Si issu d'une duplication depuis un document existant, alors on reprend la version du document d'origine
            // L'incrément de version se fait en fin de store
            if ($parent_modele->object_id) {
                $this->version = $parent_modele->version;
                $parent_modele->isAutoLock();

                // Si le document existant est verrouillé, alors on l'archive
                if ($parent_modele->valide || $parent_modele->_is_auto_locked) {
                    $parent_modele->annule = 1;
                    $parent_modele->store();
                }
            }
        }

        if ($msg = $this->_ref_content->store()) {
            CAppUI::setMsg($msg, UI_MSG_ERROR);
        }

        // Prevent modele_id = compte_rendu_id
        // But, allow to save the content
        if ($this->_id === $this->modele_id) {
            $this->modele_id = "";
        }

        // Detect the fields not completed
        $matches = [];
        preg_match_all("/(field|name)\">(\[)+[^\]]+(\])+<\/span>/ms", $this->_source, $matches);
        $this->fields_missing = count($matches[0]);

        if (!$this->content_id) {
            $this->content_id = $this->_ref_content->_id;
        }

        if (!$this->_id && !self::$import) {
            $this->author_id = CMediusers::get()->_id;
        }

        if ($this->factory == "none" || !$this->factory) {
            $this->factory = CAppUI::pref("dPcompteRendu choice_factory");
            if (!$this->factory) {
                $this->factory = "CWkHtmlToPDFConverter";
            }
        }

        // Purge de documents temporaires
        if (!$this->_id) {
            CApp::doProbably(CAppUI::gconf("dPcompteRendu CCompteRendu purge_lifetime"), [$this, "purgeSomeTempDoc"]);
        }

        if ($this->_id) {
            CApp::doProbably(
                CAppUI::gconf("dPcompteRendu CCompteRendu probability_regenerate"),
                [$this, "regenerateSomePDF"]
            );
        }

        if (CModule::getActive("oxCabinet") && $this->object_class === 'CEvenementPatient' && $this->object_id && !$this->file_category_id) {
            $object = $this->object_class::findOrNew($this->object_id);
            $categorie = CAppUI::gconf("oxCabinet CEvenementPatient categorie_{$object->type}_default");
            $this->file_category_id = $categorie;
        }

        $this->duree_lecture = intval($this->duree_lecture) + intval($this->_add_duree_lecture);
        $this->duree_ecriture = intval($this->duree_ecriture) + intval($this->_add_duree_ecriture);

        if ($this->fieldModified("valide")) {
            $this->validation_date = $this->valide ? 'now' : '';
        }

        if ($msg = parent::store()) {
            return $msg;
        }

        $this->loadRefStatutCompteRendu();
        $statut                  = new CStatutCompteRendu();
        $statut->datetime        = CMbDT::dateTime();
        $statut->compte_rendu_id = $this->_id;
        $statut->user_id         = $this->author_id ?? CMediusers::get()->_id;

        if (!count($this->_ref_statut_compte_rendu)) {
            $statut->statut          = "brouillon";
            $statut->store();
        }
        if ($field_valide) {
            $statut->statut          = "a_envoyer";
            $statut->store();
        }
        if (CModule::getActive("moebius") && CAppUI::pref("ViewConsultMoebius")) {
            if ($this->object_class == CClassMap::getInstance()->getShortName(CConsultAnesth::class)) {
                if (!$this->modele_id) {
                    $models_anesth = new Cache("config_model_list", "anesth", Cache::INNER_OUTER, 600);
                    $models_anesth->rem();
                    $models_anesth = new Cache("model_list", "anesth", Cache::INNER_OUTER, 600);
                    $models_anesth->rem();
                }

                if ($is_new && $this->modele_id) {
                    $current_function = CMediusers::get()->loadRefFunction();
                    foreach (CClassMap::getInstance()->getClassChildren(CRisque::class, true, true) as $_risk) {
                        $type = $_risk->getType();

                        foreach ($_risk->getDocumentTypes() as $risk_entry) {
                            $doc_name   = CRisque::makeDocumentName($risk_entry);
                            $conf_value = CAppUI::conf("moebius models_conf $type $doc_name", $current_function->_guid);

                            if ($this->modele_id == $conf_value) {
                                $_risk->consultation_anesth_id = CConsultAnesth::findOrFail($this->object_id)->_id;
                                $_risk->$risk_entry            = "realise";
                                $_risk->loadMatchingObjectEsc();

                                $_risk->store();
                                break;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Suppression de document / modèle
     *
     * @return string
     */
    function delete()
    {
        $this->completeField("content_id");
        $this->loadContent(false);
        $this->loadRefsFiles();

        // Remove PDF preview
        foreach ($this->_ref_files as $_file) {
            if ($msg = $_file->delete()) {
                return $msg;
            }
        }

        if ($msg = parent::delete()) {
            return $msg;
        }

        // Remove content
        return $this->_ref_content->delete();
    }

    /**
     * Envoi de document
     *
     * @return string|null
     */
    public function handleSend(): ?string
    {
        if (!$this->_send) {
            return null;
        }

        $this->loadFile();

        $this->completeField("nom", "_source");

        return parent::handleSend();
    }

    /**
     * Tell whether object has a document with the same name has this one
     *
     * @param CMbObject $object Object to test with
     *
     * @return boolean
     */
    function existsFor(CMbObject $object)
    {
        $ds  = $this->getDS();
        $doc = new self();
        $doc->setObject($object);
        $doc->nom = $ds->escape($this->nom);

        return $doc->countMatchingList();
    }

    /**
     * Construit un tableau de traduction des classes pour lesquelles la fonction filltemplate existe
     *
     * @return array
     */
    static function getTemplatedClasses()
    {
        if (self::$templated_classes !== null) {
            return self::$templated_classes;
        }

        $all_classes = [
            "CConsultAnesth",
            "CConsultation",
            "COperation",
            "CPatient",
            "CSejour",
            "CFactureCabinet",
            "CFactureEtablissement",
            'CDevisCodage',
            "CEvenementPatient",
            "CGroups",
            "CElementPrescription",
            "CPatientReunion",
        ];

        if (CModule::getActive("dPprescription")) {
            $all_classes[] = "CPrescription";
        }

        if (CModule::getActive('oxFAC')) {
            $all_classes[] = 'COXConvention';
            $all_classes[] = 'COXAttestationFormation';
            $all_classes[] = 'COXConvocationFormation';
            $all_classes[] = 'COXFACEmargement';
            $all_classes[] = 'COXOrdreMission';
            $all_classes[] = 'COXFACQSC';
            $all_classes[] = 'COXFormationPlage';
            $all_classes[] = 'COXFormationParticipantItem';
        }

        if (CModule::getActive('pyxvitalManager')) {
            $all_classes[] = 'CLicenceOrder';
        }

        if (CAppUI::gconf("dPfacturation CRelance use_relances")) {
            $all_classes[] = "CRelance";
        }

        if (CModule::getActive('AppFine')) {
            $all_classes[] = "CEvenementMedical";
        }

        foreach (CRGPDManager::getCompliantClasses() as $_rgpd_class) {
            $all_classes[] = $_rgpd_class;
        }

        $all_classes = array_unique($all_classes);
        $installed   = CApp::getInstalledClasses(
            $all_classes
        ); // TODO namespace :  check if need shortnames or fullnames

        $classes = [];
        foreach ($installed as $class) {
            if (CApp::isMethodOverridden($class, 'fillTemplate', false) || CApp::isMethodOverridden(
                    $class,
                    'fillLimitedTemplate',
                    false
                )) {
                $classes[$class] = CAppUI::tr($class);
            }
        }

        if (!count($classes)) {
            $classes["CMbObject"] = CAppUI::tr("CMbObject");
        }

        asort($classes);

        return self::$templated_classes = $classes;
    }

    /**
     * Construit une source html
     *
     * @param string $htmlcontent html source to use if not a model
     * @param string $mode        [optional]
     * @param array  $margins     [optional]
     * @param string $font        Font name
     * @param string $size        Font size
     * @param bool   $auto_print  [optional]
     * @param string $type        [optional]
     * @param string $header      [optional]
     * @param int    $sizeheader  [optional]
     * @param string $footer      [optional]
     * @param int    $sizefooter  [optional]
     * @param string $preface     [optional]
     * @param string $ending      [optional]
     *
     * @return string
     */
    function loadHTMLcontent(
        $htmlcontent,
        $mode = "modele",
        $margins = [],
        $font = "",
        $size = "",
        $auto_print = true,
        $type = "body",
        $header = "",
        $sizeheader = 0,
        $footer = "",
        $sizefooter = 0,
        $preface = "",
        $ending = ""
    ) {
        $default_font = $font;
        $default_size = $size;

        if ($default_font == "") {
            $default_font = CAppUI::conf("dPcompteRendu CCompteRendu default_font");
        }

        if ($default_size == "") {
            $default_size = CAppUI::gconf("dPcompteRendu CCompteRendu default_size");
        }

        $style = file_get_contents("style/mediboard_ext/htmlarea.css") .
            "@page {
         margin-top:    {$margins[0]}cm;
         margin-right:  {$margins[1]}cm;
         margin-bottom: {$margins[2]}cm;
         margin-left:   {$margins[3]}cm;
       }
       body, table {
         font-family: $default_font;
         font-size: $default_size;
       }
       body {
         margin:  0;
         padding: 0;
       }
       .orig {
         display: none;
       }";

        $content  = "";
        $position = [
            "header" => "top",
            "footer" => "bottom",
        ];

        if ($mode == "modele") {
            switch ($type) {
                case "header":
                case "footer":
                    $position   = $position[$type];
                    $sizeheader = $sizeheader != '' ? $sizeheader : 50;

                    $style .= "
            #{$type} {
              height: {$sizeheader}px;
              {$position}: 0cm;
              width: auto;
            }";

                    $content = "<div id=\"$type\">$htmlcontent</div>";
                    break;
                case "body":
                case "preface":
                case "ending":
                    if ($header) {
                        $sizeheader  = $sizeheader != '' ? $sizeheader : 50;
                        $padding_top = $sizeheader;

                        $style .= "
                @media print {
                  body {
                    margin-top: {$padding_top}px;
                  }
                  #header {
                    height: {$sizeheader}px;
                    top: 0cm;
                  }
                }";

                        $content .= "<div id=\"header\">$header</div>";
                    }
                    if ($footer) {
                        $sizefooter     = $sizefooter != '' ? $sizefooter : 50;
                        $padding_bottom = $sizefooter;
                        $style          .= "
                @media print {
                  body {
                    margin-bottom: {$padding_bottom}px;
                  }
                  #footer {
                    height: {$sizefooter}px;
                    bottom: 0cm;
                  }
                }";
                        $content        .= "<div id=\"footer\">$footer</div>";
                    }
                    if ($preface) {
                        $htmlcontent = "$preface<br />" . $htmlcontent;
                    }
                    if ($ending) {
                        $htmlcontent .= "<br />$ending";
                    }
                    $content .= "<div id=\"body\">$htmlcontent</div>";
            }
        } else {
            $content = $htmlcontent;
        }
        $smarty = new CSmartyDP("modules/dPcompteRendu");
        $smarty->assign("style", $style);
        $smarty->assign("content", $content);
        $smarty->assign("auto_print", $auto_print);

        return $smarty->fetch("htmlheader.tpl");
    }

    /**
     * Generate a pdf preview for the document
     *
     * @param boolean $force_generating [optional]
     * @param boolean $auto_print       [optional]
     * @param boolean $load_content     [optional]
     *
     * @return string|null
     */
    function makePDFpreview($force_generating = false, $auto_print = true, $load_content = true)
    {
        if (!CAppUI::pref("pdf_and_thumbs") && !$force_generating) {
            return null;
        }

        $mutex = new CMbMutex($this->_guid);
        if (!$mutex->lock(60)) {
            return null;
        }

        $this->loadRefsFwd();
        $file = $this->loadFile();

        // Fichier existe déjà et rempli et que la génération n'est pas forcée
        if (!$force_generating && $load_content && $file->_id && file_exists($file->_file_path) && filesize(
                $file->_file_path
            )) {
            $mutex->release();

            return null;
        }

        if ($file->_id && CAppUI::conf('dPfiles CFile prefix_format')) {
            $prefix = $file->getPrefix($file->file_date);
            if (strpos($file->file_real_filename, $prefix) !== 0) {
                if (file_exists($file->_file_path)) {
                    unlink($file->_file_path);
                }
                $file->file_real_filename = $prefix . $file->file_real_filename;
                $file->updateFormFields();
            }
        }

        // Création du CFile si inexistant
        if (!$file->_id || !file_exists($file->_file_path)) {
            $file->setObject($this);
            $file->file_name = $this->nom . ".pdf";
            $file->file_type = "application/pdf";
            $file->author_id = $file->author_id ?: CMediusers::get()->_id;
            $file->fillFields();
            $file->updateFormFields();
            //$file->forceDir();
        }

        // Génération du contenu PDF
        $margins = [
            $this->margin_top,
            $this->margin_right,
            $this->margin_bottom,
            $this->margin_left,
        ];

        if ($load_content) {
            $this->loadContent();
        }

        $content         = $this->loadHTMLcontent(
            $this->_source,
            '',
            $margins,
            self::$fonts[$this->font],
            $this->size,
            $auto_print
        );
        $htmltopdf       = new CHtmlToPDF($this->factory);
        $pdf_content     = $htmltopdf->generatePDF($content, 0, $this, $file, $auto_print);
        $this->_ref_file = $file;

        // Prévention des fichiers vides
        if (!$pdf_content) {
            $mutex->release();

            return CAppUI::tr("CCompteRendu-failed_generation");
        }

        $msg = null;
        if ($load_content) {
            $msg = $this->_ref_file->store();
        }

        $mutex->release();

        if ($load_content) {
            return $msg;
        }
    }

    /**
     * Generate the html source from a modele. Can use an optionnal header, footer
     * and another source.
     *
     * @param string $other_source [optional]
     * @param int    $header_id    [optional]
     * @param int    $footer_id    [optional]
     *
     * @return string
     */
    function generateDocFromModel($other_source = null, $header_id = null, $footer_id = null)
    {
        $source = $this->_source;

        $this->loadComponents();

        $header  = $this->_ref_header;
        $footer  = $this->_ref_footer;
        $preface = $this->_ref_preface;
        $ending  = $this->_ref_ending;

        if ($header_id) {
            $header->load($header_id);
        }
        if ($footer_id) {
            $footer->load($footer_id);
        }

        if ($other_source) {
            $source = $other_source;
            // Si on utilise une source existante, l'intro et la conclusion sont déjà incluses
            $preface = new self();
            $ending  = new self();
        }

        $header->loadContent();
        $footer->loadContent();
        $preface->loadContent();
        $ending->loadContent();

        if ($preface->_id) {
            $source = "$preface->_source<br />" . $source;
        }

        if ($ending->_id) {
            $source .= "<br />$ending->_source";
        }

        if ($header->_id || $footer->_id) {
            $header->height = isset($header->height) ? $header->height : 20;
            $footer->height = isset($footer->height) ? $footer->height : 20;
            $style          = "
        <style type='text/css'>
        #header {
          height: {$header->height}px;
          /*DOMPDF top: 0;*/
        }

        #footer {
          height: {$footer->height}px;
          /*DOMPDF bottom: 0;*/
        }";

            if ($header->_id) {
                $header->loadContent();
                $header->_source = "<div id=\"header\">$header->_source</div>";

                if (!CAppUI::pref("pdf_and_thumbs")) {
                    $header->height += 20;
                }
            }

            if ($footer->_id) {
                $footer->loadContent();
                $footer->_source = "<div id=\"footer\">$footer->_source</div>";

                if (!CAppUI::pref("pdf_and_thumbs")) {
                    $footer->height += 20;
                }
            }

            $style .= "
        @media print {
          body {
            margin-top: {$header->height}px;
          }
          hr.pagebreak {
            padding-top: {$header->height}px;
          }
        }";

            $style .= "
        @media dompdf {
          body {
            margin-bottom: {$footer->height}px;
          }
          hr.pagebreak {
            padding-top: 0px;
          }
        }</style>";

            $source = "<div id=\"body\">$source</div>";
            $source = $style . $header->_source . $footer->_source . $source;
        }

        return $source;
    }

    /**
     * Patch the disappearance of an html attribute
     *
     * @param string $source source to control
     *
     * @return string
     */
    static function restoreId($source)
    {
        if (strpos($source, '<div id="body"') === false &&
            strpos($source, "<div id='body'") === false &&
            strpos($source, "@media dompdf") !== false
        ) {
            $xml = new DOMDocument('1.0', 'iso-8859-1');
            $xml->loadXML("<div>" . utf8_encode(CMbString::convertHTMLToXMLEntities($source)) . "</div>");
            $xpath = new DOMXpath($xml);

            /** @var DOMElement $last_div */
            $last_div = null;

            // Test header id
            $elements = $xpath->query("//div[@id='header']");

            if ($elements->length) {
                $last_div = $elements->item(0);
                $last_div = $last_div->nextSibling;
                while ($last_div && $last_div->nodeType != 1) {
                    $last_div = $last_div->nextSibling;
                }
                if ($last_div->getAttribute("id") == "footer") {
                    $last_div = $last_div->nextSibling;
                }
            }

            // Or footer id
            if (!$last_div) {
                $last_div = $xpath->query("//div[@id='footer']")->item(0);
                $last_div = $last_div->nextSibling;
                while ($last_div && $last_div->nodeType != 1) {
                    $last_div = $last_div->nextSibling;
                }
            }

            $div_body = $xml->createElement("div");
            $id_body  = $xml->createAttribute("id");
            $id_value = $xml->createTextNode("body");
            $id_body->appendChild($id_value);
            $div_body->appendChild($id_body);

            $div_body = $last_div->parentNode->insertBefore($div_body, $last_div);

            while ($elt_to_move = $xpath->query("//div[@id='body']")->item(0)->nextSibling) {
                $div_body->appendChild($elt_to_move->parentNode->removeChild($elt_to_move));
            }

            // Substring to remove the header of the xml output, and div surrounded
            $source = substr($xml->saveXML(), 27, -7);
        }

        return $source;
    }

    /**
     * User stats on models
     *
     * @param string|null $factory
     *
     * @return array
     * @see parent::getUsersStats()
     */
    public function getUsersStats(string $factory = null): array
    {
        $ds    = $this->_spec->ds;

        $query = new CRequest();
        $query->addColumn("COUNT(`compte_rendu_id`)", "docs_count");
        $query->addColumn("COUNT(`duree_lecture`)", "doc_with_duree_ecriture");
        $query->addColumn("COUNT(`duree_ecriture`)", "doc_with_duree_lecture");
        $query->addColumn("SUM(`doc_size`)", "docs_weight");
        $query->addColumn("SUM(`duree_lecture`)", "docs_read_time");
        $query->addColumn("SUM(`duree_ecriture`)", "docs_write_time");
        $query->addColumn("author_id", "owner_id");
        $query->addTable("compte_rendu");
        $query->addGroup("owner_id");
        $query->addOrder("docs_weight DESC");
        $query->addWhereClause("author_id", "IS NOT NULL");
        if ($factory) {
            $query->addWhereClause("factory", " = '$factory'");
        }
        return $ds->loadList($query->makeSelect());
    }

    /**
     * @see parent::getUsersStatsDetails();
     */
    public function getUsersStatsDetails($user_ids, $date_min = null, $date_max = null, string $factory = null): array
    {
        $ds = $this->_spec->ds;

        $query = new CRequest();
        $query->addColumn("COUNT(`compte_rendu_id`)", "docs_count");
        $query->addColumn("SUM(`doc_size`)", "docs_weight");
        $query->addColumn("AVG(`duree_lecture`)", "docs_read_time");
        $query->addColumn("AVG(`duree_ecriture`)", "docs_write_time");
        $query->addColumn("object_class");
        $query->addColumn("file_category_id", "category_id");
        $query->addTable("compte_rendu");
        $query->addGroup("object_class, category_id");
        if ($factory) {
            $query->addWhereClause("factory", " = '$factory'");
        }
        if ($date_min) {
            $query->addWhere("creation_date <= '$date_max'");
        }

        if ($date_max) {
            $query->addWhere("creation_date >= '$date_min'");
        }

        if ($this->_id) {
            $query->addWhereClause("modele_id", "= '$this->_id'");
        }

        if (is_array($user_ids)) {
            $in_owner = $ds->prepareIn($user_ids);
            $query->addWhereClause("author_id", $in_owner);
        }

        return $ds->loadList($query->makeSelect());
    }

    /**
     * @see parent::getPeriodicalStatsDetails();
     */
    function getPeriodicalStatsDetails(
        $user_ids,
        $object_class = null,
        $category_id = null,
        $depth = 10,
        $no_types = null,
        $factory = null
    ) {
        $period_types = [
            "year"  => [
                "format" => "%Y",
                "unit"   => "YEAR",
            ],
            "month" => [
                "format" => "%m/%Y",
                "unit"   => "MONTH",
            ],
            "week"  => [
                "format" => "%Y S%U",
                "unit"   => "WEEK",
            ],
            "day"   => [
                "format" => "%d/%m",
                "unit"   => "DAY",
            ],
            "hour"  => [
                "format" => "%d %Hh",
                "unit"   => "HOUR",
            ],
        ];
        if ($no_types) {
            foreach ($no_types as $_no_type) {
                unset($period_types[$_no_type]);
            }
        }

        $details = [];

        $now    = CMbDT::dateTime();
        $doc    = new self();
        $ds     = $doc->_spec->ds;
        $deeper = $depth + 1;

        foreach ($period_types as $_type => $_period_info) {
            $format = $_period_info["format"];
            $unit   = $_period_info["unit"];

            $request = new CRequest();
            $request->addColumn("DATE_FORMAT(`creation_date`, '$format')", "period");
            $request->addColumn("COUNT(`compte_rendu_id`)", "count");
            $request->addColumn("SUM(`doc_size`)", "weight");
            $request->addColumn("MIN(`creation_date`)", "date_min");
            $request->addColumn("MAX(`creation_date`)", "date_max");
            $request->addColumn('AVG(`duree_lecture`)', 'docs_read_time');
            $request->addColumn('AVG(`duree_ecriture`)', 'docs_write_time');
            $date_min = CMbDT::dateTime("- $deeper $unit", $now);
            $request->addWhereClause("creation_date", " > '$date_min'");

            if (is_array($user_ids)) {
                $request->addWhereClause("author_id", CSQLDataSource::prepareIn($user_ids));
            }

            if ($object_class) {
                $request->addWhereClause("object_class", "= '$object_class'");
            }

            if ($category_id) {
                $request->addWhereClause("file_category_id", "= '$category_id'");
            }

            if ($this->_id) {
                $request->addWhereClause("modele_id", "= '$this->_id'");
            }
            if ($factory) {
                $request->addWhereClause("factory", " = '$factory'");
            }
            $request->addGroup("period");
            $results = $ds->loadHashAssoc($request->makeSelect($doc));

            foreach (range($depth, 0) as $i) {
                $period                   = CMbDT::transform("-$i $unit", $now, $format);
                $details[$_type][$period] = isset($results[$period]) ? $results[$period] : 0;

                if (is_array($details[$_type][$period])) {
                    $details[$_type][$period]['nb_writers_simultaneous'] =
                        round(
                            $details[$_type][$period]['count'] * $details[$_type][$period]['docs_write_time'] / 3600,
                            2
                        );
                }
            }
        }

        return $details;
    }

    /**
     * @inheritdoc
     */
    public function getDiskUsage($user_id): array
    {
        $ds    = $this->_spec->ds;
        $query = "
      SELECT
        COUNT(`compte_rendu_id`) AS `docs_count`,
        SUM(`doc_size`) AS `docs_weight`
      FROM `compte_rendu`
      WHERE `author_id` = '$user_id'
      GROUP BY `author_id`
      ORDER BY `docs_weight` DESC";

        return $ds->loadList($query);
    }

    /**
     * Return the content of the document in plain text
     *
     * @param string $encoding The encoding, default UTF-8
     *
     * @return string
     */
    function getPlainText($encoding = "UTF-8")
    {
        if (!$this->_source) {
            $this->loadContent(true);
        }

        return CMbString::htmlToText($this->_source, $encoding);
    }

    /**
     * Retourne la source d'un document générée depuis le modèle
     *
     * @param bool $auto_print Auto print
     *
     * @return string
     */
    function getFullContentFromModel($auto_print = true)
    {
        $this->loadContent();
        $margins = [
            $this->margin_top,
            $this->margin_right,
            $this->margin_bottom,
            $this->margin_left,
        ];
        $content = $this->generateDocFromModel();

        return $this->loadHTMLcontent($content, '', $margins, self::$fonts[$this->font], $this->size, $auto_print);
    }

    /**
     * Stream document for object
     *
     * @param CCompteRendu $compte_rendu Document
     * @param CMbObject    $object       Object
     * @param string       $factory      Factory name
     * @param bool         $auto_print   Auto print
     *
     * @return void
     */
    public static function streamDocForObject($compte_rendu, $object, $factory = null, $auto_print = true)
    {
        ob_clean();

        static::getDocForObject($compte_rendu, $object, $factory, $auto_print, true);

        CApp::rip();
    }

    public static function getDocForObject(
        $compte_rendu,
        $object,
        $factory = null,
        $auto_print = true,
        $stream = false
    ): ?string {
        // Génération de la source HTML à partir du modèle
        $template           = new CTemplateManager();
        $template->isModele = false;
        $template->document = $compte_rendu->getFullContentFromModel($auto_print);

        // Injection des champs et génération du HTML final
        $object->fillTemplate($template);
        $template->renderDocument($template->document);

        // Génération du PDF
        $htmltopdf = new CHtmlToPDF($factory);
        return $htmltopdf->generatePDF($template->document, $stream, $compte_rendu, new CFile(), $auto_print);
    }

    /**
     * Retourne un modèle de nom prédéfini pour un utilisateur / fonction / établissement et une classe donnés
     *
     * @param CMediusers|CFunctions|CGroups $owner        Owner
     * @param string                        $object_class Target Class
     * @param string                        $name         Model Name
     * @param int                           $group_id     Optional group_id
     *
     * @return CCompteRendu|null
     */
    static function getSpecialModel($owner, $object_class, $name, $group_id = null, $exclusive_group_id = false)
    {
        $special_names = static::getSpecialNames();

        if (!isset($special_names[$object_class][$name])) {
            self::error("no_special", $object_class, $name);

            return null;
        }

        $model = new self();

        if (!$owner->_id) {
            return $model;
        }

        $ds = $model->getDS();

        $where = [
            "nom"          => $ds->prepareLike("$name%"),
            "actif"        => "= '1'",
            "object_class" => $ds->prepare("= ?", $object_class),
            "object_id"    => "IS NULL",
            "type"         => $ds->prepare("= ?", $special_names[$object_class][$name]),
        ];

        switch ($owner->_class) {
            case "CMediusers":
                // Utilisateur
                $where["user_id"] = $ds->prepare("= ?", $owner->_id);
                if ($model->loadObject($where)) {
                    return $model;
                }

                $owner = $owner->loadRefFunction();
            case "CFunctions":
                // Fonction
                unset($where["user_id"]);
                $where["function_id"] = $ds->prepare("= ?", $owner->_id);
                if ($model->loadObject($where)) {
                    return $model;
                }

                $owner = $owner->loadRefGroup();
            case "CGroups":
                // Etablissement
                unset($where["function_id"]);

                if ($group_id) {
                    $where["group_id"] = $ds->prepare("= ?", $group_id);

                    if ($model->loadObject($where)) {
                        return $model;
                    }
                    if ($exclusive_group_id) {
                        return $model;
                    }
                }

                $where["group_id"] = $ds->prepare("= ?", $owner->group_id);
                if ($model->loadObject($where)) {
                    return $model;
                }
            default:
                unset($where["group_id"]);
                $where[] = "user_id IS NULL AND function_id IS NULL AND group_id IS NULL";
                $model->loadObject($where);
        }

        return $model;
    }

    /**
     * Retourne le dernier utilisateur qui a modifié le document
     *
     * @return CUser
     */
    function loadLastWriter()
    {
        $user = $this->loadLastLogForField("version")->_ref_user;
        if (!$user) {
            $this->loadRefsFwd();
            $user = $this->_ref_user;
        }

        $user->loadFirstLog();

        return $user;
    }

    /**
     * Remplace l'entête ou le pied de page dans une source html
     *
     * @param string $source       HTML source
     * @param int    $component_id Id of the component
     * @param string $type         Type of the component
     *
     * @return string
     */
    static function replaceComponent($source, $component_id, $type = "header")
    {
        if (strpos($source, "<style type=\"text/css\">") === false) {
            $source = "<style type=\"text/css\">
        #header {
          height: 0;
          /*DOMPDF top: 0;*/
        }

        #footer {
          height: 0;
          /*DOMPDF bottom: 0;*/
        }
        @media print {
          body {
            margin-top: 0;
          }
          hr.pagebreak {
            padding-top: 0;
          }
        }
        @media dompdf {
          body {
            margin-bottom: 0;
          }
          hr.pagebreak {
            padding-top: 0;
          }
        }</style>
        <div id=\"body\">" .
                $source .
                "</div>";
        };

        switch ($type) {
            case "header":
                $header = new self();
                $header->load($component_id);
                $header->loadContent(true);


                if ($header->_source) {
                    $header->_source = "<div id=\"header\">" . $header->_source . "</div>";
                }

                $height = $header->height ? $header->height : 0;
                $source = preg_replace(
                    "/(#header\s*\{\s*height:\s*)([0-9]*[\.0-9]*)px;/",
                    '${1}' . $height . 'px;',
                    $source
                );
                $source = preg_replace(
                    "/(body\s*\{\s*margin-top:\s*)([0-9]*[\.0-9]*)px;/",
                    '${1}' . $height . 'px;',
                    $source
                );
                $source = preg_replace(
                    "/(body\s*\{\s*padding-top:\s*)([0-9]*[\.0-9]*)px;/",
                    '${1}' . $height . 'px;',
                    $source
                );
                $source = preg_replace(
                    "/(hr.pagebreak\s*\{\s*padding-top:\s*)([0-9]*[\.0-9]*)px;/",
                    '${1}' . $height . 'px;',
                    $source,
                    1
                );

                $pos_style  = strpos($source, "</style>") + 9;
                $pos_header = strpos($source, "<div id=\"header\"");
                $pos_footer = strpos($source, "<div id=\"footer\"");
                $pos_body   = strpos($source, "<div id=\"body\">");

                if ($pos_header) {
                    if ($pos_footer) {
                        $source = substr_replace($source, $header->_source, $pos_header, $pos_footer - $pos_header);
                    } else {
                        $source = substr_replace($source, $header->_source, $pos_header, $pos_body - $pos_header);
                    }
                } else {
                    if ($pos_footer) {
                        $source = substr_replace($source, $header->_source, $pos_style, $pos_footer - $pos_style);
                    } else {
                        $source = substr_replace($source, $header->_source, $pos_style, 0);
                    }
                }
                break;
            case "footer":
                $footer = new self();
                $footer->load($component_id);
                $footer->loadContent(true);

                if ($footer->_source) {
                    $footer->_source = "<div id=\"footer\">" . $footer->_source . "</div>";
                }
                $height = $footer->height ? $footer->height : 0;
                $source = preg_replace(
                    "/(#footer\s*\{\s*footer:\s*)([0-9]+[\.0-9]*)px;/",
                    '${1}' . $height . 'px;',
                    $source
                );
                $source = preg_replace(
                    "/(body\s*\{\s*margin-bottom:\s*)([0-9]+[\.0-9]*)px;/",
                    '${1}' . $height . 'px;',
                    $source
                );

                $pos_footer = strpos($source, "<div id=\"footer\"");
                $pos_body   = strpos($source, "<div id=\"body\">");
                if ($pos_footer) {
                    $source = substr_replace($source, $footer->_source, $pos_footer, $pos_body - $pos_footer);
                } else {
                    $source = substr_replace($source, $footer->_source, $pos_body, 0);
                }
                break;
            default:
        }

        return $source;
    }

    /**
     * Loads the related fields for indexing datum
     *
     * @return array
     */
    function getIndexableData()
    {
        $prat = $this->getIndexablePraticien();
        if (!$prat) {
            $prat = new CMediusers();
        }
        $array["id"]        = $this->_id;
        $array["author_id"] = $this->author_id;
        $array["prat_id"]   = $prat->_id;
        $array["title"]     = $this->nom;
        $this->loadContent(false);
        $content       = $this->_ref_content;
        $array["body"] = $this->getIndexableBody($content->content);
        $date          = $this->creation_date;
        if (!$date) {
            $date = CMbDT::dateTime();
        }
        $array["date"]             = str_replace("-", "/", $date);
        $array["function_id"]      = $prat->function_id;
        $array["group_id"]         = $this->loadRefAuthor()->loadRefFunction()->group_id;
        $array["patient_id"]       = $this->getIndexablePatient() ? $this->getIndexablePatient()->_id : null;
        $array["object_ref_id"]    = $this->loadTargetObject()->_id;
        $array["object_ref_class"] = $this->loadTargetObject()->_class;

        return $array;
    }

    /**
     * Redesign the content of the body you will index
     *
     * @param string $content The content you want to redesign
     *
     * @return string
     */
    function getIndexableBody($content)
    {
        return CSearchIndexing::getRawText($content);
    }

    /**
     * Get the patient_id of CMbobject
     *
     * @return CPatient
     */
    function getIndexablePatient()
    {
        $object = $this->loadTargetObject();

        if (!$object || !$object->_id) {
            return null;
        }

        if ($object instanceof CPatient) {
            return $object;
        }

        if ($object instanceof IPatientRelated) {
            $object->loadRelPatient();
        } elseif (method_exists($object, "loadRefPatient")) {
            $object->loadRefPatient();
        }

        switch ($this->object_class) {
            case "CConsultAnesth":
                return $object->_ref_consultation->_ref_patient;
            case "CElementPrescription":
                return new CPatient();
            case "CElementPrescription":
                return new CPatient();
            default:
                return property_exists($object, '_ref_patient') ? $object->_ref_patient : new CPatient();
        }
    }

    /**
     * Get the praticien_id of CMbobject
     *
     * @return CMediusers
     */
    function getIndexablePraticien()
    {
        $object = $this->loadTargetObject();
        if (!$object || !$object->_id) {
            return null;
        }
        if ($object instanceof CConsultAnesth) {
            $prat = $object->loadRefConsultation()->loadRefPraticien();
        } elseif ($object instanceof CPatient) {
            $prat = $this->loadRefAuthor();
        } else {
            $prat = $object->loadRefPraticien();
        }

        return $prat;
    }

    /**
     * Suppression de documents dont le modèle est marqué comme temporaire
     *
     * @return bool|resource|void
     */
    function purgeSomeTempDoc()
    {
        if (!$limit = CAppUI::gconf('dPcompteRendu CCompteRendu purge_limit')) {
            return;
        }

        $modele = new self();

        $where = [
            "object_id" => "IS NULL",
            "purgeable" => "= '1'",
        ];

        $modeles_ids = $modele->loadIds($where);

        $where = ["modele_id" => CSQLDataSource::prepareIn($modeles_ids)];
        foreach ($modele->loadList($where, null, $limit * count($modeles_ids)) as $_doc) {
            $_doc->delete();
        }
    }

    static function importModele($_modele, $user_id, $function_id, $group_id, $object_class, &$modeles_ids)
    {
        $components = ["header_id", "footer_id", "preface_id", "ending_id"];

        $modele              = new self();
        $modele->user_id     = $user_id;
        $modele->function_id = $function_id;
        $modele->group_id    = $group_id;

        // Mapping des champs principaux
        foreach ($_modele->childNodes as $_node) {
            if (!in_array($_node->nodeName, self::$fields_exclude_export) && property_exists(
                    $modele->_class,
                    $_node->nodeName
                )) {
                $modele->{$_node->nodeName} = $_node->nodeValue;
            }
        }

        if ($object_class) {
            $modele->object_class = $object_class;

            if ($object_class === 'CEvenementPatient' && CModule::getActive('oxCabinet')) {
                $modele->_source = (new TAMMSIHFieldsReplacer($modele->_source))->replaceFields();
            }
        }

        $modele->nom = utf8_decode($modele->nom);

        // Mapping de l'entête, pieds de page, introduction, conclusion
        foreach ($components as $_component) {
            if ($modele->$_component) {
                $modele->$_component = $modeles_ids[$modele->$_component];
            }
        }

        // Recherche de la catégorie
        $cat = utf8_decode($_modele->getAttribute("cat"));
        if ($cat) {
            $categorie           = new CFilesCategory();
            $categorie->nom      = $cat;

            if (!$categorie->loadMatchingObjectEsc()) {
                $get_group_id = CGroups::get()->_id;

                if ($user_id) {
                    $get_group_id = $modele->loadRefUser()->loadRefFunction()->group_id;
                }
                elseif ($function_id) {
                    $get_group_id = $modele->loadRefFunction()->group_id;
                }

                $categorie->group_id = $group_id ?: $get_group_id;
                $categorie->store();
            }
            $modele->file_category_id = $categorie->_id;
        }

        if ($msg = $modele->store()) {
            CAppUI::stepAjax($modele->nom . " - " . $msg, UI_MSG_ERROR);

            return;
        }

        // Listes de choix
        $listes = $_modele->getElementsByTagName("listes")->item(0)->childNodes;
        if ($listes->length) {
            $modele->loadRefUser()->loadRefFunction();
            $modele->loadRefFunction();
            $modele->loadRefGroup();

            /** @var DOMElement $_liste */
            foreach ($listes as $_liste) {
                $nom   = html_entity_decode(utf8_decode($_liste->getAttribute("nom")), ENT_COMPAT);
                $liste = new CListeChoix();
                $where = [
                    "nom" => "= '$nom'",
                ];

                switch ($modele->_owner) {
                    default:
                    case "prat":
                        $where[] = "liste_choix.user_id     =     '$modele->user_id' OR
                      liste_choix.function_id = '" . $modele->_ref_user->function_id . "' OR
                      liste_choix.group_id    = '" . $modele->_ref_user->_ref_function->group_id . "'";
                        break;
                    case "func":
                        $where[] = "liste_choix.function_id =     '$modele->function_id' OR
                      liste_choix.group_id    = '" . $modele->_ref_function->group_id . "'";
                        break;
                    case "etab":
                        $where[] = "liste_choix.group_id = '" . $modele->group_id . "'";
                }

                // On importe la liste de choix que si elle n'est pas trouvée
                if (!$liste->loadObject($where)) {
                    [$liste->user_id, $liste->function_id, $liste->group_id] = [
                        $modele->user_id,
                        $modele->function_id,
                        $modele->group_id,
                    ];

                    $liste->nom = $nom;

                    $choix = [];
                    foreach ($_liste->childNodes as $_choix) {
                        $choix[] = html_entity_decode(utf8_decode($_choix->nodeValue), ENT_COMPAT);
                    }

                    $liste->valeurs = implode("|", $choix);

                    if ($msg = $liste->store()) {
                        CAppUI::stepAjax($modele->nom . " - " . $msg, UI_MSG_ERROR);
                    }
                }
            }
        }

        // On garde la référence entre l'id provenant du xml et l'id en base
        $modeles_ids[$_modele->getAttribute("modele_id")] = $modele->_id;

        return $modele;
    }

    /**
     * Regénération des PDF des compte-rendus
     *
     * @param int $limit Nombre de PDF à régénérer
     *
     * @return void
     */
    static function regenerateSomePDF($limit = null)
    {
        $file = new CFile();

        $files = $file->loadIds(null, "file_id DESC", "1");
        $last_id = is_array($files) ? reset($files) : null;

        // Table vide
        if (!$last_id) {
            return;
        }

        $random_id = rand(0, $last_id);
        $min_id    = $random_id - 1000;
        $max_id    = $random_id + 1000;

        $where = [
            "object_class" => "= 'CCompteRendu'",
            "doc_size"     => "= '0'",
            "file_id"      => "BETWEEN '$min_id' AND '$max_id'",
        ];

        if (!$limit) {
            $limit = 10;
        }

        $cr_ids = $file->loadColumn("object_id", $where, null, $limit);

        $cr = new self();

        $count_cr     = count($cr_ids);
        $cr_error_ids = [];

        foreach ($cr->loadList(["compte_rendu_id" => CSQLDataSource::prepareIn($cr_ids)]) as $_cr) {
            try {
                if ($_cr->makePDFpreview(true)) {
                    $cr_error_ids[] = $_cr->_id;
                }
            } catch (CMbException $e) {
                $cr_error_ids[] = $_cr->_id;
            }
        }

        CApp::log(
            ("range [$min_id - $max_id]\n") .
            ($count_cr - count($cr_error_ids)) . "/$count_cr aperçus régénés\n" .
            ("(" . implode("-", $cr_ids) . " sont les ids des compte-rendus traités)\n") .
            (count($cr_error_ids) ? (" (" . implode("-", $cr_error_ids) . " en échec)") : null)
            , null, LoggerLevels::LEVEL_DEBUG
        );
    }

    /**
     * @inheritdoc
     */
    public function isExportable($prat_ids = [], $date_min = null, $date_max = null, ...$additional_args)
    {
        $context = $this->loadTargetObject();

        return $context->isExportable($prat_ids, $date_min, $date_max);
    }

    /**
     * Returns all CConsultation models of an establishment (for configs)
     *
     * @return CStoredObject[]
     */
    public static function getConsultationModels()
    {
        $cr               = new CCompteRendu();
        $cr->object_class = "CConsultation";
        $cr->group_id     = CGroups::get()->_id;

        return $cr->loadMatchingListEsc();
    }

    /**
     * Returns all CAnesth models of an establishment (for configs)
     *
     * @return CStoredObject[]
     * @throws Exception
     */
    public static function getMoebiusModels($ancestor)
    {
        $cache = new Cache("moebius", "model_list_" . $ancestor->_guid, Cache::INNER_OUTER, 600);
        $list  = $cache->get();

        if (!$list) {
            $cr           = new CCompteRendu();
            $object_class = CClassMap::getSN(CConsultAnesth::class);

            if ($ancestor instanceof CGroups) {
                $cr->object_class = $object_class;
                $cr->group_id     = $ancestor->_id;
                $crs              = $cr->loadMatchingListEsc();
            } elseif ($ancestor instanceof CFunctions) {
                $where = "object_class = '$object_class' AND (group_id = '{$ancestor->group_id}' OR function_id = '{$ancestor->function_id}')";
                $crs   = $cr->loadList($where);
            }

            $list = $cache->put($crs);
        }

        return $list;
    }

    /**
     * @param int $model_id
     *
     * @return mixed
     * @throws Exception
     */
    public static function getNameFromId($model_id)
    {
        return CCompteRendu::find($model_id)->nom;
    }

    public static function massGetDateLastUse($modeles)
    {
        if (!count($modeles)) {
            return;
        }

        $ds = CSQLDataSource::get("std");

        $request = new CRequest();
        $request->addSelect("modele_id, creation_date");
        $request->addTable("compte_rendu");
        $request->addWhere(
            [
                "modele_id" => CSQLDataSource::prepareIn(CMbArray::pluck($modeles, "_id")),
            ]
        );
        $request->addGroup("modele_id");
        $request->addOrder("creation_date DESC");

        foreach ($ds->loadHashList($request->makeSelect()) as $_modele_id => $_date_last_use) {
            $modeles[$_modele_id]->_date_last_use = $_date_last_use;
        }
    }

    /**
     * Détermine le signataire du document
     */
    public function guessSignataire()
    {
        $object = $this->loadTargetObject();

        // Signataire du document
        switch ($this->object_class) {
            case "CConsultation":
                $this->signataire_id = $object->loadRefPlageConsult()->chir_id;
                break;
            case "CConsultAnesth":
                $this->signataire_id = $object->loadRefConsultation()->loadRefPlageConsult()->chir_id;
                break;
            case "COperation":
                $this->signataire_id = $object->chir_id;
                break;
            case "CEvenementPatient":
            case "CSejour":
                $this->signataire_id = $object->praticien_id;
                break;
            default:
        }
    }

    /**
     * Vérification du propriétaire si non admin
     *
     * @param CCompteRendu|CListeChoix|CAideSaisie|CPack $object
     *
     * @return string
     */
    public static function checkOwner($object)
    {
        $object->completeField("user_id", "function_id", "group_id");

        $module   = CModule::getActive("dPcompteRendu");
        $is_admin = $module && $module->canAdmin();

        if (!$is_admin && !$object->user_id && !$object->function_id && !$object->group_id) {
            return CAppUI::tr("compteRendu-Needs owner");
        }

        return null;
    }

    /**
     * Détecte si un modèle est d'instance
     *
     * @return bool
     */
    public function isForInstance()
    {
        return $this->_is_for_instance = (!$this->user_id && !$this->function_id && !$this->group_id && !$this->object_id);
    }

    /**
     * Retourne un objet qui simule l'instance
     *
     * @return CMbObject
     */
    public static function getInstanceObject()
    {
        $instance        = new CMbObject();
        $instance->_view = $instance->_guid = $instance->_id = CAppUI::tr("Instance");

        return $instance;
    }
    public function completeLabelFields(&$fields, $params)
    {
        $object = $this->loadTargetObject();
        $object->completeLabelFields($fields, $params);
    }

    public function loadFileDeliveriesMessage(): void
    {
        $this->loadFile()->loadRefDeliveries();
        if ($this->_ref_file->_ref_deliveries) {
            foreach ($this->_ref_file->_ref_deliveries as $delivery) {
                $delivery->loadRefMessage();
            }
        }
    }
}
