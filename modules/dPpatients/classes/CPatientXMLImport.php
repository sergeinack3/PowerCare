<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use DirectoryIterator;
use DOMElement;
use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CPlageHoraire;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\Import\CExternalDBImport;
use Ox\Core\Import\CMbXMLObjectImport;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Cabinet\CBanque;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Ccam\CActe;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Facturation\CFactureEtablissement;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mpm\CMomentUnitaire;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\Mpm\CPrisePosologie;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CCategoryPrescription;
use Ox\Mediboard\Prescription\CElementPrescription;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\System\CContentHTML;

/**
 * Utility class for importing objects
 */
class CPatientXMLImport extends CMbXMLObjectImport
{
    /** @var string */
    protected $name_suffix;

    /** @var int */
    protected $error_count = 0;

    /** @var array */
    protected $imported = [];

    /** @var string[] */
    protected $import_order = [
        // Structure objects
        "//object[@class='CGroups']",
        "//object[@class='CMediusers']",
        "//object[@class='CUser']",
        "//object[@class='CService']",
        "//object[@class='CFunctions']",
        "//object[@class='CBlocOperatoire']",
        "//object[@class='CSalle']",

        "//object[@class='CMedecin']",
        "//object[@class='CPatient']",
        "//object[@class='CDossierMedical']",
        "//object[@class='CSejour']",
        "//object[@class='CPlageOp']",
        "//object[@class='COperation']",
        "//object[@class='CConsultation']",
        "//object[@class='CConstanteMedicale']",
        "//object[@class='CFile']",
        "//object[@class='CCompteRendu']",

        // Import prescriptions
        "//object[@class='CMomentUnitaire']",
        "//object[@class='CCategoryPrescription']",
        "//object[@class='CPrescription']",
        "//object[@class='CPrescriptionLineMedicament']",
        "//object[@class='CPrescriptionLineElement']",
        "//object[@class='CPrisePosologie']",
        // Fin import prescriptions

        // Other objects
        "//object",
    ];

    /** @var string */
    protected $directory;

    /** @var string */
    protected $files_directory;

    /** @var bool */
    protected $update_data = false;

    /** @var bool */
    protected $update_patient = false;

    /** @var string string */
    protected $patient_name = "";

    /** @var string[] */
    public const IGNORED_CLASSES_TAMM = [
        "CSejour",
        "COperation",
    ];

    /** @var string[] */
    public static $_ignored_classes = [
        // Structure
        "CGroups",
        "CMediusers",
        "CUser",
        "CService",
        "CFunctions",
        "CBlocOperatoire",
        "CSalle",
        "CUniteFonctionnelle",
    ];

    /** @var string[] */
    public static $_prescription_classes = [
        "CPrescription",
        "CPrescriptionLineMedicament",
        "CPrescriptionLineComment",
        "CPrescriptionLineElement",
        "CMomentUnitaire",
        "CPrisePosologie",
        "CElementPrescription",
        "CCategoryPrescription",
    ];

    /** @var array */
    protected $_tmp_ignored_classes = [];

    /** @var bool */
    private $oxCabinet;

    public function __construct($filename)
    {
        parent::__construct($filename);

        $this->oxCabinet = (bool)CModule::getActive('oxCabinet');
    }

    /**
     * @inheritdoc
     */
    public function importObject(DOMElement $element): void
    {
        if (!$element) {
            return;
        }

        $id = $element->getAttribute("id");

        // Avoid importing the same object multiple time from a single XML file
        if (isset($this->imported[$id])) {
            return;
        }

        $this->name_suffix = " (import du " . CMbDT::dateTime() . ")";

        $_class          = $element->getAttribute("class");
        $imported_object = null;

        $import_tag = $this->getImportTag();

        $class = null;
        if ($this->oxCabinet && (str_starts_with($id, 'CSejour') || str_starts_with($id, 'COperation'))) {
            $class = 'CEvenementPatient';
        }

        // Check if the object has been imported
        $idex   = self::lookupObject($id, $import_tag, $class);
        $object = null;
        if ($idex->_id) {
            $this->imported[$id] = true;
            $object              = $idex->loadTargetObject();
            $this->map[$id]      = $object->_guid;

            if (!$this->update_data) {
                if (
                    !isset($this->options['link_file_to_op']) || !$this->options['link_file_to_op']
                    || ($_class != "CContentHTML" && $_class != "CCompteRendu")
                ) {
                    return;
                }
            }
        }

        if ($this->isIgnored($_class)) {
            return;
        }

        switch ($_class) {
            // COperation = Intervention: Données incorrectes, Le code CCAM 'QZEA024 + R + J' n'est pas valide
            case "CPatient":
                $imported_object = $this->importPatient($element, $object);
                break;

            case "CDossierMedical":
                $imported_object = $this->importDossierMedical($element, $object);
                break;

            case "CAntecedent":
                $imported_object = $this->importAntecedent($element, $object);
                break;

            case "CPlageOp":
                // No plageOp For oxCabinet, COperations are imported as CEvenementPatient
                if ($this->oxCabinet) {
                    break;
                }
            // No break
            case "CPlageconsult":
                $imported_object = $this->importPlage($element, $object);
                break;

            case "CFile":
                $imported_object = $this->importFile($element, $object);
                break;

            case "CCompteRendu":
                $imported_object = $this->importCompteRendu($element, $object);
                break;

            case "CConsultation":
                $imported_object = $this->importConsultation($element, $object);
                break;

            case "CSejour":
                // Special treatment for oxCabinet
                $imported_object = ($this->oxCabinet) ?
                    $this->importEventFromSejour($element, $object) : $this->importSejour($element, $object);
                break;

            case "COperation":
                // Special treatment for oxCabinet
                $imported_object = ($this->oxCabinet) ?
                    $this->importEventFromOperation($element, $object)
                    : $this->importOperation($element, $object, $import_tag);
                break;

            case "CContentHTML":
                $imported_object = $this->importContentHTML($element, $object);
                break;

            case "CBanque":
                $imported_object = $this->importBanque($element, $object);
                break;

            case "CMedecin":
                $imported_object = $this->importMedecin($element, $object);
                break;

            case "CFactureEtablissement":
                $imported_object = $this->importFactureEtablissement($element, $object);
                break;

            case "CPrescription":
                $imported_object = $this->importPrescription($element, $object);
                break;
            case "CPrescriptionLineMedicament":
                $imported_object = $this->importPrescriptionLineMedicament($element, $object);
                break;
            case "CPrisePosologie":
                $imported_object = $this->importPrisePosologie($element, $object);
                break;
            case "CMomentUnitaire":
                $imported_object = $this->importMomentUnitaire($element, $object);
                break;
            case "CPrescriptionLineElement":
                $imported_object = $this->importPrescriptionLineElement($element, $object);
                break;
            case "CElementPrescription":
                $imported_object = $this->importElementPrescription($element, $object);
                break;
            case "CCategoryPrescription":
                $imported_object = $this->importCategoryPrescription($element, $object);
                break;
            case "CIdSante400":
                $imported_object = $this->importExternalId($element, $object);
                break;

            case 'CFilesCategory':
                $imported_object = $this->importFileCategory($element, $object);
                break;
            case 'CConstantesMedicales':
                $imported_object = $this->importConstanteMedicale($element, $object);
                break;

            default:
                // Ignored classes
                if (!class_exists($_class) || $this->isIgnored($_class)) {
                    break;
                }

                $_object = $this->getObjectFromElement($element, $object);

                if ($_object instanceof CActe) {
                    $_object->_check_coded = false;
                }

                $_object->loadMatchingObjectEsc();

                if (!$this->storeObject($_object, $element)) {
                    break;
                }

                $imported_object = $_object;
                break;
        }

        // Store idex on new object
        if ($imported_object && $imported_object->_id) {
            // Do not search external id on CIdSante400 objects
            if ($imported_object instanceof CMbObject && !($imported_object instanceof CIdSante400)) {
                $idex->setObject($imported_object);
                $idex->id400 = $id;
                if ($msg = $idex->store()) {
                    CAppUI::stepAjax($msg, UI_MSG_WARNING);
                }
            }
        } elseif (!$this->isIgnored($_class)) {
            $this->error_count++;
            $this->writeLog("$id sans objet", null, UI_MSG_WARNING);
        }


        if ($imported_object) {
            $this->map[$id] = $imported_object->_guid;
        }

        $this->imported[$id] = true;
    }

