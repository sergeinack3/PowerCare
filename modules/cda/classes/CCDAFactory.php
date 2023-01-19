<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda;

use Exception;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Documents\CCDADocumentCDA;
use Ox\Interop\Cda\Exception\CCDAException;
use Ox\Interop\Cda\Handle\CCDAHandle;
use Ox\Interop\Cda\Levels\Level1\CCDALevel1;
use Ox\Interop\Cda\Levels\Level3\CCDALevel3;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Eai\CItemReport;
use Ox\Interop\Eai\CMbOID;
use Ox\Interop\Eai\CReport;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Interop\InteropResources\valueset\CValueSet;
use Ox\Interop\InteropResources\valueset\CXDSValueSet;
use Ox\Interop\Xds\Factory\CXDSFactory;
use Ox\Interop\Xds\Factory\IXDSContext;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\SalleOp\CActeCCAM;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Permet de générer le CDA selon les champs générique
 */
abstract class CCDAFactory implements IXDSContext
{
    /** @var string */
    public const TYPE = '';

    /** @var string */
    public const TYPE_DOC = '';

    /** @var string */
    public const CODE_JDV = '';

    // LEVEL 1
    /** @var string */
    public const TYPE_DMP = 'DMP';

    /** @var string */
    public const TYPE_ANS_L1 = 'ANS-LVL1';

    /** @var string */
    public const TYPE_ZEPRA = 'ZEPRA';

    // LEVEL 3
    /** @var string */
    public const TYPE_VSM = 'VSM';

    /** @var string */
    public const TYPE_LDL_EES = 'LDL-EES';

    /** @var string */
    public const TYPE_LDL_SES = 'LDL-SES';

    /** @var string */
    public const TYPE_IJB_COMORBIDITE = 'IJB-COMORBIDITE';

    /** @var string */
    public const TYPE_CR_BIO = 'CR-BIO';

    /** @var string */
    public const OID_CIM1O = '2.16.840.1.113883.6.3';

    /** @var string */
    public const OID_CCAM = '1.2.250.1.213.2.5';

    /** @var string */
    public const OID_ATC = '2.16.840.1.113883.6.73';

    /** @var string */
    public const TYPE_VACCINATION_NOTE = 'VAC-NOTE';

    /** @var int */
    public const LEVEL = null;

    // todo déplacer ces constantes
    public const NONE_ALLERGY          = 'NoneAllergy';
    public const NONE_TREATMENT        = 'NoneTreatment';
    public const NONE_PATHOLOGY        = 'NonePathology';
    public const STATUS_DOCUMENT       = 'statutDoc';
    public const TA_ASIP               = 'TA_ASIP';
    public const MODALITE_ENTREE       = 'modaliteE';
    public const MODALITE_SORTIE       = 'modaliteS';
    public const SYNTHESE              = 'synthese';
    public const RECHERCHE_MICRO_MULTI = 'RechercheMicroMulti';
    public const TRANSFU               = 'transfu';
    public const ADMI_SANG             = 'admiSang';

    /** @var string[]  */
    protected const TARGET_CLASSES_LEVEL_1 =  [CCompteRendu::class, CFile::class];
    /** @var string[] */
    protected const TARGET_CLASSES_LEVEL_3 = [CSejour::class, CConsultation::class, CConsultAnesth::class, COperation::class];

    /** @var string[] */ // todo move in structureBody ?
    public static $mapping_mode_entree_jdv = [
        '8' => 'ORG-068',
        '7' => 'ORG-069',
        'O' => 'ORG-069',
        '6' => 'GEN-092',
        'N' => 'GEN-092',
    ];

    /** @var string[] */ // todo move in structureBody ?
    public static $mapping_mode_sortie_jdv = [
        'normal'         => 'ORG-101',
        'transfert'      => 'ORG-073',
        'transfert_acte' => 'ORG-073',
        'mutation'       => 'GEN-092',
        'deces'          => 'GEN-092',
    ];

