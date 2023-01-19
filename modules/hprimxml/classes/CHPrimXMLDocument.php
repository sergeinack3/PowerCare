<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use DOMElement;
use DOMNode;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbPath;
use Ox\Core\CMbRange;
use Ox\Core\CMbString;
use Ox\Core\CMbXMLDocument;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Hprimxml\Event\CHPrimXMLEventPatient;
use Ox\Interop\InteropResources\CInteropResources;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Cabinet\CExamIgs;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\Ccam\CFraisDivers;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CItemPrestation;
use Ox\Mediboard\Maternite\CNaissance;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CINSPatient;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CTraitement;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\SalleOp\CActeCCAM;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\Ssr\CActiviteCdARR;
use Ox\Mediboard\Ssr\CLigneActivitesRHS;
use Ox\Mediboard\Ssr\CRHS;

/**
 * Class CHPrimXMLDocument
 * H'XML Document
 */
class CHPrimXMLDocument extends CMbXMLDocument
{
    /**
     * @var array Liste des constantes disponibles dans le schéma et dans Mediboard
     */
    static $list_constantes = [
        "poids"  => "pds",
        "taille" => "tll",
    ];
    public $evenement;
    public $finalpath;
    public $documentfinalprefix;
    public $documentfinalfilename;
    public $sentFiles = [];
    public $group_id;
    public $type;

    // Behaviour fields
    public $sous_type;
    /**
     * @var CInteropSender
     */
    public $_ref_sender;
    /**
     * @var CInteropReceiver
     */
    public $_ref_receiver;
    /**
     * @var CEchangeHprim
     */
    public $_ref_echange_hprim;

    /**
     * Construct
     *
     * @param string $dirschemaname  Schema name directory
     * @param string $schemafilename Schema filename
     * @param string $mod_name       Module name
     *
     * @return CHPrimXMLDocument
     */
    function __construct($dirschemaname, $schemafilename = null, $mod_name = "interopResources")
    {
        parent::__construct();

        $this->formatOutput = false;

        $this->patharchiveschema = "modules/$mod_name/resources/hprimxml";
        $this->schemapath        = "$this->patharchiveschema/$dirschemaname";
        $this->schemafilename    = "$this->schemapath/$schemafilename.xsd";
        $this->documentfilename  = "$this->schemapath/document.xml";
        $this->finalpath         = CFile::getDirectory() . "/$mod_name/$dirschemaname";

        $this->now = time();
    }

    /**
     * Affiche le message au format xml
     *
     * @param String $message message
     *
     * @return String
     */
    static function showxml($message)
    {
        return CMbString::highlightCode("xml", $message);
    }

    /**
     * Permet de remplir la variable $_contain avec la structure du document
     *
     * @param CHPrimXMLDocument $dom DOMDocument
     *
     * @return array
     */
    public static function parse(CHPrimXMLDocument $dom): array {
        $result = [];
        $result["validate"] = [];
        $returnErrors   = $dom->schemaValidate(null, true, false);

        if (!is_bool($returnErrors)) {
            $tabErrors = array_filter(explode("\n", $returnErrors));
            $validate  = array_unique($tabErrors);
            $result["validate"] = $validate;
        }

        return $result;
    }

    /**
     * Check schema
     *
     * @return bool
     */
    function checkSchema()
    {
        if (!is_dir($this->schemapath)) {
            $msg = "HPRIMXML schemas are missing. Please add files in '$this->schemapath/' directory";
            trigger_error($msg, E_USER_WARNING);

            return false;
        }

        if (!is_file($this->schemafilename)) {
            $schema = new CHPrimXMLSchema();
            $schema->importSchemaPackage($this->schemapath);
            $schema->purgeIncludes();
            $schema->purgeImportedNamespaces();
            $schema->save($this->schemafilename);
        }

        return true;
    }