    /**
     * @param DOMElement $element The DOM element to parse
     *
     * @return string
     */
    private function getCFileDirectory(DOMElement $element): string
    {
        [$object_class, $object_id] = explode("-", $element->getAttribute("object_id"));
        $uid = $this->getNamedValueFromElement($element, "file_real_filename");
        if ($this->files_directory) {
            $basedir = rtrim($this->files_directory, "/\\");
        } else {
            $basedir = rtrim($this->directory, "/\\");
        }

        $dir = $basedir . "/$object_class/" . intval($object_id / 1000) . "/$object_id/$uid";

        if (!is_dir($dir)) {
            $dir = $basedir . "/$object_class/$object_id/$uid";
        }

        return $dir;
    }

    /**
     * @inheritdoc
     */
    public function importObjectByGuid($guid): void
    {
        [$class, $id] = explode("-", $guid);

        if ($this->isIgnored($class)) {
            $lookup_guid = $guid;

            if ($class == "CMediusers") {
                // Idex are stored on the CUser
                $lookup_guid = "CUser-$id";
            }

            $import_tag = $this->getImportTag();
            $idex       = $this->lookupObject($lookup_guid, $import_tag);

            if ($idex->_id) {
                $this->map[$guid]      = "$class-$idex->object_id";
                $this->imported[$guid] = true;
            } elseif ($class == "CMediusers") {
                $this->map[$guid]      = CMediusers::get()->_guid;
                $this->imported[$guid] = true;
            }
        } else {
            /** @var DOMElement $_element */
            $_element = $this->xpath->query("//*[@id='$guid']")->item(0);
            if ($_element) {
                $this->importObject($_element);
            }
        }
    }

    /**
     * Set the files directory
     *
     * @param string $directory Files directory path
     *
     * @return void
     */
    public function setFilesDirectory(string $directory): void
    {
        $this->files_directory = $directory;
    }

    /**
     * @param string $directory    Directory path
     * @param string $patient_name Patient identifier
     *
     * @return void
     */
    public function setDirectory(string $directory, ?string $patient_name = ""): void
    {
        $this->directory    = $directory;
        $this->patient_name = $patient_name;
    }

    /**
     * @param bool $update_data    Update existing datas or not
     * @param bool $update_patient Update the patient infos
     *
     * @return void
     */
    public function setUpdateData(bool $update_data, bool $update_patient = false): void
    {
        $this->update_data    = $update_data;
        $this->update_patient = $update_patient;
    }

    /**
     * Try to get a consult for the file
     *
     * @param string $date       Date of the file
     * @param int    $patient_id Patient id
     * @param int    $author_id  Author id
     *
     * @return CConsultation
     * @throws Exception
     */
    private function getConsultForFile(string $date, string $patient_id, string $author_id): CConsultation
    {
        $ds   = CSQLDataSource::get('std');
        $date = CMbDT::date($date);

        $consultation = new CConsultation();
        $ljoin        = ["plageconsult" => "consultation.plageconsult_id = plageconsult.plageconsult_id",];
        $where        = [
            "consultation.patient_id" => $ds->prepare('= ?', $patient_id),
            "plageconsult.chir_id"    => $ds->prepare('= ?', $author_id),
            "plageconsult.date"       => $ds->prepare(
                'BETWEEN ?1 AND ?2',
                CMbDT::date('-1 DAY', $date),
                CMbDT::date('+1 DAY', $date)
            ),
        ];

        $consultation->loadObject($where, null, null, $ljoin);

        return $consultation;
    }

    /**
     * Try to get a sejour for the file
     *
     * @param string $date       Date of the file
     * @param int    $patient_id File patient_id
     *
     * @return CSejour
     * @throws Exception
     */
    private function getSejourForFile(string $date, string $patient_id): CSejour
    {
        $ds               = CSQLDataSource::get('std');
        $file_date_entree = CMbDT::dateTime("+1 DAY", $date);
        $file_date_sortie = CMbDT::dateTime("-1 DAY", $date);

        $sejour = new CSejour();
        $where  = [
            'entree'     => $ds->prepare('< ?', $file_date_entree),
            'sortie'     => $ds->prepare('> ?', $file_date_sortie),
            'patient_id' => $ds->prepare('= ?', $patient_id),
        ];

        $sejour->loadObject($where);

        return $sejour;
    }

    /**
     * Try to get an operation for the file
     *
     * @param string  $date   Date of the file
     * @param CSejour $sejour Sejour of the CFile
     *
     * @return COperation
     */
    private function getOperationForFile(string $date, CSejour $sejour): ?COperation
    {
        $file_date = CMbDT::date($date);
        $ds        = CSQLDataSource::get('std');

        $where      = [
            'date' => $ds->prepare(
                'BETWEEN ?1 AND ?2',
                CMbDT::date('-1 DAY', $file_date),
                CMbDT::date('+1 DAY', $file_date)
            ),
        ];
        $operations = $sejour->loadRefsOperations($where);

        return reset($operations);
    }