    /** @var String */
    public $root;
    /** @var CFile|CCompteRendu|COperation|CConsultAnesth|CConsultation|CSejour */
    public $mbObject;
    /** @var COperation|CConsultAnesth|CConsultation|CSejour */
    public $targetObject;
    /** @var CPatient */
    public $patient;
    /** @var CUser|CMediusers */
    public $practicien;
    /** @var CUser|CMediusers */
    public $author;
    /** @var CCDADomDocument */
    public $dom_cda;
    /** @var  CInteropReceiver */
    public $receiver;

    public $version;
    public $mediaType;
    public $nom;
    public $id_cda;
    public $id_cda_lot;
    public $realm_code;
    public $langage;
    public $confidentialite;
    public $date_creation;
    public $code;
    public $date_author;
    public $industry_code;
    public $healt_care;
    public $service_event = [];
    public $templateId    = [];
    public $old_version;

    /** @var string|null associate cda file to this categroy */
    public $file_category_id;

    public $old_id;
    /** @var CGroups */
    public $group;
    /** @var CReport */
    public $report;

    public $_structure_cda = [];

    /** @var CXDSValueSet|CANSValueSet */
    public  $valueset_factory;

    /**
     * Création de la classe en fonction de l'objet passé
     *
     * @param string|null $type
     * @param CMbObject   $mbObject objet mediboard
     *
     * @return CCDAFactory
     * @throws Exception
     */
    public final static function factory(?string $type, CMbObject $mbObject): CCDAFactory
    {
        $classes       = self::findClasses();
        $object_class  = get_class($mbObject);
        $factory_class = CMbArray::get($classes, $type);

        if (in_array($object_class, self::TARGET_CLASSES_LEVEL_1)) { // level 1
            if (!$factory_class) {
                $factory_class = CCDALevel1::class;
            }

            // class should be extend from CCDALevel1
            if ($factory_class !== CCDALevel1::class && !is_subclass_of($factory_class, CCDALevel1::class)) {
                throw CCDAException::invalidCoherenceFactoryParameters();
            }
        } elseif (in_array($object_class, self::TARGET_CLASSES_LEVEL_3)) { // level 3
            if (!$factory_class) {
                $factory_class = CCDALevel3::class;
            }

            // class should be extend from CCDALevel3
            if ($factory_class !== CCDALevel3::class && !is_subclass_of($factory_class, CCDALevel3::class)) {
                throw CCDAException::invalidCoherenceFactoryParameters();
            }
        } else { // not managed
            throw CCDAException::invalidFactoryType();
        }

        return new $factory_class($mbObject);
    }

    /**
     * Find all classes which extend cda factory
     * @return string[]
     * @throws Exception
     */
    private static function findClasses(): array
    {
        $cache = new Cache('cda_factory', 'cda_class', Cache::INNER);

        if (!$classes = $cache->get()) {
            $classes = [];
            /** @var CCDAFactory $child */
            foreach (CClassMap::getInstance()->getClassChildren(CCDAFactory::class, false, true) as $child) {
                $classes[$child::TYPE] = $child;
            }
        }

        return $classes;
    }

    /**
     * Find CDA level 3 class
     *
     * @return string|null
     *
     * @throws Exception
     */
    public static function get(string $type_doc): ?string
    {
        $classes = self::findClasses();
        foreach ($classes as $_class) {
            if ($_class::TYPE_DOC === $type_doc) {
                return $_class;
            }
        }

        return null;
    }

    /**
     * @param CMbObject $mbObject Object
     *
     * @see parent::__construct
     *
     */
    public function __construct(CMbObject $mbObject)
    {
        $this->mbObject = $mbObject;
        $this->report   = new CReport('Report CDA');
    }

    /**
     * Création de templateId
     *
     * @param String $root      String
     * @param String $extension null
     *
     * @return CCDAII
     */
    public function createTemplateID($root, $extension = null)
    {
        $ii = new CCDAII();
        $ii->setRoot($root);
        $ii->setExtension($extension);

        return $ii;
    }

