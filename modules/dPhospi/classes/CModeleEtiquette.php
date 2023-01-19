<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use DOMDocument;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbDT;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CMbPdf;
use Ox\Core\CMbString;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\Vaccination\CInjection;
use Ox\Mediboard\Cabinet\Vaccination\CVaccination;
use Ox\Mediboard\Ccam\CDevisCodage;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Stock\CProductOrderItemReception;
use Ox\Mediboard\System\Forms\CExObject;
use Ox\Mediboard\Urgences\CRPU;

class CModeleEtiquette extends CMbObject
{
    // DB Table key
    public $modele_etiquette_id;

    // DB Fields
    public $nom;
    public $texte;
    public $texte_2;
    public $texte_3;
    public $texte_4;
    public $largeur_page;
    public $hauteur_page;
    public $nb_lignes;
    public $nb_colonnes;
    public $marge_horiz;
    public $marge_vert;
    public $marge_horiz_etiq;
    public $marge_vert_etiq;
    public $hauteur_ligne;
    public $font;
    public $group_id;
    public $show_border;
    public $text_align;

    public $object_class;
    public $object_id;
    public $_ref_object;

    // Form fields
    public $_write_bold;
    public $_write_upper;
    public $_width_etiq;
    public $_height_etiq;
    public $_field_size;

    public $_text_fields;

    public const CONTEXT_CLASSES = [
        CFilesCategory::class,
        CConsultation::class,
        CDevisCodage::class,
        CProductOrderItemReception::class,
        CPatient::class,
        CRPU::class,
        CSejour::class,
        COperation::class,
        CInjection::class
    ];

    /** @var string[][] */
    static $fields;

    static $text_fields = ["texte", "texte_2", "texte_3", "texte_4"];

    static $listfonts = [
        "dejavusansmono" => "DejaVu Sans Mono",
        "freemono"       => "Free Mono",
        "veramo"         => "Vera Sans Mono",
    ];

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "modele_etiquette";
        $spec->key   = "modele_etiquette_id";

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props                     = parent::getProps();
        $props["nom"]              = "str notNull";
        $props["texte"]            = "text notNull";
        $props["texte_2"]          = "text";
        $props["texte_3"]          = "text";
        $props["texte_4"]          = "text";
        $props["largeur_page"]     = "float notNull default|21";
        $props["hauteur_page"]     = "float notNull default|29.7";
        $props["marge_horiz"]      = "float notNull default|0.3";
        $props["marge_vert"]       = "float notNull default|1.3";
        $props["marge_horiz_etiq"] = "float notNull default|0";
        $props["marge_vert_etiq"]  = "float notNull default|0";
        $props["nb_lignes"]        = "num notNull default|8";
        $props["nb_colonnes"]      = "num notNull default|4";
        $props["hauteur_ligne"]    = "float notNull default|8";
        $props["object_id"]        = "ref class|CMbObject meta|object_class purgeable back|modeles_etiquettes";
        $props["object_class"]     = "enum list|" . implode("|", array_keys(self::getContextClasses())) . " notNull show|0";
        $props["font"]             = "text show|0";
        $props["group_id"]         = "ref class|CGroups notNull back|modeles_etiquette";
        $props["show_border"]      = "bool default|0";
        $props["text_align"]       = "enum list|top|middle|bottom default|top";
        $props["_write_bold"]      = "bool";
        $props["_write_upper"]     = "bool";
        $props["_width_etiq"]      = "float";
        $props["_height_etiq"]     = "float";
        $props["_field_size"]      = "num";