    /**
     * Import a CPatient from a XML element
     *
     * @param DOMElement $element XML element
     * @param CMbObject  $object  Object found
     *
     * @return CMbObject|CPatient|null
     * @throws Exception
     */
    private function importPatient(DOMElement $element, ?CMbObject $object): ?CPatient
    {
        /** @var CPatient $_patient */
        $_patient = $this->getObjectFromElement($element, $object);

        // Remove first or last space from nom/prenom
        // Remove double spaces which won't allow the matching to be done
        $_patient->nom    = trim(preg_replace('/\s+/', ' ', $_patient->nom));
        $_patient->prenom = trim(preg_replace('/\s+/', ' ', $_patient->prenom));

        if ($_patient->naissance == "0000-00-00") {
            $_patient->naissance = "1850-01-01";
        }

        $_ipp = null;
        // If the ipp_tag option is specified use it to search the patient by IPP
        if (isset($this->options['ipp_tag'])) {
            $_ipp = $this->searchIPP($this->options['ipp_tag']);
            if ($_ipp) {
                $_patient->_IPP = $_ipp;
                $_patient->loadFromIPP();
            }
        }

        $duplicates = 0;
        if (!$_patient->_id) {
            $where = [];
            // If the patient matching have to be done on more fields
            if (isset($this->options['additionnal_pat_matching'])) {
                foreach ($this->options['additionnal_pat_matching'] as $_field) {
                    $where[] = $_field;
                }
            }

            // Add additionnal fields matching and trim data in database for comparison
            $duplicates = $_patient->loadMatchingPatient(false, true, $where, true);
        }

        // If we don't want to update patients with duplicates return null;
        if (
            !$_patient->_id && isset($this->options["exclude_duplicate"]) && $this->options['exclude_duplicate']
            && $duplicates > 0
        ) {
            $this->setStop(true);
            $this->writeLog(CAppUI::tr("dPpatients-imports-duplicate exists"), $element, UI_MSG_WARNING);

            return null;
        }

        // Avoid updating patients that already exists
        if (
            isset($this->options['no_update_patients_exists']) && $this->options['no_update_patients_exists']
            && $_patient && $_patient->_id
        ) {
            $sejours = $_patient->loadRefsSejours();
            $consuls = $_patient->loadRefsConsultations();

            if ($sejours || $consuls) {
                if (isset($this->options['exclude_duplicate']) && $this->options['exclude_duplicate']) {
                    $this->setStop(true);
                    $this->writeLog(
                        CAppUI::tr("dPpatients-imports-sejour or consult exists"),
                        $element,
                        UI_MSG_WARNING
                    );

                    return null;
                }

                $this->_tmp_ignored_classes = array_merge(
                    CPatientXMLImport::$_prescription_classes,
                    [
                        "CDossierMedical",
                        "CAntecedent",
                        "CTraitement",
                    ]
                );

                $this->update_patient = false;
                $this->update_data    = false;
            }
        }

        if ($this->update_patient || !$_patient->_id) {
            $_patient = $this->getObjectFromElement($element, $_patient);

            $_patient->nom    = trim(preg_replace('/\s+/', ' ', $_patient->nom));
            $_patient->prenom = trim(preg_replace('/\s+/', ' ', $_patient->prenom));

            $is_new = !$_patient->_id;

            if ($is_new) {
                $_patient->getCivilityView();
                CAppUI::stepAjax("Patient '%s' créé", UI_MSG_OK, $_patient->_view);
            } else {
                CAppUI::stepAjax("Patient '%s' retrouvé", UI_MSG_OK, $_patient->_view);
            }

            if ($repaired_fields = $_patient->repair()) {
                $this->logRepair($repaired_fields, $element);
            }

            if ($msg = $_patient->store()) {
                $this->writeLog($msg, $element, UI_MSG_WARNING);
                $this->setStop(true);

                return null;
            }
        } else {
            CAppUI::stepAjax("Patient '%s' retrouvé", UI_MSG_OK, $_patient->_view);
        }

        if ($_ipp && $_patient && $_patient->_id && !$_patient->loadIPP()) {
            $_tag       = $_patient->getTagIpp();
            $ipp        = new CIdSante400();
            $ipp->tag   = $_tag;
            $ipp->id400 = $_ipp;
            $ipp->setObject($_patient);

            // Load matching tag to avoid creating multiple IPP
            $ipp->loadMatchingObject();

            if (!$ipp->_id) {
                if ($msg = $ipp->store()) {
                    $this->writeLog($msg, $element, UI_MSG_WARNING);
                }
            }
        }

        return $_patient;
    }

    /**
     * Search an IPP in the XML file
     *
     * @param string $ipp_tag Tag IPP to use for the research
     *
     * @return string
     */
    private function searchIPP(string $ipp_tag): string
    {
        $ipp = "";

        if ($ipp_tag) {
            // Récupération du noeud contenant la valeur de l'IPP du patient dans sa base d'origine
            $xpath = "//object[@class='CIdSante400'][field[@name='tag'] = '$ipp_tag']/field[@name='id400']";
            $node  = $this->xpath->query($xpath);

            if ($node->length > 0) {
                $ipp = $node->item(0)->nodeValue;
            }
        }

        return $ipp;
    }

    /**
     * Import a CDossierMedical from a XML element
     *
     * @param DOMElement $element XML element
     * @param CMbObject  $object  Object found
     *
     * @return CDossierMedical|CMbObject|null
     */
    private function importDossierMedical(DOMElement $element, ?CMbObject $object): ?CDossierMedical
    {
        /** @var CDossierMedical $_object */
        $_object = $this->getObjectFromElement($element, $object);

        $_dossier               = new CDossierMedical();
        $_dossier->object_id    = $_object->object_id;
        $_dossier->object_class = $_object->object_class;

        $_dossier->loadMatchingObjectEsc();

        if (!$_dossier->_id) {
            if ($_object->risque_MCJ_patient == 'sans') {
                $_object->risque_MCJ_patient = 'aucun';
            }

            // Compatibility with old branches
            $_object->repair();

            if (!$this->storeObject($_object, $element)) {
                return null;
            }

            return $_object;
        }

        return $_dossier;
    }