    /**
     * Generation du CDA
     *
     * @return string
     * @throws CMbException
     */
    public function generateContentCDA(): string
    {
        $this->extractData();
        $document_cda  = $this->getDocumentCDA();
        $this->dom_cda = $document_cda->generateDocument();

        return $this->dom_cda->saveXML($this->dom_cda);
    }

    /**
     * @return CCDADocumentCDA
     */
    public function getDocumentCDA(): CCDADocumentCDA
    {
        return new CCDADocumentCDA($this);
    }

    /**
     * @return CCDAHandle|null
     */
    public function getHandle(): ?CCDAHandle
    {
        return null;
    }

    /**
     * @param string|null $content_cda
     *
     * @return CFile
     * @throws CMbException
     */
    public function generateFileCDA(?string $content_cda = null): CFile
    {
        // retrieve or generate content cda
        if (!$content_cda) {
            $content_cda = $this->dom_cda ? $this->dom_cda->saveXML($this->dom_cda) : $this->generateContentCDA();
        }

        // fill file
        return $this->getFile($content_cda);
    }

    /**
     * @param string $content_cda
     * @param int    $file_category_id
     *
     * @return CFile
     */
    protected function getFile(string $content_cda): CFile
    {
        $file = new CFile();
        $file->setObject($this->targetObject);
        $file->file_type        = "application/xml";
        $file->type_doc_dmp     = $this::TYPE_DOC ?: null;
        $file->file_category_id = $this->file_category_id;
        $file->setContent($content_cda);
        $file->doc_size = strlen($content_cda);

        return $file;
    }

    /**
     * @see parent::extractData
     */
    public function extractData()
    {
        // OID Instance
        if (!CAppUI::conf('mb_oid')) {
            $this->report->addData(
                CAppUI::tr('CCDAFactory-msg-None OID for instance'),
                CItemReport::SEVERITY_ERROR
            );
        }

        /** @var CDocumentItem|CCodable $object */
        $object = $this->mbObject;
        $this->targetObject = $target_object = $this->determineTarget();

        $this->realm_code       = "FR";
        $this->valueset_factory = $this->getFactoryValueSet();
        $this->root             = CMbOID::getOIDFromClass($object, $this->receiver);

        // load practicien
        $this->practicien = $target_object->loadRefPraticien();
        $this->practicien->loadRefFunction();
        $this->practicien->loadRefOtherSpec();

        // load author
        $this->author = $this->determineAuthor();
        $this->author->loadRefFunction();
        $this->author->loadRefOtherSpec();

        // load Patient
        $this->patient = $target_object->loadRefPatient();
        $this->patient->loadLastINS();
        $this->patient->loadIPP();

        // Création du dossier médical du patient => nécessaire pour CDA structuré
        CDossierMedical::dossierMedicalId($this->patient->_id, $this->patient->_class);

        // find group from context
        $this->group = $this->findGroup();

        // set health care
        // Idex positionné sur l'établissement dans le module CDA
        if (!CIdSante400::getValueFor($this->group, "cda_association_code")) {
            $this->report->addData(
                CAppUI::tr('CGroups-msg-None association CDA'),
                CItemReport::SEVERITY_ERROR
            );
        } else {
            $this->healt_care = $this->prepareHealthCare();
        }

        // set Nom
        $this->nom = $this->prepareNom();

        // set Id cda
        $this->id_cda = $this->prepareIdCDA();

        // set confidentialite
        $this->confidentialite = $this->valueset_factory::getConfidentialityCode($this->getConfidentiality());

        // set code
        $this->code = $this->prepareCode();

        //conformité HL7
        $this->templateId[] = $this->createTemplateID("2.16.840.1.113883.2.8.2.1", "HL7 France");

        // set industry code
        $this->industry_code = $this->prepareIndustryCode();

        // set service event
        $this->service_event = $this->prepareServiceEvent();

        if ($this->old_version) {
            $oid               = CMbOID::getOIDFromClass($object, $this->receiver);
            $this->old_version = "$oid.$this->old_id.$this->old_version";
        }
    }