        return $props;
    }

    /**
     * @see parent::updateFormFields()
     */
    function updateFormFields()
    {
        parent::updateFormFields();
        $this->_shortview   = $this->_view = $this->nom;
        $this->_width_etiq  = round(($this->largeur_page - 2 * $this->marge_horiz) / $this->nb_colonnes, 2);
        $this->_height_etiq = round(($this->hauteur_page - 2 * $this->marge_vert) / $this->nb_lignes, 2);
    }

    /**
     * @return array|void
     */
    public static function getContextClasses()
    {
        $classmap = CClassMap::getInstance();
        $classes  = [];

        foreach (self::CONTEXT_CLASSES as $_classe) {
            $_classe_short_name           = $classmap->getShortName($_classe);
            $classes[$_classe_short_name] = CAppUI::tr($_classe_short_name);
        }

        return $classes;
    }

    function replaceFields($array_fields)
    {
        $search  = [];
        $replace = [];

        $start_tag = "<TAILLE VALEUR=\"$1\">";
        $end_tag   = "</TAILLE>";

        foreach ($array_fields as $_key => $_field) {
            $_field = CMbString::htmlSpecialChars($_field);

            // Normal
            $search[]  = "%\[([0-9]*)$_key\]%";
            $replace[] = "$start_tag$_field$end_tag";
            // Gras
            $search[]  = "%\*([0-9]*)$_key\*%";
            $replace[] = "$start_tag<b>$_field</b>$end_tag";
            // Majuscule
            $search[]  = "%\+([0-9]*)$_key\+%";
            $replace[] = $start_tag . strtoupper($_field) . $end_tag;
            // Gras + majuscule
            $search[]  = "%#([0-9]*)$_key#%";
            $replace[] = "$start_tag<b>" . strtoupper($_field) . "</b>$end_tag";
        }

        foreach (self::$text_fields as $_field) {
            $this->$_field = preg_replace($search, $replace, $this->$_field ?? '');
        }
    }

    function completeLabelFields(&$fields, $params)
    {
        $fields = array_merge(
            $fields,
            [
                "DATE COURANTE"        => CMbDT::dateToLocale(CMbDT::date()),
                "HEURE COURANTE"       => CMbDT::format(null, "%H:%M"),
                "UTILISATEUR CONNECTE" => CUser::get(),
            ]
        );
    }

    function printEtiquettes($printer_guid = null, $stream = 1)
    {
        // Affectation de la police par défault si aucune n'est choisie
        if ($this->font == "") {
            $this->font = "dejavusansmono";
        }

        // Calcul des dimensions de l'étiquette
        $largeur_etiq = ($this->largeur_page - 2 * $this->marge_horiz - ($this->nb_colonnes - 1) * $this->marge_horiz_etiq) / $this->nb_colonnes;
        $hauteur_etiq = ($this->hauteur_page - 2 * $this->marge_vert - ($this->nb_lignes - 1) * $this->marge_vert_etiq) / $this->nb_lignes;

        // Création du PDF
        $pdf = new CMbPdf("P", "cm", [$this->largeur_page, $this->hauteur_page]);
        $pdf->setFont($this->font, "", $this->hauteur_ligne);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->SetMargins($this->marge_horiz, $this->marge_vert, $this->marge_horiz);
        $pdf->SetAutoPageBreak(0, $this->marge_vert);

        $pdf->AddPage();

        $distinct_texts = 0;
        $textes         = [];

        // La fonction nl2br ne fait qu'ajouter la balise <br />, elle ne supprime pas le \n.
        // Il faut donc le faire manuellement.
        foreach ($this->getTextEtiquette() as $_field) {
            if ($this->$_field || $distinct_texts == 0) {
                $distinct_texts++;
                $textes[$distinct_texts] = preg_replace("/[\t\r\n\f]/", "", utf8_encode(nl2br($this->$_field)));
            }
        }

        $nb_etiqs     = $this->nb_lignes * $this->nb_colonnes;
        $increment    = floor($nb_etiqs / $distinct_texts);
        $current_text = 1;

        if ($this->text_align != "top") {
            $pdf_ex = new CMbPdf("P", "cm", [$largeur_etiq, $hauteur_etiq]);
            $pdf_ex->setFont($this->font, "", $this->hauteur_ligne);
            $pdf_ex->SetMargins(0, 0, 0);
            $pdf_ex->setPrintHeader(false);
            $pdf_ex->setPrintFooter(false);
            $pdf_ex->SetAutoPageBreak(false);
        }

        // Création de la grille d'étiquettes et écriture du contenu.
        for ($i = 0; $i < $nb_etiqs; $i++) {
            if ($i != 0 && $i % $increment == 0 && isset($textes[$current_text + 1])) {
                $current_text++;
            }

            if (round($pdf->GetX() ?? 0) >= round($this->largeur_page - 2 * $this->marge_horiz - ($this->nb_colonnes - 1) * $this->marge_horiz_etiq)) {
                $pdf->SetX(0);
                $pdf->SetLeftMargin($this->marge_horiz);
                $pdf->SetY($pdf->GetY() + $hauteur_etiq + $this->marge_vert_etiq);
            }

            if ($this->show_border) {
                $pdf->Rect($pdf->GetX(), $pdf->GetY(), $largeur_etiq, $hauteur_etiq, "D");
            }

            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->SetLeftMargin($x);

            // On descend le curseur pour ne pas écrire sur la bordure (cas de l'alignement vertical en haut)
            if ($this->text_align === "top") {
                $pdf->SetY($y + 0.2);
            }

            // On affecte la marge droite de manière à ce que la méthode Write fasse un retour chariot
            // lorsque le contenu écrit va dépasser la largeur de l'étiquette
            $pdf->SetRightMargin($this->largeur_page - $x - $largeur_etiq);

            // Evaluation de la hauteur du contenu de la cellule
            // si un alignement spécifique est demandé.
            if ($this->text_align != "top") {
                $pdf_ex->AddPage();

                self::writeFragments($this, $textes[$current_text], $pdf_ex);

                $pdf_y    = $pdf->getY();
                $pdf_ex_y = $pdf_ex->getY();

                switch ($this->text_align) {
                    case "middle":
                        $pdf->setY($pdf_y - 0.2 + ($hauteur_etiq - $pdf_ex_y) / 2);
                        break;
                    case "bottom":
                        $pdf->setY($pdf_y - 0.4 + $hauteur_etiq - $pdf_ex_y);
                }
            }

            self::writeFragments($this, $textes[$current_text], $pdf);

            $x = $x + $largeur_etiq + $this->marge_horiz_etiq;
            $pdf->SetY($y);
            $pdf->SetX($x);
        }

        if ($printer_guid) {
            $file             = new CFile;
            $file->_file_path = tempnam("/tmp", "etiq");
            file_put_contents($file->_file_path, $pdf->Output("$this->nom.pdf", "S"));

            $printer = CStoredObject::loadFromGuid($printer_guid);
            $printer->sendDocument($file);

            unlink($file->_file_path);
        } else {
            if ($stream) {
                $pdf->Output("$this->nom.pdf", "I");
            } else {
                return $pdf->OutPut("$this->nom.pdf", "S");
            }
        }
    }

    static function writeFragments($modele, $texte, &$pdf)
    {
        $dom   = new DOMDocument();
        $texte = preg_replace("/&GT/", "&gt", $texte);
        $texte = preg_replace("/&LT/", "&lt", $texte);
        $texte = preg_replace("/&QUOT/", "&quot", $texte);
        $dom->loadXML("<root>$texte</root>");
        $was_barcode = $size_barcode = 0;

        foreach ($dom->firstChild->childNodes as $_node) {
            $size = "";

            switch ($_node->nodeName) {
                case "br":
                    $fragment = "<br />";
                    break;
                case "TAILLE":
                    $fragment = $_node->nodeValue;
                    if ($_node->firstChild && $_node->firstChild->nodeName == "b") {
                        $fragment = "<b>$fragment</b>";
                    }
                    $size = $_node->getAttributeNode("VALEUR")->value;
                    break;
                default:
                    $fragment = $_node->nodeValue;
            }

            if (!$size) {
                $size = $modele->hauteur_ligne;
            }

            if (preg_match("/@BARCODE_(.*)@/", $fragment, $matches) == 1) {
                $barcode      = $matches[1];
                $size_barcode = $size;
                $barcode_x     = $pdf->getX() + 0.15;
                $barcode_y     = $pdf->getY();
                $barcode_width = strlen($barcode) * 0.4 + 0.4;
                $pdf->writeBarcode($barcode_x, $barcode_y, $barcode_width, $size / 9, "C128B", 1, null, null, $barcode, 25);
                $pdf->setX($barcode_x + $barcode_width);
                $was_barcode = 1;
            } else {
                $pdf->setFont($modele->font, "", $size);
                if ($was_barcode) {
                    if ($_node->nodeName == "br") {
                        $pdf->setY($pdf->getY() + $size_barcode / 10);
                        $was_barcode = 0;
                    }
                }

                $pdf->WriteHTML($fragment, false);
            }
        }
    }

    /**
     * @param CStoredObject $object
     *
     * @return void
     * @todo redefine meta raf
     * @deprecated
     */
    public function setObject(CStoredObject $object)
    {
        CMbMetaObjectPolyfill::setObject($this, $object);
    }

    /**
     * @param bool $cache
     *
     * @return bool|CStoredObject|CExObject|null
     * @throws Exception
     * @deprecated
     * @todo redefine meta raf
     */
    public function loadTargetObject($cache = true)
    {
        return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
    }

    /**
     * @inheritDoc
     * @todo remove
     */
    function loadRefsFwd()
    {
        parent::loadRefsFwd();
        $this->loadTargetObject();
    }

    /**
     *
     */
    public static function getFields()
    {
        return [
            "CPatient"                   => CPatient::getFieldsEtiq(),
            "CSejour"                    => CSejour::getFieldsEtiq(),
            "COperation"                 => COperation::getFieldsEtiq(),
            "CFilesCategory"             => CFilesCategory::getFieldsEtiq(),
            "CProductOrderItemReception" => CProductOrderItemReception::getFieldsEtiq(),
            "General"                    => ["DATE COURANTE", "HEURE COURANTE", "UTILISATEUR CONNECTE"],
            "CGroups"                    => CGroups::getFieldsEtiq(),
            "CInjection"                 => CInjection::getFieldsEtiq()
        ];
    }

    public function getTextEtiquette(): array
    {
        return $this->_text_fields ?? self::$text_fields;
    }

    public function setTextEtiquette(array $text_fields): void
    {
        $this->_text_fields = $text_fields;
    }
}