    /**
     * Import a CAntecedent from a XML element
     *
     * @param DOMElement $element XML element
     * @param CMbObject  $object  Object found
     *
     * @return CAntecedent|CMbObject|null
     */
    private function importAntecedent(DOMElement $element, ?CMbObject $object): ?CAntecedent
    {
        /** @var CAntecedent $_new_atcd */
        $_new_atcd = $this->getObjectFromElement($element, $object);

        // On cherche un ATCD similaire
        $_empty_atcd                     = new CAntecedent();
        $_empty_atcd->dossier_medical_id = $_new_atcd->dossier_medical_id;
        $_empty_atcd->type               = $_new_atcd->type ?: null;
        $_empty_atcd->appareil           = $_new_atcd->appareil ?: null;
        $_empty_atcd->annule             = $_new_atcd->annule ?: null;
        $_empty_atcd->date               = $_new_atcd->date ?: null;
        $_empty_atcd->rques              = $_new_atcd->rques ?: null;
        $_empty_atcd->loadMatchingObjectEsc();

        if (!$_empty_atcd->_id) {
            $_new_atcd->_forwardRefMerging = true; // To accept any ATCD type
            if (!$this->storeObject($_new_atcd, $element, true)) {
                return null;
            }
        }

        return $_new_atcd;
    }

    /**
     * Import a CPlageConsult or CPlageOp from a XML element
     *
     * @param DOMElement $element XML element
     * @param CMbObject  $object  Object found
     *
     * @return CMbObject|CPlageconsult|CPlageOp|mixed|null
     */
    private function importPlage(DOMElement $element, ?CMbObject $object): ?CPlageHoraire
    {
        /** @var CPlageOp|CPlageconsult $_plage */
        $_plage = $this->getObjectFromElement($element, $object);

        if (
            ($this->options['date_min'] && $_plage->date < $this->options['date_min'])
            || ($this->options['date_max'] && $_plage->date > $this->options['date_max'])
        ) {
            return null;
        }

        // Only use chir_id to check collision (no check for Agenda praticien in plage)
        $_plage->_spec->collision_keys = ['chir_id'];

        $_plage->hasCollisions();

        if (count($_plage->_colliding_plages)) {
            $_plage = reset($_plage->_colliding_plages);
            CAppUI::stepAjax("%s '%s' retrouvée", UI_MSG_OK, CAppUI::tr($_plage->_class), $_plage->_view);
        } elseif (!$this->storeObject($_plage, $element, true)) {
            return null;
        }

        return $_plage;
    }

    /**
     * Import a CFile from a XML element
     *
     * @param DOMElement $element XML element
     * @param CMbObject  $object  Object found
     *
     * @return CFile|CMbObject|null
     */
    private function importFile(DOMElement $element, ?CMbObject $object): ?CFile
    {
        /** @var CFile $_file */
        $_file                     = $this->getObjectFromElement($element, $object);
        $_file->file_real_filename = null;

        if (
            ($this->options['date_min'] && $_file->file_date < $this->options['date_min'])
            || ($this->options['date_max'] && $_file->file_date > $this->options['date_max'])
        ) {
            return null;
        }

        $_filedir = $this->getCFileDirectory($element);
        $_file->fillFields();

        if ($this->oxCabinet && ($_file->object_class === 'CSejour' || $_file->object_class === 'COperation')) {
            $_file->object_class = 'CEvenementPatient';
        }

        $this->changeFileContext($_file);

        if ($msg = $_file->check()) {
            $this->writeLog($msg, $element, UI_MSG_WARNING);

            return null;
        }

        $tmp_file               = new CFile();
        $tmp_file->object_class = $_file->object_class;
        $tmp_file->object_id    = $_file->object_id;
        $tmp_file->file_name    = $_file->file_name;
        $tmp_file->author_id    = $_file->author_id;
        $tmp_file->loadMatchingObjectEsc();

        if ($tmp_file->_id) {
            if (!$this->update_data) {
                return $tmp_file;
            }

            $_file = $tmp_file;
        }

        $correct_files = $this->options['correct_file'] ?? false;
        if (CExternalDBImport::isBadFileDate($_file->file_date, $_file->file_name, $correct_files, 'dPpatients')) {
            return null;
        }

        if (CAppUI::conf('dPfiles CFile prefix_format')) {
            $prefix = $_file->getPrefix($_file->file_date);
            if (strpos($_file->file_real_filename, $prefix) !== 0) {
                $_file->file_real_filename = $prefix . $_file->file_real_filename;
            }
        }

        $_file->setCopyFrom($_filedir);

        if (!$this->storeObject($_file, $element, true)) {
            return null;
        }

        return $_file;
    }

    private function changeFileContext(CFile $file): void
    {
        if (
            isset($this->options['link_file_to_op']) && $this->options['link_file_to_op']
            && $file->object_class == 'CPatient'
        ) {
            $this->searchCodable($file);
        } elseif ($file->object_class == 'CElementPrescription') {
            $this->searchElementPrescriptionTarget($file);
        }
    }

    private function searchElementPrescriptionTarget(CFile $file): void
    {
        $patient = $this->extractPatientFromMap();
        if ($patient === null) {
            return;
        }

        $file->object_class = $patient->_class;
        $file->object_id    = $patient->_id;
    }

    private function extractPatientFromMap(): ?CPatient
    {
        foreach ($this->map as $key => $value) {
            if (str_starts_with($key, 'CPatient-')) {
                return CPatient::loadFromGuid($value);
            }
        }

        return null;
    }

    private function searchCodable(CFile $file): void
    {
        $sejour = $this->getSejourForFile($file->file_date, $file->object_id);
        if ($sejour && $sejour->_id) {
            $operation = $this->getOperationForFile($file->file_date, $sejour);
            if ($operation && $operation->_id) {
                $file->object_class = 'COperation';
                $file->object_id    = $operation->_id;
            } else {
                $file->object_class = 'CSejour';
                $file->object_id    = $sejour->_id;
            }
        } else {
            $consult = $this->getConsultForFile($file->file_date, $file->object_id, $file->author_id);
            if (!$consult || !$consult->_id) {
                $consult = CExternalDBImport::makeConsult($file->object_id, $file->author_id, $file->file_date);
            }

            if ($consult && $consult->_id) {
                $file->object_class = 'CConsultation';
                $file->object_id    = $consult->_id;
            }
        }
    }