    /**
     * @return COperation|CSejour|CConsultation
     */
    abstract protected function determineTarget(): CStoredObject;

    /**
     * @return CMediusers
     */
    abstract protected function determineAuthor(): CMediusers;

    /**
     * @return CValueSet
     */
    protected function getFactoryValueSet(): CValueSet
    {
        return new CXDSValueSet();
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function prepareHealthCare(): array
    {
        return $this->valueset_factory::getHealthcareFacilityTypeCode($this->group);
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    protected function prepareIndustryCode(): array
    {
        return $this->valueset_factory::getPracticeSettingCode();
    }

    /**
     * @return CGroups
     * @throws Exception
     */
    protected function findGroup(): CGroups
    {
        $object = $this->targetObject;
        if ($object instanceof CSejour) {
            return $object->loadRefEtablissement();
        } elseif ($object instanceof COperation) {
            return $object->loadRefSejour()->loadRefEtablissement();
        }

        $group = new CGroups();

        return $group->load($this->practicien->_group_id);
    }

    /**
     * @return string
     */
    abstract protected function getConfidentiality(): string;

    /**
     * @return array
     * @throws Exception
     */
    abstract protected function prepareCode(): array;

    /**
     * @return string
     */
    abstract protected function prepareNom(): string;

    /**
     * @return string
     */
    protected function prepareIdCDA(): string
    {
        $object = $this->mbObject;

        $this->id_cda_lot = "$this->root.$object->_id";

        return "$this->id_cda_lot.$this->version";
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    protected function prepareServiceEvent(): array
    {
        $service       = ["nullflavor" => null];
        $target_object = $this->targetObject;

        switch (get_class($target_object)) {
            case CSejour::class:
                $no_acte = 0;

                /** @var CSejour $target_object CSejour */
                $service["time_start"] = $target_object->entree;
                $service["time_stop"]  = $target_object->sortie;
                $service["executant"]  = $target_object->loadRefPraticien();

                // Prio 1 => on prend le DP
                if ($target_object->DP) {
                    $service["oid"]       = "2.16.840.1.113883.6.3";
                    $service["code"]      = $target_object->DP;
                    $service["type_code"] = "cim10";
                    $no_acte++;
                }

                // Prio 2 => on prend l'acte CCAM
                $acte_ccam = $acte_ccam_activite_1 = $acte_ccam_activite_4 = null;
                foreach ($target_object->loadRefsActesCCAM() as $_acte_ccam) {
                    if (($_acte_ccam->code_activite != "4" && $_acte_ccam->code_activite != "1")
                        || ($acte_ccam_activite_4 && $acte_ccam_activite_1)
                    ) {
                        continue;
                    }

                    // On prend le premier code CCAM Activité 4
                    if ($_acte_ccam->code_activite == '4' && !$acte_ccam_activite_4) {
                        $acte_ccam_activite_4 = $_acte_ccam;

                        continue;
                    }

                    // On prend le premier code CCAM Activité 1
                    if ($_acte_ccam->code_activite == '1' && !$acte_ccam_activite_1) {
                        $acte_ccam_activite_1 = $_acte_ccam;
                    }
                }

                // Prio Activité 4
                if (!$acte_ccam && $acte_ccam_activite_4) {
                    $acte_ccam = $acte_ccam_activite_4;
                }

                // Et ensuite prio Activité 1 si pas Activité 4
                if (!$acte_ccam && $acte_ccam_activite_1) {
                    $acte_ccam = $acte_ccam_activite_1;
                }

                if ($no_acte === 0 && $acte_ccam) {
                    $service["time_start"] = $acte_ccam->execution;
                    $service["time_stop"]  = "";
                    $service["code"]       = $acte_ccam->code_acte;
                    $service["oid"]        = CCDAFactory::OID_CCAM;
                    $acte_ccam->loadRefExecutant();
                    $service["executant"] = $acte_ccam->_ref_executant;
                    $service["type_code"] = "ccam";
                    $no_acte++;
                }
                // Prio 3 => Acte CIM10
                if ($no_acte === 0) {
                    $patient = $this->patient;
                    $first_code_cim_10 = $patient->getFirstCodeCIM10();
                    if ($first_code_cim_10) {
                        $service["code"]       = $first_code_cim_10;
                        $service["oid"]        = CCDAFactory::OID_CIM1O;
                        $service["executant"] = $this->practicien;
                        $service["type_code"] = "cim10";
                        $no_acte++;
                    }
                }

                // Prio 4 => on prend le premier code ATC de la prescription
                // TODO : Commenter car pas géré en XDS pour la cohérence des métadatas
                /*$code_atc = $this->getFirstCodeATC($target_object);
                if ($no_acte === 0 && $code_atc) {
                    $service["code"]       = $code_atc;
                    $service["oid"]        = CCDAFactory::OID_ATC;
                    $service["executant"]  = $this->practicien;
                    $service["type_code"]  = "atc";
                    $no_acte++;
                }*/

                if ($no_acte === 0) {
                    $service["nullflavor"] = "UNK";
                }
                break;
            case COperation::class:
                /** @var COperation $target_object COperation */
                $no_acte = 0;
                $acte_ccam = $acte_ccam_activite_1 = $acte_ccam_activite_4 = null;

                $target_object->loadExtCodesCCAM();
                foreach ($target_object->_ext_codes_ccam as $_dated_code_ccam) {
                    $_acte_ccam = new CActeCCAM();
                    $_acte_ccam->code_acte = $_dated_code_ccam->code;
                    $_acte_ccam->loadMatchingObject();

                    if (!$_acte_ccam->_id) {
                        continue;
                    }

                    if (($_acte_ccam->code_activite != "4" && $_acte_ccam->code_activite != "1")
                        || ($acte_ccam_activite_4 && $acte_ccam_activite_1)
                    ) {
                        continue;
                    }

                    // On prend le premier code CCAM Activité 4
                    if ($_acte_ccam->code_activite == '4' && !$acte_ccam_activite_4) {
                        $acte_ccam_activite_4 = $_acte_ccam;

                        continue;
                    }

                    // On prend le premier code CCAM Activité 1
                    if ($_acte_ccam->code_activite == '1' && !$acte_ccam_activite_1) {
                        $acte_ccam_activite_1 = $_acte_ccam;
                    }
                }

                // Prio Activité 4
                if (!$acte_ccam && $acte_ccam_activite_4) {
                    $acte_ccam = $acte_ccam_activite_4;
                }

                // Et ensuite prio Activité 1 si pas Activité 4
                if (!$acte_ccam && $acte_ccam_activite_1) {
                    $acte_ccam = $acte_ccam_activite_1;
                }

                if ($acte_ccam) {
                    $service["time_start"] = $acte_ccam->execution;
                    $service["time_stop"]  = "";
                    $service["code"]       = $acte_ccam->code_acte;
                    $service["oid"]        = CCDAFactory::OID_CCAM;
                    $acte_ccam->loadRefExecutant();
                    $service["executant"] = $acte_ccam->_ref_executant;
                    $service["type_code"] = "ccam";
                    $no_acte++;
                }

                if ($no_acte === 0) {
                    $service["time_start"] = $target_object->_datetime_best;
                    $service["time_stop"]  = $target_object->_datetime_reel_fin;
                    $service["executant"]  = $target_object->loadRefPraticien();
                    $service["nullflavor"] = "UNK";
                }
                break;
            case CConsultation::class:
                /** @var CConsultation $target_object CConsultation */
                $target_object->loadRefPlageConsult();

                $no_acte = 0;
                // We take first acte CCAM
                foreach ($target_object->loadRefsActesCCAM() as $_acte_ccam) {
                    if ($_acte_ccam->code_activite !== "1" || $no_acte >= 1) {
                        continue;
                    }

                    $service["time_start"] = $_acte_ccam->execution;
                    $service["time_stop"]  = "";
                    $service["code"]       = $_acte_ccam->code_acte;
                    $service["oid"]        = CCDAFactory::OID_CCAM;
                    $_acte_ccam->loadRefExecutant();
                    $service["executant"] = $_acte_ccam->_ref_executant;
                    $service["type_code"] = "ccam";
                    $no_acte++;
                }

                // If not acte CCAM, we take first CIM10 code
                if ($no_acte === 0) {
                    $patient = $this->patient;

                    $first_code_cim_10 = $patient->getFirstCodeCIM10();
                    if ($first_code_cim_10) {
                        $service["time_start"] = $target_object->_datetime;
                        $service["time_stop"]  = "";
                        $service["code"]       = $first_code_cim_10;
                        $service["oid"]        = CCDAFactory::OID_CIM1O;
                        $service["executant"]  = $this->practicien;
                        $service["type_code"]  = "cim10";
                        $no_acte++;
                    }
                }

                // If not code CIM10, we take ATC
                // TODO : Commenter car pas géré en XDS pour la cohérence des métadatas
                /*if ($no_acte === 0) {

                    $code_atc = $this->getFirstCodeATC($target_object);
                    if ($code_atc) {
                        $service["time_start"] = $target_object->_datetime;
                        $service["time_stop"]  = "";
                        $service["code"]       = $code_atc;
                        $service["oid"]        = CCDAFactory::OID_ATC;
                        $service["executant"]  = $this->practicien;
                        $service["type_code"]  = "atc";
                        $no_acte++;
                    }
                }*/

                if ($no_acte === 0) {
                    $service["time_start"] = $target_object->_datetime;
                    $service["time_stop"]  = $target_object->_date_fin;
                    $service["executant"]  = $this->practicien;
                    $service["nullflavor"] = "UNK";
                }
                break;
            default:
        }

        return $service;
    }

    /**
     * @param CXDSFactory $xds
     */
    public function initializeXDS(CXDSFactory $xds): void
    {
        $xds->targetObject         = $this->targetObject;
        $xds->patient              = $this->patient;
        $xds->practicien           = $this->practicien;
        $xds->receiver             = $this->receiver;
        $xds->nom                  = $this->nom;
        $xds->date_creation        = $this->date_creation;
        $xds->langage              = $this->langage;
        $xds->service_event        = $this->service_event;
        $xds->industry_code        = $this->industry_code;
        $xds->confidentiality      = $this->confidentialite;
        $xds->health_care_facility = $this->healt_care;
        $xds->code                 = $this->code;
        $xds->root                 = $this->id_cda;

        //En fonction d'un corps structuré
        $xds->entry_media_type = $xds->valueset_factory::getFormatCode(
            $this->mediaType,
            'urn:ihe:iti:xds-sd:text:2008'
        ); // todo remove

        $xds->class = $xds->valueset_factory::getClassCode(CMbArray::get($xds->code, "code"), $this::TYPE);
    }

    /**
     * Get first code ATC
     *
     * @return string|null
     */
    public function getFirstCodeATC(CMbObject $object): ?string
    {
        if (!$object instanceof CConsultation && !$object instanceof CSejour) {
            return null;
        }

        $object->loadRefsPrescriptions();
        if ($object instanceof CConsultation && !isset($object->_ref_prescriptions['externe'])) {
            return null;
        }

        if ($object instanceof CSejour && !isset($object->_ref_prescriptions['sejour'])) {
            return null;
        }

        $prescription = $object instanceof CSejour ? $object->_ref_prescriptions['sejour'] : $object->_ref_prescriptions['externe'];

        $prescription_line_medicaments = $prescription->loadRefsLinesMed();
        /** @var CPrescriptionLineMedicament $prescription_line_medicament */
        $prescription_line_medicament = reset($prescription_line_medicaments);
        if (!$prescription_line_medicament || !$prescription_line_medicament->_id) {
            return null;
        }

        return $prescription_line_medicament->atc;
    }
}