    /**
     * @see parent::addNameSpaces
     */
    function addNameSpaces()
    {
        // Ajout des namespace pour XML Spy
        $this->addAttribute($this->documentElement, "xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
        $this->addAttribute($this->documentElement, "xsi:schemaLocation", "http://www.hprim.org/hprimXML schema.xml");
    }

    /**
     * @see parent::saveTempFile
     */
    function saveTempFile()
    {
        parent::save(utf8_encode($this->documentfilename));
    }

    /**
     * @see parent::saveFinalFile
     */
    function saveFinalFile()
    {
        $this->documentfinalfilename = "$this->finalpath/$this->documentfinalprefix-$this->now.xml";
        CMbPath::forceDir(dirname($this->documentfinalfilename));
        parent::save($this->documentfinalfilename);
    }

    /**
     * @see parent::getSentFiles
     */
    function getSentFiles()
    {
        $pattern = "$this->finalpath/$this->documentfinalprefix-*.xml";
        foreach (glob($pattern) as $sentFile) {
            $baseName = basename($sentFile);
            $matches  = null;
            preg_match("`^[[:alpha:]]{2,3}[[:digit:]]{6}-([[:digit:]]*)\.xml$`", $baseName, $matches);
            $timeStamp         = $matches[1];
            $this->sentFiles[] = [
                "name"     => $baseName,
                "path"     => $sentFile,
                "datetime" => CMbDT::strftime("%Y-%m-%d %H:%M:%S", $timeStamp),
            ];
        }
    }

    /**
     * Ajout de l'entête du message
     *
     * @param DOMNode $elParent Node
     * @param int     $group_id Group id
     *
     * @return void
     */
    function addEnteteMessage(DOMNode $elParent, $group_id = null)
    {
        $echg_hprim      = $this->_ref_echange_hprim;
        $dest            = $this->_ref_receiver;
        $identifiant     = $echg_hprim->_id ? str_pad($echg_hprim->_id, 6, '0', STR_PAD_LEFT) : "ES{$this->now}";
        $date_production = $echg_hprim->_id ? $echg_hprim->date_production : CMbDT::dateTimeXML();

        $this->addAttribute($elParent, "acquittementAttendu", self::convertToBool($dest->_configs["receive_ack"]));

        $enteteMessage = $this->addElement($elParent, "enteteMessage");
        $this->addElement($enteteMessage, "identifiantMessage", $identifiant);
        $this->addDateTimeElement($enteteMessage, "dateHeureProduction", $date_production);

        $group = null;
        if ($group_id) {
            $group = CGroups::get($group_id);
        }

        if (!$group) {
            $group = CGroups::get($dest->group_id);
        }

        if (!$group) {
            $group = CGroups::loadCurrent();
        }

        $emetteur = $this->addElement($enteteMessage, "emetteur");
        $agents   = $this->addElement($emetteur, "agents");
        $this->addAgent(
            $agents,
            "application",
            CAppUI::conf('hprimxml CHPrimXMLDocument emetteur_application_code', $group),
            CAppUI::conf('hprimxml CHPrimXMLDocument emetteur_application_libelle', $group)
        );

        $group->loadLastId400();
        $user = CAppUI::$user;
        $this->addAgent($agents, "acteur", "user$user->_id", $user->_view);
        $code_systeme = (CAppUI::conf('hprimxml code_transmitter_sender') == "finess") ? $group->finess : CAppUI::conf(
            'mb_id'
        );
        $this->addAgent($agents, $this->getAttSysteme(), $code_systeme, $group->text);

        $destinataire = $this->addElement($enteteMessage, "destinataire");
        $agents       = $this->addElement($destinataire, "agents");
        if ($dest->code_appli) {
            $this->addAgent($agents, "application", $dest->code_appli, "");
        }

        if ($dest->code_acteur) {
            $this->addAgent($agents, "acteur", $dest->code_acteur, "");
        }

        $this->addAgent($agents, $this->getAttSysteme(), $dest->code_syst, $dest->libelle);
    }

    /**
     * Convert to H'XML bool
     *
     * @param bool   $field        Field
     * @param string $defaultValue Default value
     *
     * @return string "Oui" or "Non"
     */
    static function convertToBool($field = null, $defaultValue = "oui")
    {
        if ($field === null) {
            return $defaultValue;
        }

        return $field ? "oui" : "non";
    }

    /**
     * @see parent::addElement
     */
    function addElement(DOMNode $elParent, $elName, $elValue = null, $elNS = "http://www.hprim.org/hprimXML")
    {
        return parent::addElement($elParent, $elName, $elValue, $elNS);
    }

    function addDateTimeElement($elParent, $elName, $dateValue = null, ?string $elNS = "http://www.hprim.org/hprimXML")
    {
        return parent::addDateTimeElement($elParent, $elName, $dateValue, $elNS);
    }

    function addAgent(DOMNode $elParent, $categorie, $code, $libelle)
    {
        $agent = $this->addCodeLibelle($elParent, "agent", $code, $libelle);
        $this->addAttribute($agent, "categorie", $categorie);

        return $agent;
    }

    function addCodeLibelle(DOMNode $elParent, $nodeName, $code, $libelle, $commentaire = null)
    {
        $codeLibelle = $this->addElement($elParent, $nodeName);
        $code        = str_replace(" ", "", $code);
        $this->addTexte($codeLibelle, "code", $code, 10);

        if ($libelle) {
            $this->addTexte($codeLibelle, "libelle", $libelle, 35);
        }

        if ($commentaire) {
            $this->addTexte($codeLibelle, "commentaire", $commentaire, 4000);
        }

        return $codeLibelle;
    }

    /**
     * Ajout de l'élèment texte
     *
     * @param DOMNode $elParent  Noeud parent
     * @param String  $elName    Nom de l'élément
     * @param String  $elValue   Valeur de l'élément
     * @param int     $elMaxSize Taille maximum de la valeur
     *
     * @return DOMElement
     */
    function addTexte(DOMNode $elParent, $elName, $elValue, $elMaxSize = 35)
    {
        $elValue = substr($elValue, 0, $elMaxSize);

        return $this->addElement($elParent, $elName, $elValue);
    }

    /**
     * Récupération de l'attribut système
     *
     * @return string
     */
    function getAttSysteme()
    {
        $systeme = "système";
        $sender  = $this->_ref_sender;

        if ($sender && $sender->_configs) {
            $systeme = $sender->_configs["att_system"];
        }

        return (CAppUI::conf("hprimxml " . $this->evenement . " version") < "1.07") ?
            $systeme : CMbString::removeDiacritics($systeme);
    }

    /**
     * Génération de l'évènement
     *
     * @param CMbObject $mbObject   Object
     * @param bool      $referent   Référent ?
     * @param bool      $initiateur Initiateur ?
     * @param int       $group_id   Group id
     *
     * @return string
     */
    function generateTypeEvenement(CMbObject $mbObject, $referent = false, $initiateur = false, $group_id = null)
    {
        $echg_hprim                  = new CEchangeHprim();
        $echg_hprim->date_production = CMbDT::dateTime();
        $echg_hprim->sender_id       = $this->_ref_sender ? $this->_ref_sender->_id : null;
        $echg_hprim->receiver_id     = $this->_ref_receiver->_id;
        $echg_hprim->group_id        = $this->_ref_receiver->group_id;
        $echg_hprim->type            = $this->type;
        $echg_hprim->sous_type       = $this->sous_type;
        $echg_hprim->object_id       = $mbObject->_id;
        $echg_hprim->_message        = utf8_encode($this->saveXML());
        $echg_hprim->initiateur_id   = $initiateur;
        $echg_hprim->setObjectClassIdPermanent($mbObject);
        $echg_hprim->store();

        // Chargement des configs du destinataire
        $dest = $this->_ref_receiver;
        $dest->loadConfigValues();

        $this->_ref_echange_hprim = $echg_hprim;

        $this->generateEnteteMessage(null, null, $group_id);
        $this->generateFromOperation($mbObject, $referent);

        $doc_valid                  = $this->schemaValidate(null, false, $this->_ref_receiver->display_errors);
        $echg_hprim->message_valide = $doc_valid ? 1 : 0;

        $msg = $this->saveXML();

        // On sauvegarde toujours en base le message en UTF-8
        $echg_hprim->_message = utf8_encode($msg);

        $echg_hprim->store();

        // On envoie le contenu et NON l'entête en UTF-8 si le destinataire est en UTF-8
        return ($dest->_configs["encoding"] == "UTF-8") ? utf8_encode($msg) : $msg;
    }

    /**
     * Generate header message
     *
     * @param string $type     Even type
     * @param bool   $version  Version
     * @param int    $group_id Group id
     *
     * @return void
     */
    function generateEnteteMessage($type, $version = true, $group_id = null)
    {
    }

    /**
     * Generate content message
     *
     * @param CMbObject $mbObject Object
     * @param bool      $referent Is referring ?
     *
     * @return void
     */
    function generateFromOperation(CMbObject $mbObject, $referent = false)
    {
    }

    /**
     * Try to validate the document against a schema will trigger errors when not validating
     *
     * @param string $filename       Path of schema, use document inline schema if null
     * @param bool   $returnErrors   Return errors
     * @param bool   $display_errors Display errors
     *
     * @return boolean
     */
    function schemaValidate($filename = null, $returnErrors = false, $display_errors = true)
    {
        // Pas de validation car le module des ressources n'est pas installé
        if (!CModule::getInstalled("interopResources")) {
            trigger_error("interopResources-msg-Missing", E_USER_NOTICE);

            return true;
        }

        $file = $filename ? $filename : $this->schemafilename;
        // Pas de validation car les schémas ne sont pas présents
        if (!CInteropResources::fileExists($file)) {
            trigger_error("Schemas are missing. Please add files in '$file' directory", E_USER_NOTICE);

            return true;
        }

        if (!CAppUI::conf("hprimxml " . $this->evenement . " validation")) {
            return true;
        }

        return parent::schemaValidate($filename, $returnErrors, $display_errors);
    }

    /**
     * Get content XML
     *
     * @return array
     */
    public function getContentsXML(): array
    {
        return [];
    }

    /**
     * Récupération de l'identifiant source (emetteur)
     *
     * @param DOMNode $node   Node
     * @param bool    $valeur Valeur
     *
     * @return string
     */
    function getIdSource(DOMNode $node, $valeur = true)
    {
        $xpath = new CHPrimXPath($this);

        $identifiant = $xpath->queryUniqueNode("hprim:identifiant", $node);

        if ($valeur) {
            // Obligatoire pour MB
            $emetteur = $xpath->queryUniqueNode("hprim:emetteur", $identifiant, false);

            return $xpath->queryTextNode("hprim:valeur", $emetteur);
        } else {
            return $xpath->queryTextNode("hprim:emetteur", $identifiant);
        }
    }

    /**
     * Récupération de l'identifiant source (emetteur)
     *
     * @param DOMNode $node   Node
     * @param bool    $valeur Valeur
     *
     * @return string
     */
    function getIdCible(DOMNode $node, $valeur = true)
    {
        $xpath = new CHPrimXPath($this);

        $identifiant = $xpath->queryUniqueNode("hprim:identifiant", $node);

        if ($valeur) {
            $recepteur = $xpath->queryUniqueNode("hprim:recepteur", $identifiant);

            return $xpath->queryTextNode("hprim:valeur", $recepteur);
        } else {
            return $xpath->queryTextNode("hprim:recepteur", $identifiant);
        }
    }

    function addCodeValueCommentaire(
        DOMNode $elParent,
        $nodeName,
        $code,
        $value,
        $dictionnaire = null,
        $commentaire = null
    ) {
        $codeLibelleCommentaire = $this->addElement($elParent, $nodeName);

        $this->addTexte($codeLibelleCommentaire, "code", str_replace(" ", "", $code), 10);
        $this->addTexte($codeLibelleCommentaire, "value", $value, 35);
        $this->addTexte($codeLibelleCommentaire, "dictionnaire", $dictionnaire, 12);
        $this->addCommentaire($codeLibelleCommentaire, $commentaire);

        return $codeLibelleCommentaire;
    }

    function addCommentaire(DOMNode $elParent, $commentaire)
    {
        $this->addTexte($elParent, "commentaire", $commentaire, 4000);
    }

    function addActeCCAM(DOMNode $elParent, CActeCCAM $mbActeCCAM, CCodable $codable)
    {
        $sejour = $codable->loadRefSejour();

        $acteCCAM = $this->addElement($elParent, "acteCCAM");

        if (CAppUI::conf("sa CSa send_acte_immediately", $this->_ref_receiver->loadRefGroup())) {
            $current_log = $mbActeCCAM->loadLastLog();
            switch ($current_log->type) {
                case "store":
                    $action = "modification";
                    break;
                case "delete":
                    $action = "suppression";
                    break;
                default:
                    $action = CAppUI::conf("hprimxml $this->evenement version") == "2.00" ? "creation" : "création";
            }
        } else {
            $action = CAppUI::conf("hprimxml $this->evenement version") == "2.00" ? "creation" : "création";
        }

        $this->addAttribute($acteCCAM, "action", $action);
        $this->addAttribute($acteCCAM, "facturable", self::convertToBool($mbActeCCAM->facturable));
        $this->addAttributeYes($acteCCAM, "valide");
        $this->addAttributeNo($acteCCAM, "documentaire");
        $this->addAttribute($acteCCAM, "gratuit", self::convertToBool($mbActeCCAM->gratuit));
        if ($mbActeCCAM->_rembex) {
            $this->addAttributeYes($acteCCAM, "remboursementExceptionnel");
        }

        $identifiant = $this->addElement($acteCCAM, "identifiant");
        $this->addElement($identifiant, "emetteur", "acte{$mbActeCCAM->_id}");

        $this->addElement($acteCCAM, "codeActe", $mbActeCCAM->code_acte);
        if (CAppUI::conf("hprimxml $this->evenement version") == "2.00" && $mbActeCCAM->code_extension) {
            $this->addElement($acteCCAM, "codeActeExtensionPMSI", $mbActeCCAM->code_extension);
        }
        $this->addElement($acteCCAM, "codeActivite", $mbActeCCAM->code_activite);
        $this->addElement($acteCCAM, "codePhase", $mbActeCCAM->code_phase);

        // Prise en charge - ALD
        if ($mbActeCCAM->ald) {
            $priseCharge = $this->addElement($acteCCAM, "priseCharge");
            $this->addAttribute($priseCharge, "indicateurParcoursSoins", "acteConsultALD");
        }

        // Date et heure de l'opération
        if ((CAppUI::conf("hprimxml date_heure_acte") == "operation") && $codable instanceof COperation) {
            $date = $codable->date;

            $time_operation = ($codable->time_operation == "00:00:00") ? null : "$date $codable->time_operation";
            $date_heure     = CValue::first(
                $codable->debut_op,
                $codable->entree_salle,
                $time_operation,
                $codable->horaire_voulu
            );
            $heure          = CMbDT::time($date_heure);

            if ($date_heure < $sejour->entree) {
                $date  = CMbDT::date($sejour->entree);
                $heure = CMbDT::time($sejour->entree);
            }
            if ($date_heure > $sejour->sortie) {
                $date  = CMbDT::date($sejour->sortie);
                $heure = CMbDT::time($sejour->sortie);
            }
        } // Date et heure de l'exécution de l'acte
        else {
            $date  = CMbDT::date($mbActeCCAM->execution);
            $heure = CMbDT::time($mbActeCCAM->execution);
        }

        $execute = $this->addElement($acteCCAM, "execute");
        $this->addElement($execute, "date", $date);
        $this->addElement($execute, "heure", $heure);

        $mbExecutant      = $mbActeCCAM->loadRefExecutant();
        $executant        = $this->addElement($acteCCAM, "executant");
        $medecins         = $this->addElement($executant, "medecins");
        $medecinExecutant = $this->addElement($medecins, "medecinExecutant");
        $this->addAttributeYes($medecinExecutant, "principal");
        $medecin = $this->addElement($medecinExecutant, "medecin");
        $this->addProfessionnelSante($medecin, $mbExecutant);

        // UF médicale du séjour
        $ufs = $sejour->getUFs();
        if (CMbArray::get($ufs, "medicale")) {
            $uf_med = $ufs["medicale"];
            $this->addUniteFonctionnelle($executant, $uf_med->code, $uf_med->libelle);
        }

        $modificateurs = $this->addElement($acteCCAM, "modificateurs");
        foreach ($mbActeCCAM->_modificateurs as $mbModificateur) {
            $this->addElement($modificateurs, "modificateur", $mbModificateur);
        }

        if ($mbActeCCAM->code_association) {
            $this->addElement($acteCCAM, "codeAssociationNonPrevue", $mbActeCCAM->code_association);
        }

        $type_anesth = null;
        if ($mbActeCCAM->_anesth && $mbActeCCAM->object_class == "COperation") {
            $type_anesth = $mbActeCCAM->loadTargetObject()->loadRefTypeAnesth()->ext_doc;
        }

        $extension_documentaire = $mbActeCCAM->extension_documentaire ? $mbActeCCAM->extension_documentaire : $type_anesth;
        $this->addElement($acteCCAM, "codeExtensionDocumentaire", $extension_documentaire);

        if ($mbActeCCAM->position_dentaire) {
            // Gestion des dents
            $positionsDentaires = $this->addElement($acteCCAM, "positionsDentaires");
            foreach (explode("|", $mbActeCCAM->position_dentaire) as $_dent) {
                $this->addElement($positionsDentaires, "positionDentaire", $_dent);
            }
        }

        $montant = $this->addElement($acteCCAM, "montant");
        if ($mbActeCCAM->montant_depassement > 0) {
            $montantDepassement = $this->addElement(
                $montant,
                "montantDepassement",
                sprintf("%.2f", $mbActeCCAM->montant_depassement)
            );
            if (CAppUI::conf("dPpmsi systeme_facturation") == "siemens") {
                if (CAppUI::gconf("dPsalleOp CActeCCAM allow_send_reason_exceeding")) {
                    $this->addAttribute($montantDepassement, "motif", "d");
                }
            } else {
                if ($mbActeCCAM->motif_depassement) {
                    /* Limitation du motif aux valeurs possibles dans la norme */
                    if (in_array($mbActeCCAM->motif_depassement, ['d', 'e', 'f', 'n', 'da'])) {
                        $this->addAttribute($montantDepassement, "motif", $mbActeCCAM->motif_depassement);
                    } /* Conversion de la valeur pour le dépassement autorisé (a dans le cahier des charges SESAM-Vitale, da dans la norme) */
                    elseif ($mbActeCCAM->motif_depassement == 'a') {
                        $this->addAttribute($montantDepassement, "motif", 'da');
                    }
                }
            }
        }

        return $acteCCAM;
    }

    function addProfessionnelSante(DOMNode $elParent, $mbMediuser, $lien = null)
    {
        if ($lien) {
            $this->addAttribute($elParent, "lien", $lien);
        }

        $receiver = $this->_ref_receiver;
        if ($receiver->_configs["build_id_professionnel_sante"] == "rpps" && $mbMediuser->rpps) {
            $this->addElement($elParent, "noRPPS", $mbMediuser->rpps);
        } else {
            $this->addElement($elParent, "numeroAdeli", $mbMediuser->adeli);
        }

        $identification = $this->addElement($elParent, "identification");

        $idex = CIdSante400::getMatchFor($mbMediuser, $this->getTagMediuser());

        $this->addElement($identification, "code", $idex->_id ? $idex->id400 : "prat$mbMediuser->user_id");
        $this->addElement($identification, "libelle", $mbMediuser->_view);
        $personne = $this->addElement($elParent, "personne");
        $this->addElement($personne, "nomUsuel", $mbMediuser->_user_last_name);
        $prenoms = $this->addElement($personne, "prenoms");
        $this->addElement($prenoms, "prenom", $mbMediuser->_user_first_name);
    }

    /**
     * Récupération du tag du mediuser
     *
     * @return string
     */
    function getTagMediuser()
    {
        $this->_ref_echange_hprim->loadRefsInteropActor();

        return $this->_ref_echange_hprim->_ref_receiver->_tag_mediuser;
    }

    /**
     * Ajout de l'unité fonctionnelle
     *
     * @param DOMNode $elParent   Parent element
     * @param string  $uf_code    Code
     * @param string  $uf_libelle Libellé
     *
     * @return void|null
     */
    function addUniteFonctionnelle(DOMNode $elParent, $uf_code = null, $uf_libelle = null)
    {
        if (!$uf_code) {
            return null;
        }

        $uf_code = CMbString::removeDiacritics($uf_code);
        $uf_code = str_replace("'", "", $uf_code);
        $uf_code = CMbString::convertHTMLToXMLEntities($uf_code);

        $this->addCodeLibelle(
            $elParent,
            "uniteFonctionnelle",
            substr($uf_code, 0, 10),
            CMbString::removeDiacritics($uf_libelle)
        );
    }

    function addActeNGAP(DOMNode $elParent, CActeNGAP $mbActeNGAP, CCodable $codable)
    {
        $sejour = $codable->loadRefSejour();

        $acteNGAP = $this->addElement($elParent, "acteNGAP");

        if (CAppUI::conf("sa CSa send_acte_immediately", $this->_ref_receiver->loadRefGroup())) {
            $current_log = $mbActeNGAP->loadLastLog();
            switch ($current_log->type) {
                case "store":
                    $action = "modification";
                    break;
                case "delete":
                    $action = "suppression";
                    break;
                default:
                    $action = CAppUI::conf("hprimxml $this->evenement version") == "2.00" ? "creation" : "création";
            }
        } else {
            $action = CAppUI::conf("hprimxml $this->evenement version") == "2.00" ? "creation" : "création";
        }

        $this->addAttribute($acteNGAP, "action", $action);

        // executionNuit
        if ($mbActeNGAP->complement == "N") {
            // non     non réalisé de nuit
            // 1t      réalisé 1re tranche de nuit
            // 2t      2me tranche
            $executionNuit = "non";

            $time = CMbDT::time($mbActeNGAP->execution);
            if (("20:00:00" <= $time && $time <= "23:59:59") || ("06:00:00" <= $time && $time < "08:00:00")) {
                $executionNuit = "1t";
            } elseif ("00:00:00" <= $time && $time < "05:59:59") {
                $executionNuit = "2t";
            }
            $this->addAttribute($acteNGAP, "executionNuit", $executionNuit);
        }

        // executionDimancheJourFerie
        if ($mbActeNGAP->complement == "F") {
            $this->addAttributeYes($acteNGAP, "executionDimancheJourFerie");
        }

        $this->addAttribute($acteNGAP, "gratuit", self::convertToBool($mbActeNGAP->gratuit));

        $identifiant = $this->addElement($acteNGAP, "identifiant");
        $this->addElement($identifiant, "emetteur", "acte{$mbActeNGAP->_id}");

        $this->addElement($acteNGAP, "lettreCle", $mbActeNGAP->code);
        $this->addElement(
            $acteNGAP,
            "coefficient",
            $mbActeNGAP->demi ? $mbActeNGAP->coefficient * 0.5 : $mbActeNGAP->coefficient
        );
        // dénombrement doit être égale à 1 pour les actes ngap (CS, C etc....),
        // elle varie seulement pour les actes de Biologie "dénombrement = nombre de code affinés"
        // $this->addElement($acteNGAP, "denombrement" , 1);
        $this->addElement($acteNGAP, "quantite", $mbActeNGAP->quantite);

        $execute = $this->addElement($acteNGAP, "execute");
        $this->addElement($execute, "date", CMbDT::date($mbActeNGAP->execution));
        $this->addElement($execute, "heure", CMbDT::time($mbActeNGAP->execution));

        $prestataire = $this->addElement($acteNGAP, "prestataire");
        $medecins    = $this->addElement($prestataire, "medecins");

        // Médecin exécutant
        $executant = $mbActeNGAP->loadRefExecutant();
        $medecin   = $this->addElement($medecins, "medecin");
        $this->addProfessionnelSante($medecin, $executant, "exec");

        // Médecin prescripteur
        $prescripteur = $mbActeNGAP->loadRefPrescripteur();
        if ($prescripteur && $prescripteur->_id) {
            $medecin = $this->addElement($medecins, "medecin");
            $this->addProfessionnelSante($medecin, $prescripteur, "prsc");
        }

        // UF médicale du séjour
        $ufs = $sejour->getUFs();
        if (CMbArray::get($ufs, "medicale")) {
            $uf_med = $ufs["medicale"];
            $this->addUniteFonctionnelle($prestataire, $uf_med->code, $uf_med->libelle);
        }

        // Prise en charge - ALD
        if ($mbActeNGAP->ald) {
            $priseCharge = $this->addElement($acteNGAP, "priseCharge");
            $this->addAttribute($priseCharge, "indicateurParcoursSoins", "acteConsultALD");
        }

        $montant = $this->addElement($acteNGAP, "montant");
        if ($mbActeNGAP->montant_depassement > 0) {
            $this->addElement($montant, "montantDepassement", sprintf("%.2f", $mbActeNGAP->montant_depassement));
        }

        return $acteNGAP;
    }

    function addPatientError(DOMNode $elParent, $data)
    {
        if (!$data) {
            return;
        }
        $patient = $this->importNode($data["patient"], true);
        $elParent->appendChild($patient);
    }

    /**
     * Ajout de l'élément patient
     *
     * @param DOMNode  $elParent  Elément parent
     * @param CPatient $mbPatient Patient
     * @param bool     $referent  Utilisation identifiant ou IPP
     * @param bool     $light     Version allégé
     *
     * @return void
     */
    function addPatient(DOMNode $elParent, CPatient $mbPatient, $referent = false, $light = false)
    {
        $identifiant = $this->addElement($elParent, "identifiant");

        if (!$referent) {
            $this->addIdentifiantPart($identifiant, "emetteur", $mbPatient->_id, $referent);
            if ($mbPatient->_IPP) {
                $this->addIdentifiantPart($identifiant, "recepteur", $mbPatient->_IPP, $referent);
            }
        } else {
            $this->addIdentifiantPart($identifiant, "emetteur", $mbPatient->_IPP, $referent);

            if (isset($mbPatient->_id400)) {
                $this->addIdentifiantPart($identifiant, "recepteur", $mbPatient->_id400, $referent);
            }
        }

        if (CAppUI::conf("hprimxml $this->evenement version") >= "1.07") {
            $ins = $mbPatient->loadRefsINS();
            if ($ins) {
                $numero_identifiant = $this->addElement($identifiant, "numeroIdentifiantSante");
                //todo savoir à quoi correspond identifiant
                $this->addElement($numero_identifiant, "identifiant", "test");
                foreach ($ins as $_ins) {
                    if ($_ins->type == "A") {
                        continue;
                    }
                    $this->addINSC($numero_identifiant, $_ins);
                }
            }
        }

        // Ajout typePersonnePhysique
        $this->addPersonnePhysique($elParent, $mbPatient, $light);
    }

    function addIdentifiantPart(DOMNode $elParent, $partName, $partValue, $referent = null)
    {
        $part = $this->addElement($elParent, $partName);
        $this->addTexte($part, "valeur", $partValue, 17);
        $this->addAttribute($part, "etat", "permanent");
        $this->addAttribute($part, "portee", "local");
        $this->addAttribute($part, "referent", self::convertToBool($referent));
    }

    /**
     * Ajout de l'élément INSC
     *
     * @param DOMNode     $elParent Elément parent
     * @param CINSPatient $ins      INS
     *
     * @return void
     */
    function addINSC(DOMNode $elParent, CINSPatient $ins)
    {
        $insc = $this->addElement($elParent, "insC");
        $this->addElement($insc, "valeur", $ins->ins);
        //Dans le cas où il n'y a pas de date, pas d'envoie de date erroné
        if (!$ins->date) {
            return;
        }
        $this->addElement($insc, "dateEffet", CMbDT::date($ins->date));
    }

    function addPersonnePhysique($elParent, $mbPatient, $light = false)
    {
        $personnePhysique = $this->addElement($elParent, "personnePhysique");

        $sexeConversion = [
            "m" => "M",
            "f" => "F",
        ];
        $sexe           = $mbPatient->sexe ? $sexeConversion[$mbPatient->sexe] : "I";
        $this->addAttribute($personnePhysique, "sexe", $sexe);

        // Ajout typePersonne
        $this->addPersonne($personnePhysique, $mbPatient, $light);

        $dateNaissance = $this->addElement($personnePhysique, "dateNaissance");
        $this->addElement($dateNaissance, "date", $mbPatient->naissance);

        $lieuNaissance = $this->addElement($personnePhysique, "lieuNaissance");
        $this->addElement($lieuNaissance, "ville", $mbPatient->lieu_naissance);
        if ($mbPatient->pays_naissance_insee) {
            $this->addElement($lieuNaissance, "pays", str_pad($mbPatient->pays_naissance_insee, 3, '0', STR_PAD_LEFT));
        }
        $this->addElement($lieuNaissance, "codePostal", $mbPatient->cp_naissance);
    }

    function addPersonne($elParent, $mbPersonne, $light = false)
    {
        $personne                = [];
        $civiliteHprimConversion = [
            "mme"  => "mme",
            "mlle" => "mlle",
            "m"    => "mr",
            "dr"   => "dr",
            "pr"   => "pr",
            "enf"  => "enf",
        ];

        if ($mbPersonne instanceof CPatient) {
            $personne['nom']          = $mbPersonne->nom;
            $personne['nomNaissance'] = $mbPersonne->nom_jeune_fille;
            if (isset($mbPersonne->_prenoms)) {
                foreach ($mbPersonne->_prenoms as $mbKey => $mbPrenom) {
                    if ($mbKey < 3) {
                        $personne['prenoms'][] = $mbPrenom;
                    }
                }
            }
            if (!$light) {
                $personne['civilite'] = $mbPersonne->civilite;
            }
            $personne['ligne']      = $mbPersonne->adresse;
            $personne['ville']      = $mbPersonne->ville;
            $personne['pays']       = $mbPersonne->pays_insee ? $mbPersonne->pays_insee : "";
            $personne['codePostal'] = $mbPersonne->cp;
            $personne['tel']        = $mbPersonne->tel;
            $personne['tel2']       = $mbPersonne->tel2;
            if (!$light) {
                $personne['email'] = $mbPersonne->email;
            }
        } else {
            if ($mbPersonne instanceof CMedecin) {
                $personne['nom']          = $mbPersonne->nom;
                $personne['nomNaissance'] = $mbPersonne->jeunefille;
                if (!$light) {
                    $personne['civilite'] = "";
                }
                $personne['prenoms'][]  = $mbPersonne->prenom;
                $personne['ligne']      = $mbPersonne->adresse;
                $personne['ville']      = $mbPersonne->ville;
                $personne['codePostal'] = $mbPersonne->cp;
                $personne['pays']       = "";
                $personne['tel']        = $mbPersonne->tel;
                $personne['tel2']       = $mbPersonne->portable;
                if (!$light) {
                    $personne['email'] = $mbPersonne->email;
                }
            } else {
                if ($mbPersonne instanceof CMediusers) {
                    $personne['nom']          = $mbPersonne->_user_last_name;
                    $personne['nomNaissance'] = "";
                    if (!$light) {
                        $personne['civilite'] = "";
                    }
                    $personne['prenoms'][]  = $mbPersonne->_user_first_name;
                    $personne['ligne']      = $mbPersonne->_user_adresse;
                    $personne['ville']      = $mbPersonne->_user_ville;
                    $personne['codePostal'] = $mbPersonne->_user_cp;
                    $personne['pays']       = "";
                    $personne['tel']        = $mbPersonne->_user_phone;
                    $personne['tel2']       = "";
                    if (!$light) {
                        $personne['email'] = $mbPersonne->_user_email;
                    }
                }
            }
        }

        if (isset($this->_ref_receiver->_id) && $this->_ref_receiver->_configs["uppercase_fields"]) {
            $personne['nom']          = CMbString::upper($personne['nom']);
            $personne['nomNaissance'] = CMbString::upper($personne['nomNaissance']);
            $personne['ligne']        = CMbString::upper($personne['ligne']);
            $personne['ville']        = CMbString::upper($personne['ville']);
        }

        $this->addTexte($elParent, "nomUsuel", $personne['nom']);
        $this->addTexte($elParent, "nomNaissance", $personne['nomNaissance']);
        $prenoms = $this->addElement($elParent, "prenoms");
        foreach ($personne['prenoms'] as $key => $prenom) {
            if ($key == 0) {
                if (isset($this->_ref_receiver->_id) && $this->_ref_receiver->_configs["uppercase_fields"]) {
                    $prenom = CMbString::upper($prenom ? $prenom : $personne['nom']);
                }
                $this->addTexte($prenoms, "prenom", $prenom ? $prenom : $personne['nom']);
            } else {
                if (isset($this->_ref_receiver->_id) && $this->_ref_receiver->_configs["uppercase_fields"]) {
                    $prenom = CMbString::upper($prenom);
                }
                $this->addTexte($prenoms, "prenom", $prenom);
            }
        }
        if (!$light) {
            if ($personne['civilite']) {
                $civiliteHprim = $this->addElement($elParent, "civiliteHprim");
                $this->addAttribute($civiliteHprim, "valeur", $civiliteHprimConversion[$personne['civilite']]);
            }
        }
        $adresses = $this->addElement($elParent, "adresses");
        $adresse  = $this->addElement($adresses, "adresse");
        $pattern  = "/[^0-9a-zàáâãäåòóôõöøèéêëçìíîïùúûüÿñ-]/i";
        if (CMbArray::get($personne, "ligne")) {
            foreach (explode("\n", $personne['ligne']) as $_adress) {
                $this->addTexte($adresse, "ligne", substr(preg_replace($pattern, " ", $_adress), 0, 35));
            }
        }

        $this->addTexte($adresse, "ville", $personne['ville']);
        if ($personne['pays']) {
            $this->addElement($adresse, "pays", str_pad($personne['pays'], 3, '0', STR_PAD_LEFT));
        }
        $this->addElement($adresse, "codePostal", $personne['codePostal']);

        $telephones = $this->addElement($elParent, "telephones");
        if (CMbArray::get($personne, 'tel')) {
            $telephone1 = $this->addElement($telephones, "telephone", $personne['tel']);
            $this->addAttribute($telephone1, "categorie", "domicile");
        }
        if (CMbArray::get($personne, 'tel2')) {
            $telephone2 = $this->addElement($telephones, "telephone", $personne['tel2']);
            $this->addAttribute($telephone2, "categorie", "mobile");
        }

        if (!$light) {
            $emails = $this->addElement($elParent, "emails");
            $this->addElement($emails, "email", $personne['email']);
        }
    }

    function addErreurAvertissement($elParent, $statut, $code, $libelle, $commentaires = null, $mbObject = null)
    {
        $erreurAvertissement = $this->addElement($elParent, "erreurAvertissement");
        $this->addAttribute($erreurAvertissement, "statut", $statut);

        $dateHeureEvenementConcerne = $this->addElement($erreurAvertissement, "dateHeureEvenementConcerne");
        $this->addElement($dateHeureEvenementConcerne, "date", CMbDT::date());
        $this->addElement($dateHeureEvenementConcerne, "heure", CMbDT::time());

        $evenementPatients = $this->addElement($erreurAvertissement, $this->_sous_type_evt);
        $this->addElement($evenementPatients, "identifiantPatient");

        if ($this->_sous_type_evt == "fusionPatient") {
            $this->addElement($evenementPatients, "identifiantPatientElimine");
        }

        if ($this->_sous_type_evt == "venuePatient") {
            $this->addElement($evenementPatients, "identifiantVenue");
        }

        if ($this->_sous_type_evt == "debiteursVenue") {
            $this->addElement($evenementPatients, "identifiantVenue");
            $debiteurs = $this->addElement($evenementPatients, "debiteurs");
            $debiteur  = $this->addElement($debiteurs, "debiteur");
            $this->addElement($debiteur, "identifiantParticulier");
        }

        if ($this->_sous_type_evt == "mouvementPatient") {
            $this->addElement($evenementPatients, "identifiantVenue");
            $this->addElement($evenementPatients, "identifiantMouvement");
        }

        if ($this->_sous_type_evt == "fusionVenue") {
            $this->addElement($evenementPatients, "identifiantVenue");
            $this->addElement($evenementPatients, "identifiantVenueEliminee");
        }

        $observations = $this->addElement($erreurAvertissement, "observations");
        $this->addObservation($observations, $code, $libelle, $commentaires);
    }

    function addObservation($elParent, $code, $libelle, $commentaires = null)
    {
        $observation  = $this->addElement($elParent, "observation");
        $commentaires = CMbString::removeAllHTMLEntities($commentaires);

        $this->addElement($observation, "code", substr($code, 0, 17));
        $this->addElement($observation, "libelle", substr($libelle, 0, 80));
        $this->addElement($observation, "commentaire", substr($commentaires, 0, 4000));
    }

    function addReponseCCAM($elParent, $statut, $codes, $acteCCAM, $mbObject = null, $commentaires = null)
    {
        $reponse = $this->addElement($elParent, "reponse");
        $this->addAttribute($reponse, "statut", $statut);

        $elActeCCAM = $this->addElement($reponse, "acteCCAM");
        $this->addActeCCAMAcquittement($elActeCCAM, $acteCCAM);

        $this->addReponse($reponse, $statut, $codes, $mbObject, $commentaires);
    }

    function addActeCCAMAcquittement(DOMNode $elParent, $acteCCAM)
    {
        $mbActeCCAM = $acteCCAM["acteCCAM"];

        $this->addAttributeYes($elParent, "valide");

        $intervention = $this->addElement($elParent, "intervention");
        $identifiant  = $this->addElement($intervention, "identifiant");
        $this->addElement($identifiant, "emetteur", $acteCCAM["idCibleIntervention"]);
        $this->addElement($identifiant, "recepteur", $acteCCAM["idSourceIntervention"]);

        $identifiant = $this->addElement($elParent, "identifiant");
        $this->addElement($identifiant, "emetteur", $acteCCAM["idSourceActeCCAM"]);
        $this->addElement($identifiant, "recepteur", $acteCCAM["idCibleActeCCAM"]);

        $this->addElement($elParent, "codeActe", $mbActeCCAM["code_acte"]);
        $this->addElement($elParent, "codeActivite", $mbActeCCAM["code_activite"]);
        $this->addElement($elParent, "codePhase", $mbActeCCAM["code_phase"]);

        $execute = $this->addElement($elParent, "execute");
        $this->addElement($execute, "date", CMbDT::date($mbActeCCAM["date"]));
        $this->addElement($execute, "heure", CMbDT::time($mbActeCCAM["heure"]));
    }

    function addReponse($elParent, $statut, $codes, $mbObject = null, $commentaires = null)
    {
        if ($statut == "ok") {
            return;
        }

        $erreur = $this->addElement($elParent, "erreur");

        $libelle = null;
        if (is_array($codes)) {
            $code = implode("", $codes);
            foreach ($codes as $_code) {
                $libelle .= CAppUI::tr("hprimxml-error-$_code");
            }
        } else {
            $code    = $codes;
            $libelle = CAppUI::tr("hprimxml-error-$code");
        }
        $this->addElement($erreur, "code", substr($code, 0, 17));
        $this->addElement($erreur, "libelle", substr($libelle, 0, 80));
        if ($commentaires) {
            $commentaires = CMbString::removeAllHTMLEntities($commentaires);
            $this->addElement($erreur, "commentaire", substr("$libelle : \"$commentaires\"", 0, 4000));
        }
    }

    function addReponseNGAP($elParent, $statut, $codes, $acteNGAP, $mbObject = null, $commentaires = null)
    {
        $reponse = $this->addElement($elParent, "reponse");
        $this->addAttribute($reponse, "statut", $statut);

        $elActeNGAP = $this->addElement($reponse, "acteNGAP");
        $this->addActeNGAPAcquittement($elActeNGAP, $acteNGAP);

        $this->addReponse($reponse, $statut, $codes, $mbObject, $commentaires);
    }

    function addActeNGAPAcquittement(DOMNode $elParent, $acteNGAP)
    {
        $mbActeNGAP = $acteNGAP["acteNGAP"];

        $this->addAttributeYes($elParent, "valide");

        $intervention = $this->addElement($elParent, "intervention");
        $identifiant  = $this->addElement($intervention, "identifiant");
        $this->addElement($identifiant, "emetteur", $acteNGAP["idCibleIntervention"]);
        $this->addElement($identifiant, "recepteur", $acteNGAP["idSourceIntervention"]);

        $identifiant = $this->addElement($elParent, "identifiant");
        $this->addElement($identifiant, "emetteur", $acteNGAP["idSourceActeNGAP"]);
        $this->addElement($identifiant, "recepteur", $acteNGAP["idCibleActeNGAP"]);

        $this->addElement($elParent, "lettreCle", $mbActeNGAP["code"]);
        $this->addElement($elParent, "coefficient", $mbActeNGAP["coefficient"]);

        $execute = $this->addElement($elParent, "execute");
        $this->addElement($execute, "date", CMbDT::date($mbActeNGAP["date"]));
        $this->addElement($execute, "heure", CMbDT::time($mbActeNGAP["heure"]));
    }

    function addReponseGeneral(
        $elParent,
        $statut,
        $codes,
        $codeErr = null,
        $mbObject = null,
        $commentaires = null,
        $data = null
    ) {
        $reponse = $this->addElement($elParent, "reponse");
        $this->addAttribute($reponse, "statut", $statut);
        if ($codeErr) {
            $this->addAttribute($reponse, "codeErreur", $codeErr);
        }
        $this->addInterventionError($reponse, $data);
        $this->addReponse($reponse, $statut, $codes, $mbObject, $commentaires);
    }

    function addInterventionError(DOMNode $elParent, $data)
    {
        if (!$data) {
            return;
        }

        if (!CMbArray::get($data, "idCibleIntervention") || !CMbArray::get($data, "idSourceIntervention")) {
            return;
        }

        $intervention = $this->addElement($elParent, "intervention");
        $identifiant  = $this->addElement($intervention, "identifiant");
        $this->addElement($identifiant, "emetteur", $data["idCibleIntervention"]);
        $this->addElement($identifiant, "recepteur", $data["idSourceIntervention"]);
    }

    function addReponseIntervention($elParent, $statut, $codes, $acteCCAM, $mbObject = null, $commentaires = null)
    {
        $reponse = $this->addElement($elParent, "reponse");
        $this->addAttribute($reponse, "statut", $statut);

        $intervention = $this->addElement($reponse, "intervention");
        $this->addInterventionAcquittement($intervention, $mbObject);

        $this->addReponse($reponse, $statut, $codes, $mbObject, $commentaires);
    }

    function addInterventionAcquittement($elParent, $operation = null)
    {
        $identifiant = $this->addElement($elParent, "identifiant");
        $this->addElement($identifiant, "emetteur", isset($operation->_id) ? $operation->_id : 0);
    }

    function addReponseFraisDivers($elParent, $ack_presta = [])
    {
        $reponse = $this->addElement($elParent, "reponse");
        $this->addAttribute($reponse, "statut", CMbArray::get($ack_presta, "statut"));
        $this->addAttribute($reponse, "codeErreur", CMbArray::get($ack_presta, "code"));

        $FraisDivers = $this->addElement($reponse, "FraisDivers");
        $identifiant = $this->addElement($FraisDivers, "identifiant");
        $id          = CMbArray::get($ack_presta, "identifiant");
        $this->addElement($identifiant, "emetteur", $id ? $id : 0);
        $this->addElement($FraisDivers, "lettreCle", CMbArray::get($ack_presta, "lettreCle"));

        $this->addCodeLibelleCommentaire(
            $FraisDivers,
            "erreur",
            CMbArray::get($ack_presta, "code"),
            CMbArray::get($ack_presta, "comment")
        );
    }

    function addCodeLibelleCommentaire(
        DOMNode $elParent,
        $nodeName,
        $code,
        $libelle,
        $dictionnaire = null,
        $commentaire = null
    ) {
        $codeLibelleCommentaire = $this->addElement($elParent, $nodeName);

        $this->addTexte($codeLibelleCommentaire, "code", str_replace(" ", "", $code), 10);

        if (strlen($libelle) > 35) {
            $commentaire = $libelle . $commentaire;
        }
        $this->addTexte($codeLibelleCommentaire, "libelle", $libelle, 35);
        $this->addTexte($codeLibelleCommentaire, "dictionnaire", $dictionnaire, 12);
        $this->addCommentaire($codeLibelleCommentaire, $commentaire);

        return $codeLibelleCommentaire;
    }

    function getTypeEvenementPatient()
    {
        $xpath = new CHPrimXPath($this);

        $evenementPatient = $xpath->query("/hprim:evenementsPatients/hprim:evenementPatient/*");
        $type             = null;
        $evenements       = CHPrimXMLEventPatient::$evenements;
        foreach ($evenementPatient as $_evenementPatient) {
            if (array_key_exists($_evenementPatient->tagName, $evenements)) {
                $type = $_evenementPatient->tagName;
            }
        }

        return $type;
    }

    function addVenue($elParent, CSejour $mbVenue, $referent = false, $light = false)
    {
        $receiver = $this->_ref_receiver;

        if (!$light) {
            // Ajout des attributs du séjour
            $this->addAttributeNo($elParent, "confidentiel");
            // Etat d'une venue : encours, clôturée ou préadmission
            $etatConversion = [
                "preadmission" => "préadmission",
                "encours"      => "encours",
                "cloture"      => "clôturée",
            ];
            $this->addAttribute($elParent, "etat", $etatConversion[$mbVenue->_etat]);
            $this->addAttribute($elParent, "facturable", self::convertToBool($mbVenue->facturable));
            $this->addAttribute(
                $elParent,
                "declarationMedecinTraitant",
                self::convertToBool($mbVenue->adresse_par_prat_id)
            );
        }

        $identifiant = $this->addElement($elParent, "identifiant");
        if (!$referent) {
            $idex = new CIdSante400();
            // On peut également passer l'identifiant externe du séjour
            if ($receiver && ($idex_tag = $receiver->_configs["build_id_sejour_tag"])) {
                $idex = CIdSante400::getMatch($mbVenue->_class, $idex_tag, null, $mbVenue->_id);
            }

            $id_emetteur = $idex->_id ? $idex->id400 : $mbVenue->_id;

            $this->addIdentifiantPart($identifiant, "emetteur", $id_emetteur, $referent);
            if ($mbVenue->_NDA) {
                $this->addIdentifiantPart($identifiant, "recepteur", $mbVenue->_NDA, $referent);
            }
        } else {
            $this->addIdentifiantPart($identifiant, "emetteur", $mbVenue->_NDA, $referent);

            if (isset($mbVenue->_id400)) {
                $this->addIdentifiantPart($identifiant, "recepteur", $mbVenue->_id400, $referent);
            }
        }

        $natureVenueHprim     = $this->addElement($elParent, "natureVenueHprim");
        $version              = CAppUI::conf("hprimxml $this->evenement version");
        $attrNatureVenueHprim = [
            "comp"    => "hsp",
            "ambu"    => in_array($version, ["1.053", "1.054", "1.07", "1.072"]) ? "ambu" : "hsp",
            "urg"     => in_array($version, ["1.054"]) ? "urg" : "hsp",
            "psy"     => "hsp",
            "ssr"     => "hsp",
            "exte"    => in_array($version, ["1.053", "1.054", "1.07", "1.072"]) ?
                in_array($version, ["1.053", "1.054"]) ?
                    "exte" : "ext"
                : "hsp",
            "consult" => "cslt",
            "seances" => "sc",
        ];

        $attrNatureVenueHprimValue = $attrNatureVenueHprim[$mbVenue->type];

        // Si c'est un séjour pour un bébé on va mettre :
        // 1 - si l'accouchement est normal et que le bébé est accueilli avec sa maman le champ natureVenueHprim doit avoir la valeur "BB"
        // 2 - si le bébé passe en service de néonatalogie le champ natureVenueHprim doit avoir "HSP"
        if (isset($receiver->_configs) && $receiver->_configs["send_child_admit"] && $mbVenue->_id) {
            $naissance                   = new CNaissance();
            $naissance->sejour_enfant_id = $mbVenue->_id;
            $naissance->loadMatchingObject();
            if ($naissance->_id) {
                $attrNatureVenueHprimValue = "nss";
                if ($version == "1.053" || $version == "1.054") {
                    $curr_affectation = $mbVenue->getCurrAffectation();
                    // Recherche si le bb est rattaché à la maman
                    if ($curr_affectation->parent_affectation_id) {
                        $attrNatureVenueHprimValue = "bebe";
                    }
                }
            }
        }

        $this->addAttribute($natureVenueHprim, "valeur", $attrNatureVenueHprimValue);

        $entree = $this->addElement($elParent, "entree");

        $dateHeureOptionnelle = $this->addElement($entree, "dateHeureOptionnelle");
        $this->addElement($dateHeureOptionnelle, "date", CMbDT::date($mbVenue->entree));
        $this->addElement($dateHeureOptionnelle, "heure", CMbDT::time($mbVenue->entree));

        $modeEntree = $this->addElement($entree, "modeEntree");
        // mode d'entrée inconnu
        $mode = "09";
        // admission après consultation d'un médecin de l'établissement
        if ($mbVenue->_ref_consult_anesth && $mbVenue->_ref_consult_anesth->_id) {
            $mode = "01";
        }
        // malade envoyé par un médecin extérieur
        if ($mbVenue->loadRefAdresseParPraticien()->_id) {
            $mode = "02";
        }
        $this->addAttribute($modeEntree, "valeur", $mode);

        $ufs            = $mbVenue->getUFs();
        $uf_hebergement = CMbArray::get($ufs, "hebergement");
        if (isset($uf_hebergement->_id)) {
            $this->addCodeLibelle(
                $entree,
                "uniteFonctionnelleResponsable",
                $uf_hebergement->code,
                $uf_hebergement->libelle
            );
        }

        if (!$light) {
            $medecins = $this->addElement($elParent, "medecins");

            // Traitement du medecin traitant du patient
            $_ref_medecin_traitant = $mbVenue->_ref_patient->_ref_medecin_traitant;
            if ($_ref_medecin_traitant && $_ref_medecin_traitant->_id) {
                if ($_ref_medecin_traitant->adeli) {
                    $this->addMedecin($medecins, $_ref_medecin_traitant, "trt");
                }
            }

            // Traitement du medecin adressant
            $_ref_adresse_par_prat = $mbVenue->loadRefAdresseParPraticien();
            if ($_ref_adresse_par_prat && $_ref_adresse_par_prat->adeli) {
                $this->addMedecin($medecins, $_ref_adresse_par_prat, "adrs");
            }

            // Traitement du responsable du séjour
            $this->addMedecin($medecins, $mbVenue->_ref_praticien, "rsp");

            // Traitement des prescripteurs
            $_ref_prescripteurs = $mbVenue->_ref_prescripteurs;
            if (is_array($_ref_prescripteurs)) {
                foreach ($_ref_prescripteurs as $prescripteur) {
                    $this->addMedecin($medecins, $prescripteur, "prsc");
                }
            }

            // Traitement des intervenant (ayant effectués des actes)
            $_ref_actes_ccam = $mbVenue->_ref_actes_ccam;
            if (is_array($_ref_actes_ccam)) {
                foreach ($_ref_actes_ccam as $acte_ccam) {
                    $intervenant = $acte_ccam->loadRefPraticien();
                    $this->addMedecin($medecins, $intervenant, "intv");
                }
            }
        }

        // Cas dans lequel on transmet pas de sortie tant que l'on a pas la sortie réelle
        if (!$mbVenue->sortie_reelle && (isset($receiver->_id) && $receiver->_configs["send_sortie_prevue"] == 0)) {
            return;
        }

        $sortie               = $this->addElement($elParent, "sortie");
        $dateHeureOptionnelle = $this->addElement($sortie, "dateHeureOptionnelle");
        $this->addElement($dateHeureOptionnelle, "date", CMbDT::date($mbVenue->sortie));
        $this->addElement($dateHeureOptionnelle, "heure", CMbDT::time($mbVenue->sortie));

        if ($mbVenue->mode_sortie) {
            $modeSortieHprim = $this->addElement($sortie, "modeSortieHprim");
            //retour au domicile
            if ($mbVenue->mode_sortie == "normal") {
                $modeSortieEtablissementHprim = "04";
            } // décès
            else {
                if ($mbVenue->mode_sortie == "deces") {
                    $modeSortieEtablissementHprim = "05";
                } // mutation
                else {
                    if ($mbVenue->mode_sortie == "mutation") {
                        $modeSortieEtablissementHprim = "08";
                    } // autre transfert dans un autre CH
                    else {
                        if ($mbVenue->mode_sortie == "transfert") {
                            $modeSortieEtablissementHprim = "02";
                        }
                    }
                }
            }
            $this->addElement($modeSortieHprim, "code", $modeSortieEtablissementHprim);
            $this->addElement($modeSortieHprim, "libelle", $mbVenue->mode_sortie);

            if ($mbVenue->etablissement_sortie_id) {
                $destination = $this->addElement($modeSortieHprim, "destination");
                $this->addElement($destination, "libelle", $mbVenue->etablissement_sortie_id);
            }

            $this->addAttribute($modeSortieHprim, "valeur", $modeSortieEtablissementHprim);
        }

        // @todo Voir comment intégrer le placement pour la v. 1.01 et v. 1.05
        /*
        if (!$light) {
          $placement = $this->addElement($elParent, "Placement");
          $modePlacement = $this->addElement($placement, "modePlacement");
          $this->addAttribute($modePlacement, "modaliteHospitalisation", $mbVenue->modalite);
          $this->addElement($modePlacement, "libelle", substr($mbVenue->_view, 0, 80));

          $datePlacement = $this->addElement($placement, "datePlacement");
          $this->addElement($datePlacement, "date", CMbDT::date($mbVenue->entree));
        }*/
    }

    function addMedecin($elParent, $praticien, $lien)
    {
        $medecin = $this->addElement($elParent, "medecin");
        $this->addAttribute($medecin, "lien", $lien);
        $this->addElement($medecin, "numeroAdeli", $praticien->adeli);
        $identification = $this->addElement($medecin, "identification");

        $idex = CIdSante400::getMatchFor($praticien, $this->getTagMediuser());

        $this->addElement($identification, "code", $idex->_id ? $idex->id400 : $praticien->_id);
        $this->addElement($identification, "libelle", $praticien->_view);
        $personne = $this->addElement($medecin, "personne");
        $this->addPersonne($personne, $praticien);
    }

    function addMaman($elParent, CSejour $sejour_maman, $referent = false)
    {
        $venue = $this->addElement($elParent, "venue");

        $identifiant = $this->addElement($venue, "identifiant");

        if (!$referent) {
            $this->addIdentifiantPart($identifiant, "emetteur", $sejour_maman->_id, $referent);
            if ($sejour_maman->_NDA) {
                $this->addIdentifiantPart($identifiant, "recepteur", $sejour_maman->_NDA, $referent);
            }
        } else {
            $this->addIdentifiantPart($identifiant, "emetteur", $sejour_maman->_NDA, $referent);

            if (isset($sejour_maman->_id400)) {
                $this->addIdentifiantPart($identifiant, "recepteur", $sejour_maman->_id400, $referent);
            }
        }
    }

    /**
     * Add volet médical
     *
     * @param DOMNode                    $elParent Parent node
     * @param CMbObject|CSejour|CPatient $object   Object, patient or admit
     *
     * @return void
     */
    function addVoletMedical(DOMNode $elParent, CMbObject $object)
    {
        $object->loadRefDossierMedical();

        // constantes
        $this->addConstantes($elParent, $object);

        // antecedents
        $this->addAntecedents($elParent, $object);

        // allergies
        $this->addAllergies($elParent, $object);

        // traitements
        $this->addTraitements($elParent, $object);

        // antecedentsFamiliaux
        $this->addAntecedentsFamiliaux($elParent, $object);
    }

    /**
     * Add constantes
     *
     * @param DOMNode                    $elParent Parent node
     * @param CMbObject|CSejour|CPatient $object   Object, patient or admit
     *
     * @return void
     */
    function addConstantes(DOMNode $elParent, CMbObject $object)
    {
        $constantes_medicales = [];
        if ($object instanceof CSejour) {
            $constantes_medicales = $object->loadListConstantesMedicales();
        }
        if ($object instanceof CPatient) {
            $constantes_medicales = $object->loadRefsConstantesMedicales();
        }

        $constantes = $this->addElement($elParent, "constantes");
        foreach ($constantes_medicales as $_constante) {
            $this->addListConstante($constantes, $_constante);
        }
    }

    function addListConstante(DOMNode $elParent, CConstantesMedicales $constante_medicale)
    {
        $list_constantes = CConstantesMedicales::$list_constantes;

        foreach ($list_constantes as $type => $params) {
            if ($constante_medicale->$type == "") {
                continue;
            }

            if (!array_key_exists($type, self::$list_constantes)) {
                continue;
            }

            $this->addConstante($elParent, $constante_medicale, $type);
        }
    }

    function addConstante(DOMNode $elParent, CConstantesMedicales $constante_medicale, $type)
    {
        $list_constantes = CConstantesMedicales::$list_constantes;

        $constante = $this->addElement($elParent, "constante");
        $this->addAttribute($constante, "nature", CMbArray::get(CHPrimXMLDocument::$list_constantes, $type));

        $valeur = $this->addElement($constante, "valeur", $constante_medicale->$type);
        $this->addAttribute($valeur, "unite", CMbArray::get($list_constantes[$type], "unit"));

        $dateObservation = $this->addElement($constante, "dateObservation");
        $this->addDateHeure($dateObservation, $constante_medicale->datetime);
    }

    /**
     * Ajout de l'élément Date et heure
     *
     * @param DOMNode $elParent Noeud parent
     * @param string  $dateTime Date et heure
     *
     * @return void
     */
    function addDateHeure(DOMNode $elParent, $dateTime = null)
    {
        $this->addDate($elParent, $dateTime);
        $this->addHeure($elParent, $dateTime);
    }

    /**
     * Ajout de l'élément date
     *
     * @param DOMNode $elParent Noeud parent
     * @param string  $dateTime Date et heure
     *
     * @return void
     */
    function addDate(DOMNode $elParent, $dateTime = null)
    {
        $this->addElement($elParent, "date", CMbDT::date(null, $dateTime));
    }

    /**
     * Ajout de l'élément heure
     *
     * @param DOMNode $elParent Noeud parent
     * @param string  $dateTime Date et heure
     *
     * @return void
     */
    function addHeure(DOMNode $elParent, $dateTime = null)
    {
        $this->addElement($elParent, "heure", CMbDT::time(null, $dateTime));
    }

    /**
     * Add antécédents
     *
     * @param DOMNode                    $elParent Parent node
     * @param CMbObject|CSejour|CPatient $object   Object, patient or admit
     *
     * @return void
     */
    function addAntecedents(DOMNode $elParent, CMbObject $object)
    {
        $antecedents = $this->addElement($elParent, "antecedents");

        /** @var CAntecedent[] $all_antecedents */
        $all_antecedents = $object->_ref_dossier_medical->loadRefsAntecedents();
        foreach ($all_antecedents as $_antecedent) {
            // On exclut les antécédents familliaux et les allergies
            if ($_antecedent->type == "fam" || $_antecedent->type == "alle") {
                continue;
            }

            $this->addAntecedent($antecedents, $_antecedent);
        }
    }

    function addAntecedent(DOMNode $elParent, CAntecedent $antecedent)
    {
        $elAntecedent = $this->addElement($elParent, "antecedent");

        $rques = CMbString::htmlSpecialChars($antecedent->rques);
        $rques = CMbString::convertHTMLToXMLEntities($rques);

        if (preg_match_all("/[A-Z]\d{2}\.?\d{0,2}/i", $rques, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $_matches) {
                foreach ($_matches as $_match) {
                    $this->addCodeLibelleCommentaire($elAntecedent, "identification", $_match, $rques, "CIM10");
                }
            }
        } else {
            $this->addCodeLibelle($elAntecedent, "identification", $antecedent->_id, $rques);
        }

        if (!$antecedent->date) {
            return;
        }

        $date_explode = explode("-", $antecedent->date);
        if ($date_explode[1] == "00") {
            $date_explode[1] = "01";
        }
        if ($date_explode[2] == "00") {
            $date_explode[2] = "01";
        }

        $date = $date_explode[0] . "-" . $date_explode[1] . "-" . $date_explode[2];

        $this->addElement($elAntecedent, "dateDebutEstimee", $date);
    }

    /**
     * Add allergies
     *
     * @param DOMNode                    $elParent Parent node
     * @param CMbObject|CSejour|CPatient $object   Object, patient or admit
     *
     * @return void
     */
    function addAllergies(DOMNode $elParent, CMbObject $object)
    {
        $allergies = $this->addElement($elParent, "allergies");

        $all_antecedents = $object->_ref_dossier_medical->_all_antecedents;

        foreach ($all_antecedents as $_antecedent) {
            if ($_antecedent->type != "alle") {
                continue;
            }

            $rques = CMbString::htmlSpecialChars($_antecedent->rques);
            $rques = CMbString::convertHTMLToXMLEntities($rques);

            $allergie = $this->addElement($allergies, "allergie");
            if (preg_match_all("/[A-Z]\d{2}\.?\d{0,2}/i", $rques, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $_matches) {
                    foreach ($_matches as $_match) {
                        $this->addCodeLibelleCommentaire($allergie, "allergene", $_match, $rques, "CIM10");
                    }
                }
            } else {
                $this->addCodeLibelle($allergie, "allergene", $_antecedent->_id, $rques);
            }

            if (!$_antecedent->date) {
                continue;
            }

            $date_explode = explode("-", $_antecedent->date);
            if ($date_explode[1] == "00") {
                $date_explode[1] = "01";
            }
            if ($date_explode[2] == "00") {
                $date_explode[2] = "01";
            }

            $date = $date_explode[0] . "-" . $date_explode[1] . "-" . $date_explode[2];

            $this->addElement($allergie, "dateDebutEstimee", $date);
        }
    }

    /**
     * Add traitements
     *
     * @param DOMNode                    $elParent Parent node
     * @param CMbObject|CSejour|CPatient $object   Object, patient or admit
     *
     * @return void
     */
    function addTraitements(DOMNode $elParent, CMbObject $object)
    {
        $elTraitements = $this->addElement($elParent, "traitements");

        $traitements = [];
        if ($object instanceof CSejour) {
            // Traitements du patient
            $patient     = $object->_ref_patient;
            $traitements = $patient->loadRefDossierMedical()->loadRefsTraitements();
        } else {
            $patient     = $object;
            $traitements = $object->_ref_dossier_medical->loadRefsTraitements();
        }

        foreach ($traitements as $_traitement) {
            $this->addTraitement($elTraitements, $_traitement);
        }

        $prescription = $patient->_ref_dossier_medical->loadRefPrescription();
        if ($prescription && is_array($prescription->_ref_prescription_lines)) {
            foreach ($prescription->_ref_prescription_lines as $_line) {
                $_line->loadRefsPrises();

                $elTraitement = $this->addCodeLibelleCommentaire(
                    $elTraitements,
                    "traitement",
                    $_line->code_cip,
                    $_line->_ucd_view,
                    "CIP",
                    $_line->commentaire
                );

                $this->addElement($elTraitement, "dateDebutEstimee", $_line->debut);
                $this->addElement($elTraitement, "dateFinEstimee", $_line->fin);
            }
        }
    }

    function addTraitement($elParent, CTraitement $traitement)
    {
        $rques = CMbString::htmlSpecialChars($traitement->traitement);
        $rques = CMbString::convertHTMLToXMLEntities($rques);

        $elTraitement = $this->addCodeLibelle($elParent, "traitement", $traitement->_id, $rques);

        $this->addElement($elTraitement, "dateDebutEstimee", $traitement->debut);
        $this->addElement($elTraitement, "dateFinEstimee", $traitement->fin);
    }

    /**
     * Add antécédents familiaux
     *
     * @param DOMNode                    $elParent Parent node
     * @param CMbObject|CSejour|CPatient $object   Object, patient or admit
     *
     * @return void
     */
    function addAntecedentsFamiliaux(DOMNode $elParent, CMbObject $object)
    {
        $antecedentsFamiliaux = $this->addElement($elParent, "antecedentsFamiliaux");

        $all_antecedents = $object->_ref_dossier_medical->_all_antecedents;

        foreach ($all_antecedents as $_antecedent) {
            if ($_antecedent->type != "fam") {
                continue;
            }

            $antecedentFamilial = $this->addElement($antecedentsFamiliaux, "antecedentFamilial");
            $this->addElement($antecedentFamilial, "parent", "pr");
            $this->addAntecedent($antecedentFamilial, $_antecedent);
        }
    }

    function addIntervention($elParent, COperation $operation, $referent = null, $light = false)
    {
        $receiver = $this->_ref_receiver;

        $identifiant = $this->addElement($elParent, "identifiant");
        $this->addElement($identifiant, "emetteur", substr($operation->_class, 1, 1) . "-$operation->_id");
        $last_idex = $operation->_ref_last_id400;
        if (isset($last_idex->_id)) {
            $this->addElement($identifiant, "recepteur", $last_idex->id400);
        }

        $sejour = $operation->loadRefSejour();

        if (!$operation->plageop_id) {
            $operation->completeField("date");
        }

        // Calcul du début de l'intervention
        $time_operation    = ($operation->time_operation == "00:00:00") ? null : "$operation->date $operation->time_operation";
        $debut_plageOp = $operation->loadRefPlageOp()->debut;
        $mbOpDateTimeDebut = CValue::first(
            $operation->debut_op,
            $operation->entree_salle,
            $time_operation,
            $operation->horaire_voulu ? "$operation->date $operation->horaire_voulu" : null,
            $debut_plageOp ? "$operation->date $debut_plageOp" : null,
        );
        $mbOpDebut         = CMbRange::forceInside($sejour->entree, $sejour->sortie, $mbOpDateTimeDebut);

        // Calcul de la fin de l'intervention
        $mbOpDateTimeFin = CValue::first(
            $operation->fin_op,
            $operation->sortie_salle,
            CMbDT::addDateTime($operation->temp_operation, $mbOpDebut)
        );
        $mbOpFin         = CMbRange::forceInside($sejour->entree, $sejour->sortie, $mbOpDateTimeFin);

        $debut = $this->addElement($elParent, "debut");
        $this->addElement($debut, "date", CMbDT::date($mbOpDebut));
        $this->addElement($debut, "heure", CMbDT::time($mbOpDebut));

        $fin = $this->addElement($elParent, "fin");
        $this->addElement($fin, "date", CMbDT::date($mbOpFin));
        $this->addElement($fin, "heure", CMbDT::time($mbOpFin));

        if ($light) {
            // Ajout des participants
            $mbParticipants = [];
            foreach ($operation->_ref_actes_ccam as $acte_ccam) {
                $acte_ccam->loadRefExecutant();
                $mbParticipant                           = $acte_ccam->_ref_executant;
                $mbParticipants[$mbParticipant->user_id] = $mbParticipant;
            }

            $participants = $this->addElement($elParent, "participants");
            foreach ($mbParticipants as $mbParticipant) {
                $participant = $this->addElement($participants, "participant");
                $medecin     = $this->addElement($participant, "medecin");
                $this->addProfessionnelSante($medecin, $mbParticipant);
            }

            // Libellé de l'opération
            $this->addTexte($elParent, "libelle", $operation->libelle, 80);
        } else {
            $salle = $operation->updateSalle();
            $this->addUniteFonctionnelle($elParent, $salle->code ?: $salle->nom, $salle->nom);

            // Uniquement le responsable de l’'intervention
            $participants = $this->addElement($elParent, "participants");
            $participant  = $this->addElement($participants, "participant");
            $medecin      = $this->addElement($participant, "medecin");
            $this->addProfessionnelSante($medecin, $operation->loadRefChir());

            // Libellé de l'opération
            $this->addTexte($elParent, "libelle", $operation->libelle, 4000);

            // Remarques sur l'opération
            $this->addTexte(
                $elParent,
                "commentaire",
                CMbString::convertHTMLToXMLEntities("$operation->materiel - $operation->rques"),
                4000
            );

            if (CAppUI::conf("hprimxml $this->evenement version") == "1.072") {
                // Conventionnée ?
                $this->addElement($elParent, "convention", $operation->conventionne ? 1 : 0);

                // TypeAnesthésie : nomemclature externe (idex)
                if ($operation->type_anesth) {
                    $tag_hprimxml   = $this->_ref_receiver->_tag_hprimxml;
                    $idexTypeAnesth = CIdSante400::getMatch(
                        "CTypeAnesth",
                        $tag_hprimxml,
                        null,
                        $operation->type_anesth
                    );
                    $this->addElement($elParent, "typeAnesthesie", $idexTypeAnesth->id400);
                }

                // Indicateurs
                $indicateurs                   = $this->addElement($elParent, "indicateurs");
                $dossier_medical               = new CDossierMedical();
                $dossier_medical->object_class = "CPatient";
                $dossier_medical->object_id    = $operation->loadRefPatient()->_id;
                $dossier_medical->loadMatchingObject();

                $antecedents = $dossier_medical->loadRefsAntecedents();
                foreach ($antecedents as $_antecedent) {
                    $rques = CMbString::htmlSpecialChars($_antecedent->rques);
                    $rques = CMbString::convertHTMLToXMLEntities($rques);
                    $this->addCodeLibelle($indicateurs, "indicateur", $_antecedent->_id, $rques);
                }
                // Extemporané
                if ($operation->exam_extempo) {
                    $this->addCodeLibelle($indicateurs, "indicateur", "EXT", "Extemporané");
                }

                // Recours / Durée USCPO
                $this->addElement($elParent, "recoursUscpo", $operation->duree_uscpo ? 1 : 0);
                $this->addElement($elParent, "dureeUscpo", $operation->duree_uscpo ? $operation->duree_uscpo : null);

                // Côté (droit|gauche|bilatéral|total|inconnu)
                // D - Droit
                // G - Gauche
                // B - Bilatéral
                // T - Total
                // I - Inconnu
                $cote = [
                    "droit"     => "D",
                    "gauche"    => "G",
                    "bilatéral" => "B",
                    "total"     => "T",
                    "inconnu"   => "I",
                    "haut"      => "HT",
                    "bas"       => "BS",
                ];

                if ($operation->cote) {
                    $this->addCodeLibelle(
                        $elParent,
                        "cote",
                        $cote[$operation->cote],
                        CMbString::capitalize($operation->cote)
                    );
                }
            }

            if ($receiver->_configs["send_timing_bloc"]) {
                $this->addTimingOp($elParent, $operation);
            }
        }
    }

    /**
     * Ajout des timing de bloc
     *
     * @param DOMNode    $elParent  Node
     * @param COperation $operation Opération
     *
     * @return void
     */
    function addTimingOp(DOMNode $elParent, COperation $operation)
    {
        $this->addElement($elParent, "entreeBloc", $operation->entree_bloc);
        $this->addElement($elParent, "entreeSalle", $operation->entree_salle);
        $this->addElement($elParent, "debutIntervention", $operation->debut_prepa_preop);
        $this->addElement($elParent, "finIntervention", $operation->fin_op);
        $this->addElement($elParent, "sortieSalle", $operation->sortie_salle);
        $this->addElement($elParent, "entreeSSPI", $operation->entree_reveil);
        $this->addElement($elParent, "sortieSSPI", $operation->sortie_reveil_reel);
    }

    /**
     * Ajout des débiteurs
     *
     * @param DOMNode  $elParent  Node
     * @param CPatient $mbPatient Patient
     *
     * @return void
     */
    function addDebiteurs(DOMNode $elParent, CPatient $mbPatient)
    {
        $debiteur = $this->addElement($elParent, "debiteur");

        $assurance = $this->addElement($debiteur, "assurance");
        $this->addAssurance($assurance, $mbPatient);
    }

    /**
     * Ajout de l'assurance
     *
     * @param DOMNode  $elParent  Node
     * @param CPatient $mbPatient Patient
     *
     * @return void
     */
    function addAssurance(DOMNode $elParent, CPatient $mbPatient)
    {
        $this->addElement($elParent, "nom", $mbPatient->regime_sante);

        $assure = $this->addElement($elParent, "assure");
        $this->addAssure($assure, $mbPatient);

        if ($mbPatient->deb_amo && $mbPatient->fin_amo) {
            $dates = $this->addElement($elParent, "dates");
            $this->addElement($dates, "dateDebutDroit", CMbDT::date($mbPatient->deb_amo));
            $this->addElement($dates, "dateFinDroit", CMbDT::date($mbPatient->fin_amo));
        }

        $obligatoire = $this->addElement($elParent, "obligatoire");
        $this->addElement($obligatoire, "grandRegime", $mbPatient->code_regime);
        $this->addElement($obligatoire, "caisseAffiliation", $mbPatient->caisse_gest);
        $this->addElement($obligatoire, "centrePaiement", $mbPatient->centre_gest);

        // Ajout des exonérations
        $mbPatient->guessExoneration();
        if ($mbPatient->_type_exoneration) {
            $exonerationsTM = $this->addElement($obligatoire, "exonerationsTM");
            $exonerationTM  = $this->addElement($exonerationsTM, "exonerationTM");
            $this->addAttribute($exonerationTM, "typeExoneration", $mbPatient->_type_exoneration);
        }
    }

    /**
     * Ajout de l'assuré
     *
     * @param DOMNode  $elParent  Node
     * @param CPatient $mbPatient Patient
     *
     * @return void
     */
    function addAssure(DOMNode $elParent, CPatient $mbPatient)
    {
        $this->addElement($elParent, "immatriculation", $mbPatient->matricule);

        $personne       = $this->addElement($elParent, "personne");
        $sexeConversion = [
            "m" => "M",
            "f" => "F",
        ];

        $sexe = $mbPatient->assure_sexe ? $sexeConversion[$mbPatient->assure_sexe] : "I";
        $this->addAttribute($personne, "sexe", $sexe);

        $assure_nom             = $mbPatient->assure_nom;
        $assure_nom_jeune_fille = $mbPatient->assure_nom_jeune_fille;
        $assure_prenom          = $mbPatient->assure_prenom;
        $assure_prenom_2        = $mbPatient->_assure_prenom_2;
        $assure_prenom_3        = $mbPatient->_assure_prenom_3;
        $assure_prenom_4        = $mbPatient->_assure_prenom_4;
        $assure_adresse         = $mbPatient->assure_adresse;
        $assure_ville           = $mbPatient->assure_ville;

        if (isset($this->_ref_receiver->_id) && $this->_ref_receiver->_configs["uppercase_fields"]) {
            $assure_nom             = CMbString::upper($assure_nom);
            $assure_nom_jeune_fille = CMbString::upper($assure_nom_jeune_fille);
            $assure_prenom          = CMbString::upper($assure_prenom);
            $assure_prenom_2        = CMbString::upper($assure_prenom_2);
            $assure_prenom_3        = CMbString::upper($assure_prenom_3);
            $assure_prenom_4        = CMbString::upper($assure_prenom_4);
            $assure_adresse         = CMbString::upper($assure_adresse);
            $assure_ville           = CMbString::upper($assure_ville);
        }

        $this->addTexte($personne, "nomUsuel", $assure_nom);
        $this->addTexte($personne, "nomNaissance", $assure_nom_jeune_fille);

        $prenoms = $this->addElement($personne, "prenoms");
        $this->addTexte($prenoms, "prenom", $assure_prenom);
        $this->addTexte($prenoms, "prenom", $assure_prenom_2);
        $this->addTexte($prenoms, "prenom", $assure_prenom_3);
        $this->addTexte($prenoms, "prenom", $assure_prenom_4);

        $adresses = $this->addElement($personne, "adresses");
        $adresse  = $this->addElement($adresses, "adresse");
        $this->addTexte($adresse, "ligne", substr($assure_adresse, 0, 35));
        $this->addTexte($adresse, "ville", $assure_ville);

        if ($mbPatient->assure_pays_insee) {
            $this->addElement($adresse, "pays", str_pad($mbPatient->assure_pays_insee, 3, '0', STR_PAD_LEFT));
        }

        $this->addElement($adresse, "codePostal", $mbPatient->assure_cp);
        $dateNaissance   = $this->addElement($personne, "dateNaissance");
        $assureNaissance = $mbPatient->assure_naissance ? $mbPatient->assure_naissance : $mbPatient->naissance;
        $this->addElement(
            $dateNaissance,
            CMbDT::isLunarDate($assureNaissance) ? "dateLunaire" : "date",
            $assureNaissance
        );

        $this->addElement($elParent, "lienAssure", $mbPatient->rang_beneficiaire);
    }

    /**
     * Ajout de la saisie délocalisée
     *
     * @param DOMNode $elParent Node
     * @param CSejour $mbSejour Séjour
     *
     * @return void
     */
    function addSaisieDelocalisee(DOMNode $elParent, CSejour $mbSejour)
    {
        $action = CAppUI::conf("hprimxml $this->evenement version") == "2.00" ? "creation" : "création";
        $this->addAttribute($elParent, "action", $action);
        $this->addDateTimeElement($elParent, "dateAction");
        $dateHeureOptionnelle = $this->addElement($elParent, "dateHeureReference");
        $this->addDateHeure($dateHeureOptionnelle);

        $operations = $mbSejour->loadRefsOperations();
        $mbOp       = reset($operations);

        // Identifiant de l'intervention
        $identifiant = $this->addElement($elParent, "identifiant");
        $this->addElement($identifiant, "emetteur", $mbOp->_id);

        $this->addUniteFonctionnelleResponsable($elParent, $mbOp);

        // Traitement du médecin responsable du séjour
        $this->addMedecinResponsable($elParent, $mbSejour->_ref_praticien);

        // Diagnostics RUM
        $diagnosticsRum = $this->addElement($elParent, "diagnosticsRum");
        if (!CAppUI::conf("hprimxml send_only_das_diags")) {
            $diagnosticPrincipal = $this->addElement($diagnosticsRum, "diagnosticPrincipal");
            $this->addElement($diagnosticPrincipal, "codeCim10", strtoupper($mbSejour->DP));
            if ($mbSejour->DR) {
                $diagnosticRelie = $this->addElement($diagnosticsRum, "diagnosticRelie");
                $this->addElement($diagnosticRelie, "codeCim10", strtoupper($mbSejour->DR));
            }
        }

        if (count($mbSejour->loadRefDossierMedical()->_codes_cim)) {
            $diagnosticsSignificatifs = $this->addElement($diagnosticsRum, "diagnosticsSignificatifs");
            // Dans le cas où l'on envoie tous les diagnostics en DAS
            if (CAppUI::conf("hprimxml send_only_das_diags")) {
                if ($mbSejour->DP) {
                    $diagnosticSignificatif = $this->addElement($diagnosticsSignificatifs, "diagnosticSignificatif");
                    $this->addElement($diagnosticSignificatif, "codeCim10", strtoupper($mbSejour->DP));
                }
                if ($mbSejour->DR) {
                    $diagnosticSignificatif = $this->addElement($diagnosticsSignificatifs, "diagnosticSignificatif");
                    $this->addElement($diagnosticSignificatif, "codeCim10", strtoupper($mbSejour->DR));
                }
            }
            foreach ($mbSejour->_ref_dossier_medical->_codes_cim as $curr_code) {
                $diagnosticSignificatif = $this->addElement($diagnosticsSignificatifs, "diagnosticSignificatif");
                $this->addElement($diagnosticSignificatif, "codeCim10", strtoupper($curr_code));
            }
        }
    }

    function addUniteFonctionnelleResponsable(DOMNode $elParent, $mbOp)
    {
        $this->addCodeLibelle($elParent, "uniteFonctionnelleResponsable", $mbOp->code_uf, $mbOp->libelle_uf);
    }

    function addMedecinResponsable($elParent, $praticien)
    {
        $medecinResponsable = $this->addElement($elParent, "medecinResponsable");

        $this->addElement($medecinResponsable, "numeroAdeli", $praticien->adeli);

        $identification = $this->addElement($medecinResponsable, "identification");

        $idex = CIdSante400::getMatchFor($praticien, $this->getTagMediuser());

        $this->addElement($identification, "code", $idex->_id ? $idex->id400 : $praticien->_id);
        $this->addElement($identification, "libelle", $praticien->_view);
        $personne = $this->addElement($medecinResponsable, "personne");
        $this->addPersonne($personne, $praticien);
    }

    /**
     * Ajout du SSR
     *
     * @param DOMNode $elParent Node
     * @param CSejour $mbSejour Séjour
     *
     * @return void
     */
    function addSsr($elParent, CSejour $mbSejour)
    {
        // Identifiant du séjour
        $identifiant = $this->addElement($elParent, "identifiantSSR");
        $this->addElement($identifiant, "emetteur", $mbSejour->_id);

        $mbRhss = CRHS::getAllRHSsFor($mbSejour);
        foreach ($mbRhss as $_mbRhs) {
            $_mbRhs->loadRefSejour();
            $rhs = $this->addElement($elParent, "rhs");
            $this->addRhs($rhs, $mbSejour, $_mbRhs);
        }
    }

    /**
     * Ajout du RHS
     *
     * @param DOMNode $elParent Node
     * @param CSejour $mbSejour Séjour
     * @param CRHS    $mbRhs    RHS
     *
     * @return void
     */
    function addRhs(DOMNode $elParent, CSejour $mbSejour, CRHS $mbRhs)
    {
        $action = CAppUI::conf("hprimxml $this->evenement version") == "2.00" ? "creation" : "création";
        $this->addAttribute($elParent, "action", $action);
        $this->addAttribute($elParent, "version", "M01");

        $this->addElement($elParent, "dateAction", CMbDT::dateTimeXML());

        // Identifiant du séjour
        $identifiant = $this->addElement($elParent, "identifiant");
        $this->addElement($identifiant, "emetteur", $mbRhs->_id);

        $dateHeureOptionnelleLundi = $this->addElement($elParent, "dateHeureOptionnelleLundi");
        $this->addElement($dateHeureOptionnelleLundi, "date", $mbRhs->date_monday);

        // @todo Voir pour mettre sur un plateau
        $this->addCodeLibelle($elParent, "uniteMedicale", CGroups::loadCurrent()->_id, CGroups::loadCurrent()->_view);

        $joursPresence = $this->addElement($elParent, "joursPresence");
        if ($mbRhs->_in_bounds) {
            $this->addJoursPresence($joursPresence, $mbRhs);
        }

        $this->addElement($elParent, "diagnostics");

        $actesReeducation = $this->addElement($elParent, "actesReeducation");
        $this->addActesReeducation($actesReeducation, $mbRhs);

        $dependances = $this->addElement($elParent, "dependances");
        $this->addDependances($dependances, $mbRhs);
    }

    /**
     * Ajout des jours de présence
     *
     * @param DOMNode $elParent Node
     * @param CRHS    $mbRhs    RHS
     *
     * @return void
     */
    function addJoursPresence(DOMNode $elParent, CRHS $mbRhs)
    {
        if ($mbRhs->_in_bounds_mon) {
            $jourPresence = $this->addElement($elParent, "jourPresence");
            $this->addAttribute($jourPresence, "jour", "lundi");
        }
        if ($mbRhs->_in_bounds_tue) {
            $jourPresence = $this->addElement($elParent, "jourPresence");
            $this->addAttribute($jourPresence, "jour", "mardi");
        }
        if ($mbRhs->_in_bounds_wed) {
            $jourPresence = $this->addElement($elParent, "jourPresence");
            $this->addAttribute($jourPresence, "jour", "mercredi");
        }
        if ($mbRhs->_in_bounds_thu) {
            $jourPresence = $this->addElement($elParent, "jourPresence");
            $this->addAttribute($jourPresence, "jour", "jeudi");
        }
        if ($mbRhs->_in_bounds_fri) {
            $jourPresence = $this->addElement($elParent, "jourPresence");
            $this->addAttribute($jourPresence, "jour", "vendredi");
        }
        if ($mbRhs->_in_bounds_sat) {
            $jourPresence = $this->addElement($elParent, "jourPresence");
            $this->addAttribute($jourPresence, "jour", "samedi");
        }
        if ($mbRhs->_in_bounds_sun) {
            $jourPresence = $this->addElement($elParent, "jourPresence");
            $this->addAttribute($jourPresence, "jour", "dimanche");
        }
    }

    /**
     * Ajout des actes de rééducation
     *
     * @param DOMNode $elParent Node
     * @param CRHS    $rhs      RHS
     *
     * @return void
     */
    function addActesReeducation(DOMNode $elParent, CRHS $rhs)
    {
        $rhs->loadRefLignesActivites();
        $lignes = $rhs->_ref_lignes_activites;

        // Ajout des actes de rééducation
        foreach ($lignes as $_ligne) {
            $this->addActeReeducation($elParent, $_ligne, $rhs);
        }
    }

    /**
     * Ajout d'un acte de rééducation
     *
     * @param DOMNode            $elParent         Node
     * @param CLigneActivitesRHS $ligneActiviteRhs Ligne d'activité RHS
     * @param CRHS               $rhs              RHS
     *
     * @return void
     */
    function addActeReeducation(DOMNode $elParent, CLigneActivitesRHS $ligneActiviteRhs, CRHS $rhs)
    {
        // Actes lundi
        $this->addCDARRorCSARR($elParent, $ligneActiviteRhs, "mo-", $rhs->date_monday, $ligneActiviteRhs->qty_mon);

        // Actes mardi
        $this->addCDARRorCSARR($elParent, $ligneActiviteRhs, "tu-", $rhs->_date_tuesday, $ligneActiviteRhs->qty_tue);

        // Actes mercredi
        $this->addCDARRorCSARR($elParent, $ligneActiviteRhs, "we-", $rhs->_date_wednesday, $ligneActiviteRhs->qty_wed);

        // Actes jeudi
        $this->addCDARRorCSARR($elParent, $ligneActiviteRhs, "th-", $rhs->_date_thursday, $ligneActiviteRhs->qty_thu);

        // Actes vendredi
        $this->addCDARRorCSARR($elParent, $ligneActiviteRhs, "fr-", $rhs->_date_friday, $ligneActiviteRhs->qty_fri);

        // Actes samedi
        $this->addCDARRorCSARR($elParent, $ligneActiviteRhs, "sa-", $rhs->_date_saturday, $ligneActiviteRhs->qty_sat);

        // Actes dimanche
        $this->addCDARRorCSARR($elParent, $ligneActiviteRhs, "su-", $rhs->_date_sunday, $ligneActiviteRhs->qty_sun);
    }

    /**
     * Ajout d'un acte CDARR ou CSARR
     *
     * @param DOMNode            $elParent         Node
     * @param CLigneActivitesRHS $ligneActiviteRhs Ligne d'activité RHS
     * @param string             $day_prefix       Day prefix
     * @param string             $day              Day
     * @param int                $qty              Quantity
     *
     * @return void
     */
    function addCDARRorCSARR(DOMNode $elParent, CLigneActivitesRHS $ligneActiviteRhs, $day_prefix, $day, $qty)
    {
        if (!$qty) {
            return;
        }

        if ($ligneActiviteRhs->code_activite_cdarr) {
            $this->addActeCDARR($elParent, $ligneActiviteRhs, $day_prefix, $day, $qty);
        }
        if ($ligneActiviteRhs->code_activite_csarr) {
            $this->addActeCSARR($elParent, $ligneActiviteRhs, $day_prefix, $day, $qty);
        }
    }

    /**
     * Ajout d'un acte CDARR
     *
     * @param DOMNode            $elParent         Node
     * @param CLigneActivitesRHS $ligneActiviteRhs Ligne d'activité RHS
     * @param string             $day_prefix       Day prefix
     * @param string             $day              Day
     * @param int                $qty              Quantity
     *
     * @return void
     */
    function addActeCDARR(DOMNode $elParent, CLigneActivitesRHS $ligneActiviteRhs, $day_prefix, $day, $qty)
    {
        $intervenant = $ligneActiviteRhs->loadRefIntervenantCdARR();
        $executant   = $ligneActiviteRhs->loadRefExecutant();
        $sejour      = $ligneActiviteRhs->loadRefRHS()->loadRefSejour();

        $acteReeducation = $this->addElement($elParent, "acteReeducation");
        $action          = CAppUI::conf("hprimxml $this->evenement version") == "2.00" ? "creation" : "création";
        $this->addAttribute($acteReeducation, "action", $action);
        $this->addAttribute($acteReeducation, "typeIntervenant", $intervenant->code);

        $this->addElement(
            $acteReeducation,
            "dateAction",
            CMbDT::dateTimeXML($day . " " . CMbDT::time($sejour->entree))
        );

        $acteur = $this->addElement($acteReeducation, "acteur");
        $this->addProfessionnelSante($acteur, $executant);

        $identifiant = $this->addElement($acteReeducation, "identifiant");
        $this->addElement($identifiant, "emetteur", $day_prefix . $ligneActiviteRhs->_id);

        $CDARR = $this->addElement($acteReeducation, "CDARR");
        $this->addElement($CDARR, "codeCDARR", $ligneActiviteRhs->code_activite_cdarr);
        $this->addElement($CDARR, "quantite", $ligneActiviteRhs->$qty);
    }

    /**
     * Ajout d'un acte CSARR
     *
     * @param DOMNode            $elParent         Node
     * @param CLigneActivitesRHS $ligneActiviteRhs Ligne d'activité RHS
     * @param string             $day_prefix       Day prefix
     * @param string             $day              Day
     * @param int                $qty              Quantity
     *
     * @return void
     */
    function addActeCSARR(DOMNode $elParent, CLigneActivitesRHS $ligneActiviteRhs, $day_prefix, $day, $qty)
    {
        $intervenant = $ligneActiviteRhs->loadRefIntervenantCdARR();
        $executant   = $ligneActiviteRhs->loadRefExecutant();
        $sejour      = $ligneActiviteRhs->loadRefRHS()->loadRefSejour();

        for ($i = 0; $i < $qty; $i++) {
            $acteReeducation = $this->addElement($elParent, "acteReeducation");
            $action          = CAppUI::conf("hprimxml $this->evenement version") == "2.00" ? "creation" : "création";
            $this->addAttribute($acteReeducation, "action", $action);
            $this->addAttribute($acteReeducation, "typeIntervenant", $intervenant->code);

            $this->addElement(
                $acteReeducation,
                "dateAction",
                CMbDT::dateTimeXML($day . " " . CMbDT::time($sejour->entree))
            );

            $acteur = $this->addElement($acteReeducation, "acteur");
            $this->addProfessionnelSante($acteur, $executant);

            $identifiant = $this->addElement($acteReeducation, "identifiant");
            $this->addElement($identifiant, "emetteur", $day_prefix . $ligneActiviteRhs->_id . "-$i");

            $CSARR = $this->addElement($acteReeducation, "CSARR");
            $this->addElement($CSARR, "codeCsarr", $ligneActiviteRhs->code_activite_csarr);
            $this->addElement($CSARR, "dateRealisationCsarr", $day);
            $nombrePatientCsarr = $ligneActiviteRhs->nb_patient_seance ? $ligneActiviteRhs->nb_patient_seance : "1";
            $this->addElement($CSARR, "nombrePatientCsarr", $nombrePatientCsarr);
            $nb_intervenant_seance = $ligneActiviteRhs->nb_intervenant_seance ? $ligneActiviteRhs->nb_intervenant_seance : "1";
            $this->addElement($CSARR, "nombreIntervenantCsarr", $nb_intervenant_seance);

            if ($ligneActiviteRhs->_modulateurs) {
                $modulateur = reset($ligneActiviteRhs->_modulateurs);
                $this->addElement($CSARR, "codeModulateurLieuCsarr", $modulateur);
            }
        }
    }

    /**
     * Ajout des dépendances
     *
     * @param DOMNode $elParent Node
     * @param CRHS    $mbRhs    RHS
     *
     * @return void
     */
    function addDependances(DOMNode $elParent, CRHS $rhs)
    {
        $sejour     = $rhs->loadRefSejour();
        $dateAction = $sejour->entree;
        if ($rhs->_in_bounds_mon) {
            $dateAction = $rhs->date_monday . " " . CMbDT::time($sejour->entree);
        } elseif ($rhs->_in_bounds_tue) {
            $dateAction = $rhs->_date_tuesday . " " . CMbDT::time($sejour->entree);
        } elseif ($rhs->_in_bounds_wed) {
            $dateAction = $rhs->_date_wednesday . " " . CMbDT::time($sejour->entree);
        } elseif ($rhs->_in_bounds_thu) {
            $dateAction = $rhs->_date_thursday . "  " . CMbDT::time($sejour->entree);
        } elseif ($rhs->_in_bounds_fri) {
            $dateAction = $rhs->_date_friday . " " . CMbDT::time($sejour->entree);
        } elseif ($rhs->_in_bounds_sat) {
            $dateAction = $rhs->_date_saturday . " " . CMbDT::time($sejour->entree);
        } elseif ($rhs->_in_bounds_sun) {
            $dateAction = $rhs->_date_sunday . " " . CMbDT::time($sejour->entree);
        }

        $this->addElement($elParent, "dateAction", CMbDT::dateTimeXML($dateAction));

        $dependances = $rhs->loadRefDependances()->loadRefBilanRHS();
        $identifiant = $this->addElement($elParent, "identifiant");
        $this->addElement($identifiant, "emetteur", $dependances->_id);

        $this->addElement($elParent, "habillage", $dependances->habillage);
        $this->addElement($elParent, "deplacement", $dependances->deplacement);
        $this->addElement($elParent, "alimentation", $dependances->alimentation);
        $this->addElement($elParent, "continence", $dependances->continence);
        $this->addElement($elParent, "comportement", $dependances->comportement);
        $this->addElement($elParent, "relation", $dependances->relation);
    }

    /**
     * Ajout d'un chapitre d'un acte de rééducation
     *
     * @param DOMNode $elParent Node
     * @param CRHS    $mbRhs    RHS
     *
     * @return void
     */
    function addChapitreActeReeducation(DOMNode $elParent, CRHS $mbRhs)
    {
        $totauxType = $mbRhs->countTypeActivite();

        foreach ($totauxType as $mnemonique => $_total_type) {
            if (!$_total_type) {
                continue;
            }

            $chapitreActeReeducation = $this->addElement($elParent, "chapitreActeReeducation");

            $this->addAttribute($chapitreActeReeducation, "mnemonique", strtolower($mnemonique));

            $this->addElement($chapitreActeReeducation, "duree", $_total_type);
            $this->addElement($chapitreActeReeducation, "commentaire", CActiviteCdARR::get($mnemonique)->libelle);
        }
    }

    /**
     * Ajout des diagnostics SSR
     *
     * @param DOMNode $elParent Node
     * @param CRHS    $rhs      RHS
     *
     * @return void
     */
    function addDiagnosticsEtatSSR(DOMNode $elParent, CRHS $rhs)
    {
        $receiver = $this->_ref_receiver;

        // Finalité principale de prise en charge - FPP
        $this->addDiagnosticEtatSSR($elParent, self::transformXCodeCIM($rhs->FPP, $receiver), "fpc", $rhs);

        // Manifestation morbide principale - MMP
        $this->addDiagnosticEtatSSR($elParent, self::transformXCodeCIM($rhs->MMP, $receiver), "mm", $rhs);

        // Affection étiologique - AE
        $this->addDiagnosticEtatSSR($elParent, self::transformXCodeCIM($rhs->AE, $receiver), "ae", $rhs);

        $das_dad = $rhs->loadRefDASAndDAD();
        // Diagnostics associés significatifs - DAS
        if ($das = CMbArray::get($das_dad, "DAS")) {
            foreach ($das as $_das) {
                $this->addDiagnosticEtatSSR($elParent, self::transformXCodeCIM($_das->code, $receiver), "ds", $rhs);
            }
        }

        // Diagnostic associé documentaire - DAD
        if ($dad = CMbArray::get($das_dad, "DAD")) {
            foreach ($dad as $_dad) {
                $this->addDiagnosticEtatSSR($elParent, self::transformXCodeCIM($_dad->code, $receiver), "dd", $rhs);
            }
        }
    }

    /**
     * Ajout d'un diagnostic SSR
     *
     * @param DOMNode $elParent       Node
     * @param string  $codeCim10      Code CIM10
     * @param string  $typeDiagnostic Type du diagnostic
     * @param CRHS    $rhs            RHS
     */
    function addDiagnosticEtatSSR(DOMNode $elParent, $codeCim10, $typeDiagnostic, CRHS $rhs)
    {
        if (!$codeCim10) {
            return;
        }

        $diagnostic = $this->addElement($elParent, "diagnostic");

        $action = CAppUI::conf("hprimxml $this->evenement version") == "2.00" ? "creation" : "création";

        if (CAppUI::conf("sa CSa send_diag_immediately", $this->_ref_receiver->loadRefGroup())) {
            switch ($typeDiagnostic) {
                case "fpc":
                    // Cas de la création
                    if (!$rhs->_old->FPP && $rhs->FPP) {
                        $action = CAppUI::conf("hprimxml $this->evenement version") == "2.00" ? "creation" : "création";
                    } // Cas de la modification
                    elseif ($rhs->FPP) {
                        $action = "modification";
                    } else {
                        $action = "suppression";
                    }
                    break;
                case "mm":
                    // Cas de la création
                    if (!$rhs->_old->MMP && $rhs->MMP) {
                        $action = CAppUI::conf("hprimxml $this->evenement version") == "2.00" ? "creation" : "création";
                    } // Cas de la modification
                    elseif ($rhs->MMP) {
                        $action = "modification";
                    } else {
                        $action = "suppression";
                    }
                    break;
                case "ae":
                    // Cas de la création
                    if (!$rhs->_old->AE && $rhs->AE) {
                        $action = CAppUI::conf("hprimxml $this->evenement version") == "2.00" ? "creation" : "création";
                    } // Cas de la modification
                    elseif ($rhs->AE) {
                        $action = "modification";
                    } else {
                        $action = "suppression";
                    }
                    break;
                default:
            }
        }

        $this->addAttribute($diagnostic, "action", $action);
        $this->addAttribute($diagnostic, "type", $typeDiagnostic);

        $this->addElement($diagnostic, "codeCim10", $codeCim10);
    }

    /**
     * Transforme le X en + dans le code CIM
     *
     * @param int              $code     code
     * @param CInteropReceiver $receiver receiver
     *
     * @return string
     */
    static function transformXCodeCIM($code, CInteropReceiver $receiver)
    {
        if ($receiver->_configs["transform_X_code_CIM"]) {
            $code = preg_replace("/([A-Z][0-9]{2,3})X/", "\\1+", $code);
        }

        return strtoupper($code);
    }

    /**
     * Ajout des diagnostics
     *
     * @param DOMNode $elParent Node
     * @param CSejour $mbSejour Séjour
     *
     * @return void
     */
    function addDiagnosticsEtat(DOMNode $elParent, CSejour $mbSejour)
    {
        $send_only_das_diags = CAppUI::conf("hprimxml send_only_das_diags");
        $type_diag_dp        = $mbSejour->type == "ssr" ? "fpc" : "dp";

        $receiver = $this->_ref_receiver;

        $mbSejour->loadOldObject();

        $this->addDiagnosticEtat(
            $elParent,
            self::transformXCodeCIM($mbSejour->DP ? $mbSejour->DP : $mbSejour->_old->DP, $receiver),
            $send_only_das_diags ? "ds" : $type_diag_dp,
            $mbSejour
        );

        if ($mbSejour->DR || $mbSejour->_old->DR) {
            $type_diag_dr = $mbSejour->type == "ssr" ? "mm" : "dr";
            $this->addDiagnosticEtat(
                $elParent,
                self::transformXCodeCIM($mbSejour->DR ? $mbSejour->DR : $mbSejour->_old->DR, $receiver),
                $send_only_das_diags ? "ds" : $type_diag_dr,
                $mbSejour
            );
        }

        $mbSejour->loadRefDossierMedical();
        $codes_cim = $mbSejour->_ref_dossier_medical->_codes_cim;
        if (count($codes_cim) <= 0) {
            return;
        }

        foreach ($codes_cim as $_diag_significatif) {
            $this->addDiagnosticEtat(
                $elParent,
                self::transformXCodeCIM($_diag_significatif, $receiver),
                "ds",
                $mbSejour
            );
        }
    }

    /**
     * Ajout d'un diagnostic
     *
     * @param DOMNode $elParent       Node
     * @param string  $codeCim10      Code CIM10
     * @param string  $typeDiagnostic Type du diagnostic
     * @param CSejour $mbSejour       mbSejour
     */
    function addDiagnosticEtat(DOMNode $elParent, $codeCim10, $typeDiagnostic, $mbSejour)
    {
        if (!$codeCim10) {
            return;
        }

        $diagnostic = $this->addElement($elParent, "diagnostic");

        $action = CAppUI::conf("hprimxml $this->evenement version") == "2.00" ? "creation" : "création";

        if (CAppUI::conf("sa CSa send_diag_immediately", $this->_ref_receiver->loadRefGroup())) {
            switch ($typeDiagnostic) {
                case "dp":
                    // Cas de la création
                    if (!$mbSejour->_old->DP && $mbSejour->DP) {
                        $action = CAppUI::conf("hprimxml $this->evenement version") == "2.00" ? "creation" : "création";
                    } // Cas de la modification
                    elseif ($mbSejour->DP) {
                        $action = "modification";
                    } else {
                        $action = "suppression";
                    }
                    break;
                case "dr":
                    // Cas de la création
                    if (!$mbSejour->_old->DR && $mbSejour->DR) {
                        $action = CAppUI::conf("hprimxml $this->evenement version") == "2.00" ? "creation" : "création";
                    } // Cas de la modification
                    elseif ($mbSejour->DR) {
                        $action = "modification";
                    } else {
                        $action = "suppression";
                    }
                    break;
                default:
            }
        }

        $this->addAttribute($diagnostic, "action", $action);
        $this->addAttribute($diagnostic, "type", $typeDiagnostic);

        $this->addElement($diagnostic, "codeCim10", $codeCim10);
    }

    /**
     * Ajout des IGS2
     *
     * @param DOMNode  $elParent Node
     * @param CExamIgs $examIGS  Séjour
     *
     * @return void|null
     */
    function addIGS2(DOMNode $elParent, CExamIgs $examIGS)
    {
        // Ajout des éléments
        $this->addDateTimeElement($elParent, "dateAction", $examIGS->date);
        $this->addElement($elParent, "identifiant");

        $identifiant = $this->addElement($elParent, "identifiant");
        $this->addElement($identifiant, "emetteur", "$examIGS->_id");

        $this->addElement($elParent, "valeurGlobale", $examIGS->scoreIGS);

        // Ajout des attributs
        if (CAppUI::conf("sa CSa send_igs_immediately", $this->_ref_receiver->loadRefGroup())) {
            $current_log = $examIGS->loadLastLog();
            switch ($current_log->type) {
                case "store":
                    $action = "modification";
                    break;
                case "delete":
                    $action = "suppression";
                    break;
                default:
                    $action = CAppUI::conf("hprimxml $this->evenement version") == "2.00" ? "creation" : "création";
            }
        } else {
            $action = CAppUI::conf("hprimxml $this->evenement version") == "2.00" ? "creation" : "création";
        }
        $this->addAttribute($elParent, "action", $action);

        if ($examIGS->admission) {
            $admission = [
                "8" => "chirUrgente",
                "6" => "medecine",
                "0" => "chirProgrammee",
            ];
            $this->addAttribute($elParent, "modeAdmission", CMbArray::get($admission, $examIGS->admission));
        }

        if ($examIGS->maladies_chroniques) {
            $maladies_chroniques = [
                "9"  => "cancerMetastatique",
                "10" => "maladieHematologiqueMaligne",
                "17" => "sida",
            ];
            $this->addAttribute(
                $elParent,
                "maladieChronique",
                CMbArray::get($maladies_chroniques, $examIGS->maladies_chroniques)
            );
        }

        if ($examIGS->glasgow) {
            $scoreGlasgow = [
                "26" => "inferieurA6",
                "13" => "6A8",
                "7"  => "9A10",
                "5"  => "11A13",
                "0"  => "14A18",
            ];
            $this->addAttribute($elParent, "scoreGlasgow", CMbArray::get($scoreGlasgow, $examIGS->glasgow));
        }

        if ($examIGS->age) {
            $age = [
                "0"  => "inferieurA40",
                "7"  => "40A59",
                "12" => "60A69",
                "15" => "70A74",
                "16" => "75A79",
                "18" => "75A79",
            ];
            $this->addAttribute($elParent, "age", CMbArray::get($age, $examIGS->age));
        }

        if ($examIGS->TA) {
            $TA = [
                "13" => "inferieurA70",
                "5"  => "70A99",
                "0"  => "100A199",
                "2"  => "superieurEgalA200",
            ];
            $this->addAttribute($elParent, "tensionArterielleSystolique", CMbArray::get($TA, $examIGS->TA));
        }

        if ($examIGS->FC) {
            $FC = [
                "11" => "inferieurA40",
                "2"  => "40A69",
                "0"  => "70A119",
                "4"  => "120A159",
                "7"  => "superieurEgalA160",
            ];
            $this->addAttribute($elParent, "frequenceCardiaque", CMbArray::get($FC, $examIGS->FC));
        }

        if ($examIGS->temperature) {
            $temperature = [
                "0" => "inferieurA39",
                "3" => "superieurEgalA39",
            ];
            $this->addAttribute($elParent, "temperature", CMbArray::get($temperature, $examIGS->temperature));
        }

        if ($examIGS->PAO2_FIO2) {
            $PAO2_FIO2 = [
                "11" => "inferieurA100",
                "9"  => "100A199",
                "6"  => "superieurEgalA200",
            ];
            $this->addAttribute($elParent, "paO2_FIO2", CMbArray::get($PAO2_FIO2, $examIGS->PAO2_FIO2));
        }

        if ($examIGS->diurese) {
            $diurese = [
                "11" => "inferieurA0,5",
                "4"  => "0,5A0,999",
                "0"  => "superieurA1",
            ];
            $this->addAttribute($elParent, "diurese", CMbArray::get($diurese, $examIGS->diurese));
        }

        if ($examIGS->uree) {
            $uree = [
                "0"  => "inferieurA10",
                "6"  => "10A29,9",
                "10" => "superieurA30",
            ];
            $this->addAttribute($elParent, "ureeSanguine", CMbArray::get($uree, $examIGS->uree));
        }

        if ($examIGS->globules_blancs) {
            $Leucocytes = [
                "12" => "inferieurA1",
                "0"  => "1A19,9",
                "3"  => "superieurEgalA20",
            ];
            $this->addAttribute($elParent, "leucocytes", CMbArray::get($Leucocytes, $examIGS->globules_blancs));
        }

        if ($examIGS->kaliemie) {
            $kaliemie = [
                "3a" => "inferieurA3",
                "0"  => "3A4,9",
                "3b" => "superieurEgalA5",
            ];
            $this->addAttribute($elParent, "kaliemie", CMbArray::get($kaliemie, $examIGS->kaliemie));
        }

        if ($examIGS->natremie) {
            $natremie = [
                "5" => "inferieurA125",
                "0" => "125A144",
                "1" => "superieurEgalA145",
            ];
            $this->addAttribute($elParent, "natremie", CMbArray::get($natremie, $examIGS->natremie));
        }

        if ($examIGS->HCO3) {
            $HCO3 = [
                "6" => "inferieurA15",
                "3" => "15A19",
                "0" => "superieurEgalA20",
            ];
            $this->addAttribute($elParent, "HCO3", CMbArray::get($HCO3, $examIGS->HCO3));
        }

        if ($examIGS->billirubine) {
            $billirubine = [
                "0" => "inferieurA68,4",
                "4" => "68,4A102,5",
                "9" => "superieurA102,6",
            ];
            $this->addAttribute($elParent, "bilirubine", CMbArray::get($billirubine, $examIGS->billirubine));
        }
    }

    /**
     * Ajout de la naissance
     *
     * @param DOMNode $elParent Node
     * @param string  $sejour   Séjour
     */
    function addNaissance(DOMNode $elParent, CSejour $sejour)
    {
        // poidsNouveauNe
        // ageGestationnel
        // poidsNouveauNe
    }

    /**
     * Ajout des frais divers
     *
     * @param DOMNode      $elParent      Node
     * @param CFraisDivers $mbFraisDivers Frais divers
     *
     * @return void
     */
    function addFraisDivers(DOMNode $elParent, CFraisDivers $mbFraisDivers)
    {
        $fraisDivers = $this->addElement($elParent, "FraisDivers");

        // Action réalisée
        $action = CAppUI::conf("hprimxml $this->evenement version") == "2.00" ? "creation" : "création";
        $this->addAttribute($fraisDivers, "action", $action);
        //Produit facturable
        $this->addAttributeYes($fraisDivers, "facturable");

        // Date de l'événement
        $this->addDateTimeElement($fraisDivers, "dateAction");

        // Acteur déclencheur de cet action dans l'application créatrice.
        $mbExecutant = $mbFraisDivers->loadRefExecutant();
        $acteur      = $this->addElement($fraisDivers, "acteur");
        $this->addProfessionnelSante($acteur, $mbExecutant);

        // Correspond à l'identification de la ligne de saisie
        $identifiant = $this->addElement($fraisDivers, "identifiant");
        $emetteur    = $this->addElement($identifiant, "emetteur", "$mbFraisDivers->_id");

        // Lettre clé
        $this->addElement($fraisDivers, "lettreCle", $mbFraisDivers->_ref_type->code);
        // Coefficient
        $this->addElement($fraisDivers, "coefficient", $mbFraisDivers->coefficient);
        // Quantité de produits
        $this->addElement($fraisDivers, "quantite", $mbFraisDivers->quantite);

        // Date d'execution
        $execute = $this->addElement($fraisDivers, "execute");
        $this->addDateHeure($execute, $mbFraisDivers->execution);

        // Montant des frais
        $montant = $this->addElement($fraisDivers, "montant");
        $this->addTypeMontant($montant, $mbFraisDivers);
    }

    /**
     * Ajout du type du montant
     *
     * @param DOMNode      $elParent      Node
     * @param CFraisDivers $mbFraisDivers Frais divers
     *
     * @return void
     */
    function addTypeMontant($elParent, CFraisDivers $mbFraisDivers)
    {
        $this->addElement($elParent, "total", $mbFraisDivers->montant_base);
    }

    /**
     * Ajout des frais divers de prestations
     *
     * @param DOMNode $elParent     Node
     * @param array   $_item_presta Prestation
     * @param string  $date         Date
     *
     * @return void
     */
    function addFraisDiversPrestas(DOMNode $elParent, $_item_presta, $date)
    {
        $receiver = $this->_ref_receiver;

        /** @var CItemPrestation $item_prestation */
        $item_prestation = $_item_presta["item"];

        if ($_item_presta["sous_item_facture"]) {
            $sous_item = $_item_presta["sous_item_facture"];
            $idex      = $sous_item->loadLastId400($receiver->_tag_hprimxml);
        } else {
            $idex = $item_prestation->loadLastId400($receiver->_tag_hprimxml);
        }

        $fraisDivers = $this->addElement($elParent, "FraisDivers");

        // Action réalisée
        $action = CAppUI::conf("hprimxml $this->evenement version") == "2.00" ? "creation" : "création";
        $this->addAttribute($fraisDivers, "action", $action);
        //Produit facturable
        $this->addAttribute($fraisDivers, "facturable", $item_prestation->facturable);

        // Correspond à l'identification de la ligne de saisie
        $identifiant = $this->addElement($fraisDivers, "identifiant");
        $this->addElement($identifiant, "emetteur", $item_prestation->_id);

        // Lettre clé
        $this->addElement($fraisDivers, "lettreCle", $idex->_id ? $idex->id400 : $item_prestation->nom);
        // Coefficient
        $this->addElement($fraisDivers, "coefficient", CMbArray::get($_item_presta, "quantite"));
        // Quantité de produits
        $this->addElement($fraisDivers, "quantite", CMbArray::get($_item_presta, "quantite"));

        // Date d'execution
        $execute = $this->addElement($fraisDivers, "execute");
        $this->addDateHeure($execute, $date);
    }

    /**
     * Ajout du mouvement
     *
     * @param DOMNode      $elParent    Node
     * @param CAffectation $affectation Affectation
     *
     * @return void
     */
    function addMouvement(DOMNode $elParent, CAffectation $affectation)
    {
        $receiver = $this->_ref_receiver;

        $mouvement = $this->addElement($elParent, "mouvement");

        // Correspond à l'identification de l'affectation
        $identifiant = $this->addElement($mouvement, "identifiant");

        // Recherche d'une affectation existante
        $tag = $receiver->_tag_hprimxml;

        $idex = CIdSante400::getMatch("CAffectation", $tag, null, $affectation->_id);

        $this->addElement($identifiant, "emetteur", $idex->_id ? $idex->id400 : $affectation->_id);

        // Traitement du médecin responsable du séjour
        $this->addMedecinResponsable($mouvement, $affectation->_ref_sejour->_ref_praticien);

        // Emplacement
        $this->addEmplacement($mouvement, $affectation);

        // Debut de l'affectation
        $debut = $this->addElement($mouvement, "debut");
        $this->addDateHeure($debut, $affectation->entree);

        // Fin de l'affectation
        $fin = $this->addElement($mouvement, "fin");
        $this->addDateHeure($fin, $affectation->sortie);

        $unitesFonctionnellesResponsables = $this->addElement($mouvement, "unitesFonctionnellesResponsables");

        $this->addUFResponsable($unitesFonctionnellesResponsables, $affectation);
    }

    /**
     * Ajout de l'emplacement du mouvement
     *
     * @param DOMNode      $elParent    Node
     * @param CAffectation $affectation Affectation
     *
     * @return void
     */
    function addEmplacement(DOMNode $elParent, CAffectation $affectation)
    {
        $receiver = $this->_ref_receiver;

        if (!$receiver->_configs["send_movement_location"]) {
            return;
        }

        $emplacement = $this->addElement($elParent, "emplacement");

        $affectation->loadRefLit()->loadRefChambre()->loadRefService();

        // Chambre
        $lit     = $affectation->_ref_lit;
        $chambre = $lit->_ref_chambre;
        $idex    = CIdSante400::getMatchFor($chambre, $receiver->_tag_chambre);
        $code    = $idex->_id ? $idex->id400 : $chambre->_id;
        $this->addCodeLibelleCommentaire(
            $emplacement,
            "chambre",
            $code,
            $chambre->nom,
            null,
            $chambre->caracteristiques
        );

        // Lit
        $idex = CIdSante400::getMatchFor($lit, $receiver->_tag_lit);
        $code = $idex->_id ? $idex->id400 : $lit->_id;
        $this->addCodeLibelleCommentaire($emplacement, "lit", $code, $lit->nom, null, $lit->nom_complet);

        // Chambre seul
        $this->addAttribute($emplacement, "chambreSeul", self::convertToBool($chambre->_chambre_seule));
    }

    /**
     * Ajout de l'UF responsable
     *
     * @param DOMNode      $elParent    Node
     * @param CAffectation $affectation Affectation
     *
     * @return void
     */
    function addUFResponsable(DOMNode $elParent, CAffectation $affectation)
    {
        $ufs = $affectation->getUFs();

        $uf_hebergement = CMbArray::get($ufs, "hebergement");
        if (isset($uf_hebergement->_id)) {
            $uniteFonctionnelleResponsable = $this->addElement($elParent, "uniteFonctionnelleResponsable");
            $this->addCodeLibelleAttribute(
                $uniteFonctionnelleResponsable,
                $uf_hebergement->code,
                $uf_hebergement->libelle,
                "responsabilite",
                "h"
            );
        }

        $uf_medicale = CMbArray::get($ufs, "medicale");
        if (isset($uf_medicale->_id)) {
            $uniteFonctionnelleResponsable = $this->addElement($elParent, "uniteFonctionnelleResponsable");
            $this->addCodeLibelleAttribute(
                $uniteFonctionnelleResponsable,
                $uf_medicale->code,
                $uf_medicale->libelle,
                "responsabilite",
                "m"
            );
        }

        $uf_soins = CMbArray::get($ufs, "soins");
        if (isset($uf_soins->_id)) {
            $uniteFonctionnelleResponsable = $this->addElement($elParent, "uniteFonctionnelleResponsable");
            $this->addCodeLibelleAttribute(
                $uniteFonctionnelleResponsable,
                $uf_soins->code,
                $uf_soins->libelle,
                "responsabilite",
                "s"
            );
        }
    }

    function addCodeLibelleAttribute(DOMNode $elParent, $code, $libelle, $attName, $attValue)
    {
        $code = str_replace(" ", "", $code);
        $this->addTexte($elParent, "code", $code, 10);
        $this->addTexte($elParent, "libelle", $libelle, 35);

        $this->addAttribute($elParent, $attName, $attValue);
    }
}