    /**
     * Import a CCompteRendu from a XML element
     *
     * @param DOMElement $element XML element
     * @param CMbObject  $object  Object found
     *
     * @return CCompteRendu|CMbObject|null
     */
    private function importCompteRendu(DOMElement $element, ?CMbObject $object): ?CCompteRendu
    {
        CCompteRendu::$import = true;

        /** @var CCompteRendu $cr */
        $cr = $this->getObjectFromElement($element, $object);

        if (
            ($this->options['date_min'] && $cr->creation_date < $this->options['date_min'])
            || ($this->options['date_max'] && $cr->creation_date > $this->options['date_max'])
        ) {
            return null;
        }

        if ($this->oxCabinet && ($cr->object_class === 'CSejour' || $cr->object_class === 'COperation')) {
            $cr->object_class = 'CEvenementPatient';
        }

        if (
            isset($this->options['link_file_to_op']) && $this->options['link_file_to_op']
            && $cr->object_class == 'CPatient'
        ) {
            $sejour = $this->getSejourForFile($cr->creation_date, $cr->object_id);
            if ($sejour && $sejour->_id) {
                $operation = $this->getOperationForFile($cr->creation_date, $sejour);
                if ($operation && $operation->_id) {
                    $cr->object_class = 'COperation';
                    $cr->object_id    = $operation->_id;
                } else {
                    $cr->object_class = 'CSejour';
                    $cr->object_id    = $sejour->_id;
                }
            } else {
                $consult = $this->getConsultForFile($cr->creation_date, $cr->object_id, $cr->author_id);
                if ($consult && $consult->_id) {
                    $cr->object_class = 'CConsultation';
                    $cr->object_id    = $consult->_id;
                }
            }
        }

        // If the object_id is not an int the target object has not been found
        if ($cr->object_id && ((int)$cr->object_id) === 0) {
            $this->writeLog('CCompteRendu-Error-No context', null, UI_MSG_WARNING);
            return null;
        }

        // If the cr is a modele (no object_id) check if group_id, function_id or user_id is found and imported.
        if (
            $this->oxCabinet &&
            !$cr->object_id &&
            !((int) $cr->group_id) && !((int)$cr->function_id) && !((int) $cr->user_id)
        ) {
            $this->writeLog('CCompteRendu-Error-Importing modele on instance is prohibited', null, UI_MSG_WARNING);
            return null;
        }

        $tmp_cr               = new CCompteRendu();
        $tmp_cr->object_class = $cr->object_class;
        $tmp_cr->object_id    = $cr->object_id;
        $tmp_cr->nom          = $cr->nom;
        $tmp_cr->author_id    = $cr->author_id;
        $tmp_cr->loadMatchingObjectEsc();

        if ($tmp_cr->_id) {
            if (!$this->update_data) {
                return $tmp_cr;
            }

            /** @var CCompteRendu $cr */
            $cr = $this->getObjectFromElement($element, $tmp_cr);
            if ($this->oxCabinet && ($cr->object_class === 'CSejour' || $cr->object_class === 'COperation')) {
                $cr->object_class = 'CEvenementPatient';
            }
        }

        // Font calibri is not GPL
        if ($cr->font == 'calibri') {
            $cr->font = 'carlito';
        }

        if (!$this->storeObject($cr, $element, true)) {
            return null;
        }

        return $cr;
    }

    /**
     * Import a CConsultation from a XML element
     *
     * @param DOMElement $element XML element
     * @param CMbObject  $object  Object found
     *
     * @return CConsultation|CMbObject|null
     */
    private function importConsultation(DOMElement $element, ?CMbObject $object): ?CConsultation
    {
        /** @var CConsultation $_object */
        $_object = $this->getObjectFromElement($element, $object);

        // TAMM does not use hospitalizations notion.
        if ($this->oxCabinet) {
            $_object->sejour_id = null;
        }

        if ($this->update_data && $_object->annule) {
            return null;
        }

        $_new_consult                  = new CConsultation();
        $_new_consult->patient_id      = $_object->patient_id;
        $_new_consult->plageconsult_id = $_object->plageconsult_id;
        // Do not mix canceled and active consult
        $_new_consult->annule = $_object->annule;
        $_new_consult->loadMatchingObjectEsc();

        if ($_new_consult->_id) {
            $_object = $_new_consult;

            if (!$this->update_data) {
                CAppUI::stepAjax(CAppUI::tr($_object->_class) . " '%s' retrouvée", UI_MSG_OK, $_object);
            } else {
                $_object                = $this->getObjectFromElement($element, $_object);
                $_object->_is_importing = true;

                if (!$this->storeObject($_object, $element, true)) {
                    return null;
                }
            }
        } elseif (!$this->storeObject($_object, $element, true)) {
            return null;
        }

        return $_object;
    }

    /**
     * Import a CSejour from a XML element
     *
     * @param DOMElement $element XML element
     * @param CMbObject  $object  Object found
     *
     * @return CMbObject|CSejour|mixed|null
     */
    private function importSejour(DOMElement $element, ?CMbObject $object): ?CSejour
    {
        /** @var CSejour $_object */
        $_object = $this->getObjectFromElement($element, $object);

        if (
            ($this->options['date_min'] && $_object->entree < $this->options['date_min'])
            || ($this->options['date_max'] && $_object->entree > $this->options['date_max'])
        ) {
            return null;
        }

        $_sej = $this->findSejour(
            $_object->patient_id,
            $_object->entree,
            $_object->type,
            $_object->praticien_id,
            $_object->annule
        );
        if ($_sej && $_sej->_id) {
            return $_sej;
        }

        $_collisions = $_object->getCollisions();

        if (count($_collisions)) {
            $_object = reset($_collisions);

            CAppUI::stepAjax(CAppUI::tr($_object->_class) . " '%s' retrouvé", UI_MSG_OK, $_object);
        } else {
            $_object->_hour_entree_prevue = null;
            $_object->_min_entree_prevue  = null;
            $_object->_hour_sortie_prevue = null;
            $_object->_min_sortie_prevue  = null;

            if (isset($this->options['uf_replace']) && $this->options['uf_replace']) {
                $_object->uf_medicale_id = $this->options['uf_replace'];
            }

            if (!$this->storeObject($_object, $element, true)) {
                return null;
            }
        }

        return $_object;
    }

    private function importEventFromSejour(DOMElement $element, ?CMbObject $object): ?CEvenementPatient
    {
        $values = self::getValuesFromElement($element);

        $this->importObjectByGuid($values['praticien_id']);
        $this->importObjectByGuid($values['patient_id']);

        $praticien_id       = self::getIdFromGuid($this->map[$values['praticien_id']]);
        $dossier_medical_id = $this->getDossierMedicalId($this->map[$values['patient_id']]);

        return $this->buildEvent(
            $values,
            $dossier_medical_id,
            ($praticien_id !== '') ? $praticien_id : null,
            $object,
            'sejour',
            CMbDT::dateTime($values['entree']),
            $this->buildDescription($values)
        );
    }

    private function importEventFromOperation(DOMElement $element, ?CMbObject $object): ?CEvenementPatient
    {
        $values = self::getValuesFromElement($element);

        $this->importObjectByGuid($values['chir_id']);
        $this->importObjectByGuid($values['sejour_id']);

        $imported_event = $this->map[$values['sejour_id']];
        if (!$imported_event) {
            return null;
        }

        /** @var CEvenementPatient $imported_event */
        $imported_event = CStoredObject::loadFromGuid($imported_event);
        if (!$imported_event || !$imported_event->_id) {
            return null;
        }

        $praticien_id = self::getIdFromGuid($this->map[$values['chir_id']]);

        return $this->buildEvent(
            $values,
            $imported_event->dossier_medical_id,
            $praticien_id,
            $object,
            'intervention',
            CMbDT::dateTime($values['date']),
            $this->buildOperationInfos($values)
        );
    }

    private function buildEvent(
        array $values,
        ?int $dossier_medical_id,
        ?int $praticien_id,
        ?CMbObject $object,
        string $type,
        string $datetime,
        string $description
    ): ?CEvenementPatient {
        if (!$dossier_medical_id || !$praticien_id) {
            return null;
        }

        $event                     = ($object) ?? new CEvenementPatient();
        $event->type               = $type;
        $event->date               = $datetime;
        $event->libelle            = $values['libelle'] ?? 'Importation';
        $event->praticien_id       = $praticien_id;
        $event->dossier_medical_id = $dossier_medical_id;
        $event->cancel             = $values['annule'] ?? $values['annulee'] ?? '0';
        $event->loadMatchingObjectEsc();

        if ($event->_id && !$this->update_data) {
            CAppUI::stepAjax(CAppUI::tr($event->_class) . " '%s' retrouvée", UI_MSG_OK, $event);

            return $event;
        }

        $event->description = $description;

        $exists = (bool)$event->_id;

        if (!$this->storeObject($event, null, true)) {
            return null;
        }

        if ($exists) {
            CAppUI::stepAjax('CEvenementPatient-msg-found');
        }

        return $event;
    }

    private function getDossierMedicalId(string $patient_guid): ?int
    {
        [$patient_class, $patient_id] = explode('-', $patient_guid);

        return CDossierMedical::dossierMedicalId($patient_id, $patient_class);
    }

    private function buildDescription(array $values): string
    {
        $desc = [
            sprintf('Séjour du %s au %s', $values['entree'], $values['sortie']),
        ];

        if (isset($values['rques'])) {
            $desc[] = $values['rques'];
        }

        return implode("\n", $desc);
    }

    private function buildOperationInfos(array $values): string
    {
        $desc = [
            sprintf('Opération à %s d\'une durée de %s', $values['time_operation'], $values['temp_operation']),
            sprintf('Côté : %s', $values['cote']),
        ];

        if (isset($values['codes_ccam'])) {
            $codes  = explode('|', $values['codes_ccam']);
            $desc[] = 'Actes CCAM effectués : ' . implode(', ', $codes);
        }

        if (isset($values['examen'])) {
            $desc[] = 'Examen : ' . $values['examen'];
        }

        return implode("\n", $desc);
    }

    /**
     * Import a COperation from a XML element
     *
     * @param DOMElement $element    XML element
     * @param CMbObject  $object     Object found
     * @param string     $import_tag Tag used for the import
     *
     * @return CMbObject|COperation|mixed|null
     */
    private function importOperation(DOMElement $element, ?CMbObject $object, string $import_tag): ?COperation
    {
        /** @var COperation $_interv */
        $_interv = $this->getObjectFromElement($element, $object);
        $_ds     = $_interv->getDS();

        $where = [
            "sejour_id"                  => $_ds->prepare("= ?", $_interv->sejour_id),
            "chir_id"                    => $_ds->prepare("= ?", $_interv->chir_id),
            "date"                       => $_ds->prepare("= ?", $_interv->date),
            "cote"                       => $_ds->prepare("= ?", $_interv->cote),
            "id_sante400.id_sante400_id" => "IS NULL",
        ];
        $ljoin = [
            "id_sante400" => "id_sante400.object_id = operations.operation_id AND
                            id_sante400.object_class = 'COperation' AND
                            id_sante400.tag = '$import_tag'",
        ];

        $_matching = $_interv->loadList($where, null, null, null, $ljoin);

        if (count($_matching)) {
            $_interv = reset($_matching);
            CAppUI::stepAjax("%s '%s' retrouvée", UI_MSG_OK, CAppUI::tr($_interv->_class), $_interv->_view);
        } else {
            $is_new = !$_interv->_id;
            if ($msg = $_interv->store(false)) {
                $this->writeLog($msg, $element, UI_MSG_WARNING);

                return null;
            }

            CAppUI::stepAjax(
                "%s '%s' " . ($is_new ? "créée" : "mise à jour"),
                UI_MSG_OK,
                CAppUI::tr($_interv->_class),
                $_interv->_view
            );
        }

        return $_interv;
    }

    /**
     * Import a CContentHTML from a XML element
     *
     * @param DOMElement $element XML element
     * @param CMbObject  $object  Object found
     *
     * @return CContentHTML|CMbObject|null
     */
    private function importContentHTML(DOMElement $element, ?CMbObject $object): ?CContentHTML
    {
        /** @var CContentHTML $_object */
        $_object = $this->getObjectFromElement($element, $object);

        if (
            ($this->options['date_min'] && $_object->last_modified < $this->options['date_min'])
            || ($this->options['date_max'] && $_object->last_modified > $this->options['date_max'])
        ) {
            return null;
        }

        $_object->content = stripslashes($_object->content);

        if (!$this->storeObject($_object, $element)) {
            return null;
        }

        return $_object;
    }

    /**
     * Import a CBanque from a XML element
     *
     * @param DOMElement $element XML element
     * @param CMbObject  $object  Object found
     *
     * @return CBanque|CMbObject|null
     */
    private function importBanque(DOMElement $element, ?CMbObject $object): ?CBanque
    {
        /** @var CBanque $_object */
        $_object = $this->getObjectFromElement($element, $object);

        $_new_banque      = new CBanque();
        $_new_banque->nom = $_object->nom;
        $_new_banque->loadMatchingObjectEsc();

        if ($_new_banque->_id) {
            $_object = $_new_banque;

            CAppUI::stepAjax(CAppUI::tr($_object->_class) . " '%s' retrouvée", UI_MSG_OK, $_object);
        } elseif (!$this->storeObject($_object, $element)) {
            return null;
        }

        return $_object;
    }

    /**
     * Import a CMedecin from a XML element
     *
     * @param DOMElement $element XML element
     * @param CMbObject  $object  Object found
     *
     * @return CMedecin|null
     */
    private function importMedecin(DOMElement $element, ?CMbObject $object): ?CMedecin
    {
        /** @var CMedecin $_object */
        $_object = $this->getObjectFromElement($element, $object);

        $siblings = $_object->loadExactSiblings();

        if ($siblings) {
            $_object = reset($siblings);
        }

        if (!$_object->_id) {
            $_object->actif = '0';
        }

        if (!$this->update_data && $_object->_id) {
            return $_object;
        }

        if (!$this->storeObject($_object, $element, true)) {
            return null;
        }

        return $_object;
    }

    /**
     * @param DOMElement $element XML element
     * @param CMbObject  $object  Object found
     *
     * @return CFactureEtablissement|null
     */
    private function importFactureEtablissement(DOMElement $element, ?CMbObject $object): ?CFactureEtablissement
    {
        /** @var CFactureEtablissement $_object */
        $_object = $this->getObjectFromElement($element, $object);

        if (
            ($this->options['date_min'] && CMbDT::date($_object->ouverture) < $this->options['date_min'])
            || ($this->options['date_max'] && CMbDT::date($_object->ouverture) > $this->options['date_max'])
        ) {
            return null;
        }

        $_object->loadMatchingObjectEsc();

        if (!$this->storeObject($_object, $element)) {
            return null;
        }

        return $_object;
    }

    /**
     * @param DOMElement $element XML element for the CPrescription
     * @param CMbObject  $object  Prescription found
     *
     * @return CPrescription|null
     */
    private function importPrescription(DOMElement $element, ?CMbObject $object): ?CPrescription
    {
        /** @var CPrescription $_object */
        $_object = $this->getObjectFromElement($element, $object);

        $presc               = new CPrescription();
        $presc->object_class = $_object->object_class;
        $presc->object_id    = $_object->object_id;

        $presc->loadMatchingObjectEsc();

        if ($presc->_id) {
            $_object = $presc;
        } elseif (!$this->storeObject($_object, $element)) {
            return null;
        }

        return $_object;
    }

    /**
     * @param DOMElement $element XML Element for the line
     * @param CMbObject  $object  Line found
     *
     * @return CPrescriptionLineMedicament|null
     */
    private function importPrescriptionLineMedicament(
        DOMElement $element,
        ?CMbObject $object
    ): ?CPrescriptionLineMedicament {
        if (!CPrescription::isMPMActive()) {
            return null;
        }

        /** @var CPrescriptionLineMedicament $_object */
        $_object = $this->getObjectFromElement($element, $object);

        if (
            ($this->options['date_min'] && $_object->debut && $_object->debut < $this->options['date_min'])
            || ($this->options['date_max'] && $_object->fin && $_object->fin > $this->options['date_max'])
        ) {
            return null;
        }

        $line                  = new CPrescriptionLineMedicament();
        $line->prescription_id = $_object->prescription_id;
        $line->code_cis        = $_object->code_cis;

        if ($_object->debut) {
            $line->debut = $_object->debut;
        }
        if ($_object->fin) {
            $line->fin = $_object->fin;
        }

        $line->loadMatchingObjectEsc();

        if ($line->_id) {
            $_object = $line;
        } elseif (!$this->storeObject($_object, $element)) {
            return null;
        }

        return $_object;
    }

    /**
     * @param DOMElement $element XML element for the CPrisePosologie
     * @param CMbObject  $object  CPrisePosologie found
     *
     * @return CPrisePosologie|null
     */
    private function importPrisePosologie(DOMElement $element, ?CMbObject $object): ?CPrisePosologie
    {
        /** @var CPrisePosologie $_object */
        $_object = $this->getObjectFromElement($element, $object);

        $prise                     = new CPrisePosologie();
        $prise->object_class       = $_object->object_class;
        $prise->object_id          = $_object->object_id;
        $prise->quantite           = $_object->quantite;
        $prise->unite_prise        = $_object->unite_prise;
        $prise->moment_unitaire_id = $_object->moment_unitaire_id;

        $prise->loadMatchingObjectEsc();

        if ($prise->_id) {
            $_object = $prise;
        } elseif (!$this->storeObject($_object, $element)) {
            return null;
        }

        return $_object;
    }

    /**
     * @param DOMElement $element XML element for the CMomentUnitaire
     * @param CMbObject  $object  CMomentUnitaire found
     *
     * @return CMomentUnitaire|null
     */
    private function importMomentUnitaire(DOMElement $element, ?CMbObject $object): ?CMomentUnitaire
    {
        /** @var CMomentUnitaire $_object */
        $_object = $this->getObjectFromElement($element, $object);

        $moment          = new CMomentUnitaire();
        $moment->libelle = $_object->libelle;

        $moment->loadMatchingObjectEsc();

        if ($moment->_id) {
            $_object = $moment;
        } elseif (!$this->storeObject($_object, $element)) {
            return null;
        }

        return $_object;
    }

    /**
     * @param DOMElement $element XML element for the CPrescriptionLineElement
     * @param CMbObject  $object  CPrescriptionLineElement found
     *
     * @return CPrescriptionLineElement|null
     */
    private function importPrescriptionLineElement(DOMElement $element, ?CMbObject $object): ?CPrescriptionLineElement
    {
        /** @var CPrescriptionLineElement $_object */
        $_object = $this->getObjectFromElement($element, $object);

        if (
            ($this->options['date_min'] && $_object->debut && $_object->debut < $this->options['date_min'])
            || ($this->options['date_max'] && $_object->fin && $_object->fin > $this->options['date_max'])
        ) {
            return null;
        }

        $line                          = new CPrescriptionLineElement();
        $line->prescription_id         = $_object->prescription_id;
        $line->element_prescription_id = $_object->element_prescription_id;

        if ($_object->debut) {
            $line->debut = $_object->debut;
        }

        if ($_object->fin) {
            $line->fin = $_object->fin;
        }

        $line->loadMatchingObjectEsc();

        if ($line->_id) {
            $_object = $line;
        } elseif (!$this->storeObject($_object, $element)) {
            return null;
        }

        return $_object;
    }

    /**
     * @param DOMElement $element XML element for the CEelementPrescription
     * @param CMbObject  $object  Object found
     *
     * @return CElementPrescription|null
     */
    private function importElementPrescription(DOMElement $element, ?CMbObject $object): ?CElementPrescription
    {
        /** @var CElementPrescription $_object */
        $_object = $this->getObjectFromElement($element, $object);

        $element_presc                           = new CElementPrescription();
        $element_presc->libelle                  = $_object->libelle;
        $element_presc->category_prescription_id = $_object->category_prescription_id;

        $element_presc->loadMatchingObjectEsc();

        if ($element_presc->_id) {
            $_object = $element_presc;
        } elseif (!$this->storeObject($_object, $element)) {
            return null;
        }

        return $_object;
    }

    /**
     * @param DOMElement $element XML element for the CEelementPrescription
     * @param CMbObject  $object  Object found
     *
     * @return CConstantesMedicales|null
     */
    private function importConstanteMedicale(DOMElement $element, ?CMbObject $object): ?CConstantesMedicales
    {
        /** @var CConstantesMedicales $_object */
        $_object = $this->getObjectFromElement($element, $object);

        if ($this->oxCabinet && in_array($_object->context_class, self::IGNORED_CLASSES_TAMM)) {
            return null;
        }

        [$context_class, $context_id] = explode('-', $_object->context_id);

        $constante                = new CConstantesMedicales();
        $constante->patient_id    = $_object->patient_id;
        $constante->context_id    = $context_id;
        $constante->context_class = $_object->context_class;
        $constante->creation_date = $_object->creation_date;
        $constante->datetime      = $_object->datetime;

        $constante->loadMatchingObjectEsc();

        if ($constante->_id) {
            $_object = $constante;
        } elseif (!$this->storeObject($_object, $element)) {
            return null;
        }

        return $_object;
    }

    /**
     * @param DOMElement $element XML element for the CCategoryPrescription
     * @param CMbObject  $object  Object found
     *
     * @return CCategoryPrescription|null
     */
    private function importCategoryPrescription(DOMElement $element, ?CMbObject $object): ?CCategoryPrescription
    {
        /** @var CCategoryPrescription $_object */
        $_object = $this->getObjectFromElement($element, $object);

        $cat              = new CCategoryPrescription();
        $cat->nom         = $_object->nom;
        $cat->group_id    = $_object->group_id ?: null;
        $cat->function_id = $_object->function_id ?: null;
        $cat->user_id     = $_object->user_id ?: null;

        $cat->loadMatchingObjectEsc();

        if ($cat->_id) {
            $_object = $cat;
        } elseif (!$this->storeObject($_object, $element)) {
            return null;
        }

        return $_object;
    }

    /**
     * @param DOMElement $element XML element for the CIdSante400
     * @param CMbObject  $object  Object found
     *
     * @return CIdSante400|null
     */
    private function importExternalId(DOMElement $element, ?CMbObject $object): ?CIdSante400
    {
        /** @var CIdSante400 $_object */
        $_object = $this->getObjectFromElement($element, $object);

        $ex_id = CIdSante400::getMatch($_object->object_class, $_object->tag, $_object->id400, $_object->object_id);

        if ($ex_id->_id) {
            $_object = $ex_id;
        } elseif (!$this->storeObject($_object, $element)) {
            return null;
        }

        return $_object;
    }

    private function importFileCategory(DOMElement $element, ?CMbObject $object): ?CFilesCategory
    {
        /** @var CFilesCategory $_object */
        $_object = $this->getObjectFromElement($element, $object);

        $cat        = new CFilesCategory();
        $cat->nom   = $_object->nom;
        $cat->class = $_object->class;

        // Search a global category
        $cat = $this->matchCategory($cat);

        if (!$cat->_id) {
            // Search cat in the current group
            $cat = $this->matchCategory($cat, CGroups::loadCurrent()->_id);
        }

        if ($cat->_id) {
            $_object = $cat;
        } else {
            $_object->group_id = CGroups::loadCurrent()->_id;

            if (!$this->storeObject($_object, $element)) {
                return null;
            }
        }

        return $_object;
    }

    private function matchCategory(CFilesCategory $cat, ?string $group_id = null): CFilesCategory
    {
        $ds = $cat->getDS();

        $where = [
            'group_id' => $group_id ? $ds->prepare('= ?', $group_id) : 'IS NULL',
            'nom'      => $ds->prepare('= ?', $cat->nom),
        ];

        if ($cat->class) {
            $where['class'] = $ds->prepare('= ?', $cat->class);
        }

        $cat->loadObject($where);

        return $cat;
    }

    /**
     * Count the valid directories for import int $directory
     *
     * @param string $directory Root dir to check directories
     *
     * @return int
     */
    public static function countValideDirs(string $directory): int
    {
        $iterator         = new DirectoryIterator($directory);
        $count_valid_dirs = 0;

        foreach ($iterator as $_fileinfo) {
            if ($_fileinfo->isDot()) {
                continue;
            }

            if ($_fileinfo->isFile()) {
                continue;
            }

            if ($_fileinfo->isDir()) {
                if (strpos($_fileinfo->getFilename(), "CPatient-") === 0) {
                    $count_valid_dirs++;
                }
            }
        }

        return $count_valid_dirs;
    }

    /**
     * @param string     $msg     Message to write
     * @param DOMElement $element DOM element tu get ID from
     * @param int        $type    Error type
     *
     * @return void
     */
    protected function writeLog(string $msg, ?DOMElement $element = null, int $type = UI_MSG_OK): void
    {
        if (isset($this->options['log_file']) && $this->options['log_file'] != '') {
            $msg = ($element != null) ? $element->getAttribute('id') . ' : ' . $msg : $msg;

            $exists = file_exists($this->options['log_file']);
            if (!$exists) {
                $exists = touch($this->options['log_file']);
            }

            if ($exists) {
                $msg = $this->patient_name . ' : ' . $msg;
                file_put_contents($this->options['log_file'], $msg . "\n", FILE_APPEND);
            } else {
                try {
                    CApp::log($msg);
                } catch (Exception $e) {
                    CAppUI::stepAjax($e->getMessage(), UI_MSG_WARNING);
                }
            }
        } else {
            CAppUI::stepAjax($msg, $type);
        }
    }

    /**
     * Getter fot nb_errors
     *
     * @return int
     */
    public function getErrorCount(): int
    {
        return $this->error_count;
    }

    /**
     * Return if a class is ignored or not
     *
     * @param string $class Classe name to check
     *
     * @return bool
     */
    protected function isIgnored(string $class): bool
    {
        return in_array($class, static::$_ignored_classes) || in_array($class, $this->_tmp_ignored_classes);
    }

    public function findSejour(
        ?string $patient_id,
        ?string $date,
        ?string $type,
        ?string $user_id = null,
        ?string $annule = '0',
        ?string $group_id = null
    ): ?CSejour {
        if (!$patient_id) {
            return null;
        }

        if (!$group_id) {
            $group_id = CGroups::loadCurrent()->_id;
        }

        // Recherche d'un séjour dont le debut est à la date passée en argument (sans le time)
        $date = CMbDT::date($date);

        $sejour = new CSejour();
        $ds     = $sejour->getDS();
        $where  = [
            "patient_id" => $ds->prepare("= ?", $patient_id),
            "annule"     => $ds->prepare("= ?", $annule),
            "DATE(`sejour`.`entree`) " . $ds->prepare("= ?", $date),
        ];

        if ($type) {
            $where['type'] = $ds->prepare("= ?", $type);
        }

        if ($user_id) {
            $where['praticien_id'] = $ds->prepare("= ?", $user_id);
        }

        if ($group_id) {
            $where['group_id'] = $ds->prepare("= ?", $group_id);
        }

        $sejour->loadObject($where);

        return $sejour;
    }
}
