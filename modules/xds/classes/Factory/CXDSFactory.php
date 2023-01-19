<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Factory;

use DateTime;
use DateTimeZone;
use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbSecurity;
use Ox\Core\CMbString;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Eai\CItemReport;
use Ox\Interop\Eai\CMbOID;
use Ox\Interop\Eai\CReport;
use Ox\Interop\InteropResources\valueset\CValueSet;
use Ox\Interop\InteropResources\valueset\CXDSValueSet;
use Ox\Interop\Xds\CXDSQueryRegistryStoredQuery;
use Ox\Interop\Xds\CXDSTools;
use Ox\Interop\Xds\CXDSXmlDocument;
use Ox\Interop\Xds\Exception\CXDSException;
use Ox\Interop\Xds\Structure\CXDSAssociation;
use Ox\Interop\Xds\Structure\CXDSClass;
use Ox\Interop\Xds\Structure\CXDSConfidentiality;
use Ox\Interop\Xds\Structure\CXDSContentType;
use Ox\Interop\Xds\Structure\CXDSDocumentEntryAuthor;
use Ox\Interop\Xds\Structure\CXDSEventCodeList;
use Ox\Interop\Xds\Structure\CXDSExtrinsicObject;
use Ox\Interop\Xds\Structure\CXDSFormat;
use Ox\Interop\Xds\Structure\CXDSHasMemberAssociation;
use Ox\Interop\Xds\Structure\CXDSHealthcareFacilityType;
use Ox\Interop\Xds\Structure\CXDSPracticeSetting;
use Ox\Interop\Xds\Structure\CXDSRegistryObjectList;
use Ox\Interop\Xds\Structure\CXDSRegistryPackage;
use Ox\Interop\Xds\Structure\CXDSType;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Ccam\CDatedCodeCCAM;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CSubmissionLot;
use Ox\Mediboard\Files\CSubmissionLotToDocument;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPaysInsee;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\CPreferences;

/**
 * Permet de générer le XDs en fonction des champs remplis
 */
class CXDSFactory implements IShortNameAutoloadable
{
    /** @var string */
    public const TYPE = self::TYPE_XDS;

    /** @var string */
    public const TYPE_XDS = 'XDS';

    /** @var string */
    public const TYPE_ANS = 'ANS';

    /** @var string */
    public const TYPE_DMP = 'DMP';

    /** @var string */
    public const TYPE_ZEPRA = 'ZEPRA';

    /** @var string */
    public const HIDE_PRACTICIEN = '0';

    /** @var string */
    public const HIDE_PATIENT = '1';

    /** @var string */
    public const HIDE_REPRESENTANT = '2';

    // context
    /** @var IXDSContext */
    public $context_object;
    /** @var CMbObject */
    public $targetObject;
    /** @var CUser|CMediusers */
    public $practicien;
    /** @var CGroups */
    public $group;
    /** @var CPatient */
    public $patient;
    /** @var CInteropReceiver */
    public $receiver;
    /** @var CReport */
    public $report;
    /** @var string */
    public $nom;


    /** @var array */
    public $entry_media_type = [];
    /** @var array */
    public $class;
    public $code;
    public $root;
    public $service_event;
    public $industry_code;
    public $confidentiality;
    public $patient_identifier;
    public $ins_patient;
    public $hide;
    public $name_submission;
    public $id_classification;
    public $id_external;
    public $xcn_mediuser;
    public $xon_etablissement;
    public $specialty;
    public $practice_setting;
    public $health_care_facility;
    public $hash;
    public $repository;
    public $doc_uuid;
    public $id_submission;
    public $uri;
    public $uuid          = [];
    public $oid           = [];
    public $document;
    public $docItem;
    public $name_document = [];
    public $size;
    public $date_creation;
    public $langage;
    /** @var bool */
    public $repositoryUniqueId = false;
    public $author_role;

    /** @var CValueSet */
    public $valueset_factory;

    /**
     * Création de la classe en fonction de l'objet passé
     *
     * @param CMbObject|CCDAFactory $mbObject objet mediboard
     *
     * @return CXDSFactory
     * @throws Exception
     */
    public static function factory(?string $type, $mbObject, ?CDocumentItem $docItem = null)
    {
        if (!$type) {
            return new self($mbObject, $docItem);
        }

        $classes = self::findClasses();
        if (!$class = CMbArray::get($classes, $type)) {
            throw CXDSException::invalidFactoryType();
        }

        return new $class($mbObject, $docItem);
    }

    /**
     * @param IXDSContext $context_object
     */
    public function setContextObject(IXDSContext $context_object): void
    {
        $this->context_object = $context_object;
    }

    /**
     * Find all classes which extend xds factory
     * @return array
     * @throws Exception
     */
    public static function findClasses(): array
    {
        $cache = new Cache('xds_factory', 'xds_class', Cache::INNER_OUTER);

        if (!$classes = $cache->get()) {
            $classes = [];
            /** @var CXDSFactory $child */
            foreach (CClassMap::getInstance()->getClassChildren(CXDSFactory::class, false, true) as $child) {
                $classes[$child::TYPE] = $child;
            }
            $classes[self::TYPE] = self::class;
        }

        return $classes;
    }

    /**
     * Constructeur
     *
     * @param CMbObject|IXDSContext $mbObject mediboard object
     */
    public function __construct($mbObject, ?CDocumentItem $docItem)
    {
        $this->document = $mbObject;
        $this->docItem  = $docItem;
        $this->report   = new CReport('Report XDS');
    }

    /**
     * Extrait les données de l'objet nécessaire au XDS
     *
     * @return void
     * @throws Exception
     */
    public function extractData()
    {
        // initialize value set
        $this->valueset_factory = $this->getValueSet();

        // initialize context
        $this->initContext();

        if (!$content = $this->document->getContent() ?: $this->document->getBinaryContent()) {
            $this->report->addData(
                'CXDSFactory-msg-error impossible to retrieve content of document',
                CItemReport::SEVERITY_ERROR
            );
        }

        $this->size = $this->document->doc_size ?: strlen($content);
        $this->hash = sha1($content);

        $mediuser                = CMediusers::get();
        $specialty               = $mediuser->loadRefOtherSpec();
        $this->id_classification = 0;
        $this->id_external       = 0;

        // find group
        $this->group = $this->findGroup();

        $identifiant              = $this->getIdEtablissement(true, $this->group) . "/$mediuser->_id";
        $this->specialty          = $specialty->code . "^" . $specialty->libelle . "^" . $specialty->oid;
        $this->author_role        = $this->getAuthorRole($this->patient, $this->targetObject);
        $this->xcn_mediuser       = CXDSTools::getXCNMediuser(
            $identifiant,
            $mediuser->_p_last_name,
            $mediuser->_p_first_name
        );
        $this->xon_etablissement  = $this->getXONetablissement(
            $this->group->text,
            $this->getIdEtablissement(false, $this->group)
        );
        $this->patient_identifier = $this->getID($this->patient, $this->receiver);
        $uuid                     = CMbSecurity::generateUUID();

        $this->uuid["registry"]  = $uuid . "1";
        $this->uuid["extrinsic"] = $uuid . "2";
        $this->uuid["signature"] = $uuid . "3";

        $this->nom = $this->prepareNom();
    }

    /**
     * @return string
     */
    protected function prepareNom(): string
    {
        if ($this->nom) {
            return $this->nom;
        }

        $docItem = $this->docItem;
        if (!$docItem) {
            $this->report->addData(CAppUI::tr("CXDSFactory-msg-missing name of document"), CItemReport::SEVERITY_ERROR);
        }

        if ($docItem instanceof CCompteRendu) {
            return $docItem->nom;
        }

        /** @var CFile $docItem */
        if (isset($docItem->_file_name_cda) && $docItem->_file_name_cda) {
            return $docItem->_file_name_cda;
        }

        return basename($docItem->file_name);
    }

    /**
     * @return CValueSet
     */
    protected function getValueSet(): CValueSet
    {
        return new CXDSValueSet();
    }

    /**
     * @return CGroups
     * @throws Exception
     */
    protected function findGroup(): CGroups
    {
        // Pour un séjour : On prend l'établissement du séjour et non l'étab de la fonction
        if ($this->targetObject instanceof CSejour) {
            return $this->targetObject->loadRefEtablissement();
        }

        if ($this->targetObject instanceof COperation) {
            return $this->targetObject->loadRefSejour()->loadRefEtablissement();
        }

        $mediuser = CMediusers::get();

        return $mediuser->loadRefFunction()->loadRefGroup();
    }

    /**
     * @throws Exception
     */
    protected function initContext(): void
    {
        // retrieve context from object
        if ($this->context_object) {
            $this->context_object->initializeXDS($this);

            return;
        }

        // find context
        /** @var CDocumentItem $object */
        $target_object = $object = $this->docItem;
        if ($object instanceof CDocumentItem) {
            $target_object = $object->loadTargetObject();
        }
        if ($target_object instanceof CConsultAnesth) {
            $target_object = $target_object->loadRefConsultation();
        }
        $this->targetObject = $target_object;

        // load practicien
        $this->practicien = $target_object->loadRefPraticien();
        $this->practicien->loadRefFunction();
        $this->practicien->loadRefOtherSpec();

        // load Patient
        $this->patient = $target_object->loadRefPatient();
        $this->patient->loadLastINS();
        $this->patient->loadIPP();

        // elements to defined
        $this->date_creation = null;
        $this->langage       = null;
    }

    /**
     * Génération de la requête XDS57 concernant le dépubliage et l'archivage
     *
     * @param string $uuid         Identifiant du document dans le registre
     * @param string $archivage    Type archivage
     * @param string $masquage     Type masquage
     * @param string $id_extrinsic ID extrinsic
     * @param array  $metadata     Metadata
     *
     * @return CXDSXmlDocument
     * @throws CMbException
     */
    public function generateXDS57($uuid, $archivage = null, $masquage = null, $id_extrinsic = null, $metadata = null)
    {
        $id_registry = $this->uuid["registry"];

        $class = new CXDSRegistryObjectList();

        //Ajout du lot de soumission
        $registry = $this->createRegistryPackage($id_registry);
        $class->appendRegistryPackage($registry);

        // Cas de l'archivage
        if ($archivage) {
            $NewStatus      = "";
            $OriginalStatus = "";
            switch ($archivage) {
                case "unpublished":
                    $NewStatus      = "urn:asip:ci-sis:2010:StatusType:Deleted";
                    $OriginalStatus = "urn:oasis:names:tc:ebxml-regrep:StatusType:Approved";
                    break;
                case "archived":
                    $NewStatus      = "urn:asip:ci-sis:2010:StatusType:Archived";
                    $OriginalStatus = "urn:oasis:names:tc:ebxml-regrep:StatusType:Approved";
                    break;
                case "unarchived":
                    $NewStatus      = "urn:oasis:names:tc:ebxml-regrep:StatusType:Approved";
                    $OriginalStatus = "urn:asip:ci-sis:2010:StatusType:Archived";
                    break;
                default:
            }

            $asso = new CXDSAssociation(
                "association01",
                $id_registry,
                $uuid,
                "urn:ihe:iti:2010:AssociationType:UpdateAvailabilityStatus"
            );
            $asso->setSlot("OriginalStatus", ["$OriginalStatus"]);
            $asso->setSlot("NewStatus", ["$NewStatus"]);
            $class->appendAssociation($asso);
        }

        // Cas du masquage
        if ($masquage !== null) {
            $version = CMbArray::get($metadata, "version");

            $asso = new CXDSAssociation("association01", $id_registry, $id_extrinsic);
            $asso->setSlot("SubmissionSetStatus", ["Original"]);
            $asso->setSlot("PreviousVersion", ["$version"]);
            $class->appendAssociation($asso);

            // Ajout d'un document
            //$extrinsic = $this->createExtrinsicObject($id_extrinsic, $uuid, true, $metadata);
            //$class->appendExtrinsicObject($extrinsic);
        }

        // Generate headers xds
        $xds_headers = $archivage ? $class->toXML() : $class->toXML($metadata, $id_extrinsic, $uuid, $masquage);

        // add headers to xds
        $xds = new CXDSXmlDocument();
        $xds->importDOMDocument($xds, $xds_headers);

        return $xds;
    }

    /**
     * Génère le corps XDS
     *
     * @return CXDSXmlDocument
     * @throws CMbException
     */
    public function generateXDS41(string $doc_content)
    {
        $id_registry = $this->uuid["registry"];
        $id_document = $this->uuid["extrinsic"];
        $doc_uuid    = $this->doc_uuid;

        // Ajout du lot de soumission
        $class = new CXDSRegistryObjectList();

        // Métadonnée du lot de soumission
        $registry = $this->createRegistryPackage($id_registry);
        $class->appendRegistryPackage($registry);

        // Ajout d'un document
        $extrinsic = $this->createExtrinsicObject($id_document);
        $class->appendExtrinsicObject($extrinsic);

        // Ajout des associations
        $asso1 = $this->createAssociation("association01", $id_registry, $id_document);
        $class->appendAssociation($asso1);

        // Si le document est déjà existant
        if ($doc_uuid) {
            $asso4 = $this->createAssociation("association02", $id_document, $doc_uuid, true, true);
            $class->appendAssociation($asso4);
        }

        // Création dans mediboard du lot de soumission
        $cxds_submissionlot_document                   = new CSubmissionLotToDocument();
        $cxds_submissionlot_document->submissionlot_id = $this->id_submission;
        $cxds_submissionlot_document->setObject($this->docItem);
        if ($msg = $cxds_submissionlot_document->store()) {
            throw new CMbException($msg);
        }

        // generate headers xds
        $header_xds = $class->toXML();

        // add headers to xds
        $xds     = new CXDSXmlDocument();
        $message = $xds->createDocumentRepositoryElement($xds, "ProvideAndRegisterDocumentSetRequest");
        $xds->importDOMDocument($message, $header_xds);

        // Add document to xds
        $document = $xds->createDocumentRepositoryElement($message, "Document");
        $xds->addAttribute($document, "id", $id_document);
        $document->nodeValue = base64_encode($doc_content);

        return $xds;
    }

    /**
     * Génère le corps XDM
     *
     * @return CXDSXmlDocument
     * @throws CMbException
     */
    public function generateXDS32(string $status)
    {
        $id_registry = $this->uuid["registry"];
        $id_document = $this->uuid["extrinsic"];
        $doc_uuid    = $this->doc_uuid;

        // Ajout du lot de soumission
        $class = new CXDSRegistryObjectList();

        // Métadonnée du lot de soumission
        $registry = $this->createRegistryPackage($id_registry);
        $class->appendRegistryPackage($registry);

        // Ajout d'un document
        $extrinsic = $this->createExtrinsicObject($id_document, null, true, null, $status);
        $class->appendExtrinsicObject($extrinsic);

        // Ajout des associations
        $asso1 = $this->createAssociation("association01", $id_registry, $id_document);
        $class->appendAssociation($asso1);

        // Si le document est déjà existant
        if ($doc_uuid) {
            $asso4 = $this->createAssociation("association02", $id_document, $doc_uuid, true, true);
            $class->appendAssociation($asso4);
        }

        // Création dans mediboard du lot de soumission
        $cxds_submissionlot_document                   = new CSubmissionLotToDocument();
        $cxds_submissionlot_document->submissionlot_id = $this->id_submission;
        $cxds_submissionlot_document->setObject($this->docItem);
        if ($msg = $cxds_submissionlot_document->store()) {
            throw new CMbException($msg);
        }

        // Generate headers
        $xds_headers = $class->toXML();

        // add headers on xds document
        $xds = new CXDSXmlDocument();
        $xds->importDOMDocument($xds, $xds_headers);

        return $xds;
    }

    /**
     * @param string $repository_id
     * @param string $oid
     *
     * @return CXDSXmlDocument
     */
    public function generateXDS43(string $repository_id, string $oid): CXDSXmlDocument
    {
        $xml = new CXDSXmlDocument();
        $root             = $xml->createDocumentRepositoryElement($xml, "RetrieveDocumentSetRequest");
        $document_request = $xml->createDocumentRepositoryElement($root, "DocumentRequest");

        $xml->createDocumentRepositoryElement($document_request, "RepositoryUniqueId", $repository_id);
        $xml->createDocumentRepositoryElement($document_request, "DocumentUniqueId"  , $oid);

        return $xml;
    }

    /**
     * @param CXDSQueryRegistryStoredQuery $query
     *
     * @return CXDSXmlDocument
     */
    public function generateXDS18(CXDSQueryRegistryStoredQuery $query): CXDSXmlDocument
    {
        return $query->createQuery();
    }

    /**
     * Garde en mémoire le nom des documents
     *
     * @param String $name Nom du document
     *
     * @return void
     */
    function appendNameDocument($name)
    {
        array_push($this->name_document, $name);
    }

    /**
     * Retourne l'INS présent dans le CDA
     *
     * @param CPatient $patient Patient
     *
     * @return string
     */
    protected function getIns($patient)
    {
        $ins = null;
        //@todo: faire l'INSA
        $last_ins = $patient->_ref_last_ins;
        if ($last_ins) {
            $ins = $last_ins->ins;
        }
        $comp5 = "INS-C";
        $comp4 = "1.2.250.1.213.1.4.2";
        $comp4 = "&$comp4&ISO";
        $comp1 = $ins;

        $result = "$comp1^^^$comp4^$comp5";

        return $result;
    }

    /**
     * Retourne le NIR présent dans le CDA
     *
     * @param CPatient $patient Patient
     *
     * @return string
     */
    protected function getNIR($patient)
    {
        $comp5 = "NH";
        $comp4 = CAppUI::conf("dmp NIR_OID");
        $comp4 = "&$comp4&ISO";
        $comp1 = $patient->matricule;

        $result = "$comp1^^^$comp4^$comp5";

        return $result;
    }

    /**
     * Retourne le NIR présent dans le CDA
     *
     * @param CPatient $patient
     *
     * @return string
     * @throws \Exception
     */
    protected function getINSNIR(CPatient $patient)
    {
        $comp5 = "NH";
        $comp4 = CAppUI::conf("dmp NIR_OID");
        $comp4 = "&$comp4&ISO";
        $comp1 = $patient->getINSNIR();

        $result = "$comp1^^^$comp4^$comp5";

        return $result;
    }

    /**
     * Incrémente l'identifiant des classifications
     *
     * @return void
     */
    function setClaId()
    {
        $this->id_classification++;
    }

    /**
     * Incrémente l'identifiant des externals
     *
     * @return void
     */
    function setEiId()
    {
        $this->id_external++;
    }

    /**
     * Création du lot de soumission
     *
     * @param String $id Identifiant du lot de soumission
     *
     * @return CXDSRegistryPackage
     * @throws CMbException
     */
    function createRegistryPackage($id)
    {
        $cla_id                = &$this->id_classification;
        $ei_id                 = &$this->id_external;
        $specialty             = $this->specialty;
        $object                = $this->targetObject;
        $valueset_factory      = $this->valueset_factory;
        $praticien             = $this->practicien;
        $receiver              = $this->receiver;
        $this->name_submission = $id;

        $registry = new CXDSRegistryPackage($id);

        //date de soumission
        $registry->setSubmissionTime([CXDSTools::getTimeUtc()]);

        //title
        $registry->setTitle($this->nom);

        //PS qui envoie le document
        $document = new CXDSDocumentEntryAuthor("cla$cla_id", $id, true);
        $this->setClaId();

        // Pour un séjour : On prend l'établissement du séjour et non l'étab de la fonction
        if ($this->targetObject instanceof CSejour) {
            $author_organization = $this->targetObject->loadRefEtablissement();
        } elseif ($this->targetObject instanceof COperation) {
            $author_organization = $this->targetObject->loadRefSejour()->loadRefEtablissement();
        } else {
            $author_organization = $praticien->loadRefFunction()->loadRefGroup();
        }

        // On prend le praticien de la consult/du séjour (sas envoi)
        $preferences = CPreferences::getAllPrefs($praticien->_id);
        if ($author_organization && $author_organization->_id &&
            (CMbArray::get($preferences, 'authentification_directe') ==  '2')
            && CAppUI::gconf("dmp general authentification_indirecte", $author_organization->_id)
        ) {
            $author = $this->getPersonEtab($praticien, $author_organization);
        } else {
            $author = $this->getPerson($praticien);
        }

        $document->setAuthorPerson([$author]);

        $spec = $praticien->loadRefOtherSpec();
        if ($spec->libelle) {
            $document->setAuthorSpecialty(["$spec->code^$spec->libelle^$spec->oid"]);
        } else {
            $document->setAuthorSpecialty([$specialty]);
        }

        if ($author_organization->_id) {
            $institution = $this->getXONetablissement(
                $author_organization->text,
                $this->getIdEtablissement(false, $author_organization)
            );
            $document->setAuthorInstitution([$institution]);
        } else {
            //Institution qui envoie le document
            $document->setAuthorInstitution([$this->xon_etablissement]);
        }

        $registry->appendDocumentEntryAuthor($document);

        $entry = $valueset_factory::getContentTypeCode($object);

        $content = new CXDSContentType("cla$cla_id", $id, $entry["code"]);
        $this->setClaId();
        $content->setCodingScheme([$entry["codeSystem"]]);
        $content->setContentTypeCodeDisplayName($entry["displayName"]);
        $registry->setContentType($content);

        //spécification d'un SubmissionSet ou d'un folder, ici submissionSet
        $registry->setSubmissionSet("cla$cla_id", $id, false);
        $this->setClaId();

        //patient du document
        $registry = $this->preparePatientDocument($registry);
        $this->setEiId();

        //OID de l'instance serveur
        $only_oid_root = !$this instanceof CXDSANS;
        $oid_instance  = CMbOID::getOIDOfInstance($registry, $receiver, $only_oid_root);
        $registry->setSourceId("ei$ei_id", $id, $oid_instance);
        $this->setEiId();

        //OID unique
        $oid                      = CMbOID::getOIDFromClass($registry, $receiver);
        $cxds_submissionlot       = new CSubmissionLot();
        $cxds_submissionlot->date = "now";
        $cxds_submissionlot->type = $this::getTypeSubmissionLot();
        if ($msg = $cxds_submissionlot->store()) {
            throw new CMbException($msg);
        }

        $this->id_submission = $cxds_submissionlot->_id;
        $this->oid["lot"]    = "$oid.$cxds_submissionlot->_id";
        $registry->setUniqueId("ei$ei_id", $id, $this->oid["lot"]);
        $this->setEiId();

        return $registry;
    }

    /**
     * @return string
     */
    protected function getTypeSubmissionLot(): string
    {
        return $this::TYPE;
    }

    /**
     * Retourne l'identifiant de l'établissement courant
     *
     * @param boolean $forPerson Identifiant concernant une personne
     * @param CGroups $group     etablissement
     *
     * @return null|string
     */
    protected function getIdEtablissement($forPerson = false, $group = null): ?string
    {
        return null;
    }

    /**
     * Get author role
     *
     * @return string|null
     */
    protected function getAuthorRole(CPatient $patient, CMbObject $targetObject): ?string
    {
        return null;
        if (!$targetObject instanceof CDocumentItem) {
            return null;
        }

        $author = $targetObject->loadRefAuthor();

        $authorRole = null;

        // Correspondant
        if ($author && $author->rpps) {
            $medecin = new CMedecin();
            $ds = $medecin->getDS();
            $where_medecin = [];
            $ljoin_medecin = [];

            $ljoin_medecin["correspondant"] = "correspondant.medecin_id = medecin.medecin_id";
            $where_medecin['patient_id']    = $ds->prepare(' = ?', $patient->_id);
            $where_medecin['rpps']          = $ds->prepare(' = ?', $author->rpps);
            $where_medecin[]                = 'correspondant.correspondant_id IS NOT NULL';

            if ($medecin->countList($where_medecin, 'medecin.medecin_id', $ljoin_medecin ) > 0) {
                $authorRole = 'Correspondant';
            }
        }

        // Médecin traitant
        if ($author->_id && $author->rpps) {
            $medecin_traitant = $patient->loadRefMedecinTraitant();
            if ($medecin_traitant && $medecin_traitant->_id && $medecin_traitant->rpps === $author->rpps) {
                $authorRole = 'Médecin traitant';
            }
        }

        // Référent - Responsable du patient dans la structure de soins
        if ($targetObject instanceof CSejour && $author->_id == $targetObject->praticien_id) {
            $authorRole = 'Référent - Responsable du patient dans la structure de soins';
        }

        return $authorRole;
    }

    /**
     * Retourne les informations de l'etablissement sous la forme HL7v2 XON
     *
     * @param String $libelle     Libelle
     * @param String $identifiant Identifiant
     *
     * @return string
     */
    protected function getXONetablissement($libelle, $identifiant): string
    {
        $comp1  = $libelle;
        $comp6  = "&1.2.250.1.71.4.2.2&ISO";
        $comp7  = get_class($this) !== CXDSFactory::class ? "IDNST" : null;
        $comp10 = $identifiant;
        $xon    = "$comp1^^^^^$comp6^$comp7^^^$comp10";

        return $xon;
    }

    /**
     * @param CXDSRegistryPackage $registry
     *
     * @return CXDSRegistryPackage
     */
    protected function preparePatientDocument(CXDSRegistryPackage $registry): CXDSRegistryPackage
    {
        $ei_id      = &$this->id_external;
        $id         = $this->name_submission;
        $patient_id = $this->patient_identifier;
        $registry->setPatientId("ei$ei_id", $id, $patient_id);

        return $registry;
    }

    /**
     * Création  d'un document
     *
     * @param String $id       Identifiant
     * @param String $lid      Lid
     * @param bool   $hide     Est ce qu'on met le masquage dans la trame ?
     * @param array  $metadata Metadata
     * @param string $status   Status
     *
     * @return CXDSExtrinsicObject
     * @throws Exception
     */
    protected function createExtrinsicObject(
        $id,
        $lid = null,
        $hide = true,
        $metadata = null,
        $status = null
    ): CXDSExtrinsicObject {
        $cla_id     = &$this->id_classification;
        $ei_id      = &$this->id_external;
        $patient_id = $this->patient_identifier;
        $service    = $this->service_event;
        $industry   = $this->industry_code;
        $praticien  = $this->practicien;
        $authorRole = $this->author_role;

        $this->appendNameDocument($id);

        $extrinsic = new CXDSExtrinsicObject($id, "text/xml", $status, $lid);

        //effectiveTime en UTC
        if ($this->date_creation) {
            $extrinsic->setSlot("creationTime", [CXDSTools::getTimeUtc($this->date_creation)]);
        }

        //languageCode
        $extrinsic->setSlot("languageCode", [$this->langage]);

        // repositoryUniqueId
        $repository_unique_id = CMbArray::get($metadata, 'repositoryUniqueId');
        if ($this->repositoryUniqueId && $repository_unique_id) {
            $extrinsic->setSlot("repositoryUniqueId", [$repository_unique_id]);
        }

        //legalAuthenticator XCN
        $legalAuthenticator = $this->getPerson($praticien);
        $extrinsic->setSlot("legalAuthenticator", [$legalAuthenticator]);

        // Size (si document non signé alors le calcul de la taille est effectué sur l'ensemble de son contenu => tout le XML du CDA)
        if ($metadata) {
            $extrinsic->setSlot("size", [CMbArray::get($metadata, 'size')]);
        } elseif ($this->size) {
            $extrinsic->setSlot("size", [$this->size]);
        }

        // URI
        if ($this->uri) {
            $extrinsic->setSlot("URI", [$this->uri]);
        }

        // Hash
        if ($metadata) {
            $extrinsic->setSlot("hash", [CMbArray::get($metadata, 'hash')]);
        } elseif ($this->hash) {
            $extrinsic->setSlot("hash", [$this->hash]);
        }

        //documentationOf/serviceEvent/effectiveTime/low en UTC
        if ($service["time_start"]) {
            $extrinsic->setSlot("serviceStartTime", [CXDSTools::getTimeUtc($service["time_start"])]);
        }

        //documentationOf/serviceEvent/effectiveTime/high en UTC
        if ($service["time_stop"]) {
            $extrinsic->setSlot("serviceStopTime", [CXDSTools::getTimeUtc($service["time_stop"])]);
        }

        //recordTarget/patientRole/id
        $extrinsic->setSlot("sourcePatientId", [$patient_id]);

        //recordtarget/patientRole
        $extrinsic->setSlot("sourcePatientInfo", $this->getSourcepatientInfo($this->patient));

        // Ajout du titre
        $extrinsic->setTitle($this->nom);

        //Auteur du document
        $document = new CXDSDocumentEntryAuthor("cla$cla_id", $id);
        $this->setClaId();

        //author/assignedAuthor
        $author = $this->getPerson($praticien);
        $document->setAuthorPerson([$author]);

        //author/assignedAuthor/code
        $spec = $praticien->loadRefOtherSpec();
        if ($spec->libelle) {
            $document->setAuthorSpecialty(["$spec->code^$spec->libelle^$spec->oid"]);
        }

        // Add author role
        if ($authorRole) {
            $document->appendAuthorRole($authorRole);
        }

        //author/assignedAuthor/representedOrganization - si absent, ne pas renseigner
        //si nom pas présent - champ vide
        //si id nullflavor alors 6-7-10 vide

        // Pour un séjour : On prend l'établissement du séjour et non l'étab de la fonction
        if ($this->targetObject instanceof CSejour) {
            $author_organization = $this->targetObject->loadRefEtablissement();
        } elseif ($this->targetObject instanceof COperation) {
            $author_organization = $this->targetObject->loadRefSejour()->loadRefEtablissement();
        } else {
            $author_organization = $praticien->loadRefFunction()->loadRefGroup();
        }

        if ($author_organization->_id) {
            $institution = $this->getXONetablissement(
                $author_organization->text,
                $this->getIdEtablissement(false, $author_organization),
            );
            $document->setAuthorInstitution([$institution]);
        }
        $extrinsic->appendDocumentEntryAuthor($document);

        //confidentialityCode
        $confidentialite = $this->confidentiality;
        $confid          = new CXDSConfidentiality("cla$cla_id", $id, $confidentialite["code"]);
        $this->setClaId();
        $confid->setCodingScheme([$confidentialite["codeSystem"]]);
        $confid->setName($confidentialite["displayName"]);
        $extrinsic->appendConfidentiality($confid);

        switch ($this->hide) {
            case self::HIDE_PRACTICIEN:
                $confid2 = CXDSConfidentiality::getMasquage("cla$cla_id", $id, "MASQUE_PS");
                $this->setClaId();
                $extrinsic->appendConfidentiality($confid2);
                break;
            case self::HIDE_PATIENT:
                $confid3 = CXDSConfidentiality::getMasquage("cla$cla_id", $id, "INVISIBLE_PATIENT");
                $this->setClaId();
                $extrinsic->appendConfidentiality($confid3);
                break;
            case self::HIDE_REPRESENTANT:
                $confid4 = CXDSConfidentiality::getMasquage("cla$cla_id", $id, "INVISIBLE_REPRESENTANTS_LEGAUX");
                $this->setClaId();
                $extrinsic->appendConfidentiality($confid4);
                break;
            default:
        }

        //documentationOf/serviceEvent/code - table de correspondance
        if (($service["oid"] ?? false) && ($service["code"] ?? false)) {
            $eventSystem = $service["oid"];
            $eventCode   = $service["code"];
            switch ($service["type_code"]) {
                case "cim10":
                    $cim10   = CCodeCIM10::get($eventCode);
                    $libelle = $cim10->libelle;
                    break;
                case "ccam":
                    $ccam    = CDatedCodeCCAM::get($eventCode);
                    $libelle = $ccam->libelleCourt;
                    break;
                default:
                    // CAS VSM METADATA
                    $libelle = CMbArray::get($service, "libelle");
            }

            $event = new CXDSEventCodeList("cla$cla_id", $id, $eventCode);
            $this->setClaId();
            $event->setCodingScheme([$eventSystem]);
            $event->setName($libelle);
            $extrinsic->appendEventCodeList($event);
        }

        // format
        $codingScheme = CMbArray::get($this->entry_media_type, "codingScheme");
        $name         = CMbArray::get($this->entry_media_type, "name");
        $formatCode   = CMbArray::get($this->entry_media_type, "formatCode");
        $format       = new CXDSFormat("cla$cla_id", $id, $formatCode);
        $this->setClaId();
        $format->setCodingScheme([$codingScheme]);
        $format->setName($name);
        $extrinsic->setFormat($format);

        //componentOf/encompassingEncounter/location/healthCareFacility/code
        $healtcare = $this->health_care_facility;
        $healt     = new CXDSHealthcareFacilityType("cla$cla_id", $id, $healtcare["code"]);
        $this->setClaId();
        $healt->setCodingScheme([$healtcare["codeSystem"]]);
        $healt->setName($healtcare["displayName"]);
        $extrinsic->setHealthcareFacilityType($healt);

        //documentationOf/serviceEvent/performer/assignedEntity/representedOrganization/standardIndustryClassCode
        $pratice = new CXDSPracticeSetting("cla$cla_id", $id, $industry["code"]);
        $this->setClaId();
        $pratice->setCodingScheme([$industry["codeSystem"]]);
        $pratice->setName($industry["displayName"]);
        $this->practice_setting = $this->practice_setting ? $this->practice_setting : $industry;
        $extrinsic->setPracticeSetting($pratice);

        //code
        $code = $this->code;
        $type = new CXDSType("cla$cla_id", $id, $code["code"]);
        $this->setClaId();
        $type->setCodingScheme([$code["codeSystem"]]);
        $type->setName($code["displayName"]);
        $extrinsic->setType($type);

        //class
        [$classCode, $oid, $name] = $this->class;
        $classification = new CXDSClass("cla$cla_id", $id, $classCode);
        $this->setClaId();
        $classification->setCodingScheme([$oid]);
        $classification->setName($name);
        $extrinsic->setClass($classification);

        //recordTarget/patientRole/id
        $this->setExtrinsincPatientID($extrinsic, $id);
        $this->setEiId();

        //id - root
        $root                   = $this->root;
        $this->oid["extrinsic"] = $root;
        $extrinsic->setUniqueId("ei$ei_id", $id, $root);
        $this->setEiId();

        return $extrinsic;
    }


    /**
     * Création du document de la signature
     *
     * @param String $id            Identifiant
     * @param String $creation_time Creation Time
     *
     * @return CXDSExtrinsicObject
     * @throws Exception
     */
    public function createSignature($id, $creation_time = null)
    {
        // Ajout des metadata pour le lot de soumission
        $cla_id    = &$this->id_classification;
        $ei_id     = &$this->id_external;
        $ins       = $this->ins_patient;
        $praticien = $this->practicien;

        //Création du document
        $extrinsic = new CXDSExtrinsicObject($id, "text/xml");
        $extrinsic->setSlot("creationTime", [$creation_time ?: CXDSTools::getTimeUtc()]);
        $extrinsic->setSlot("languageCode", ["art"]);

        // Pour un séjour : On prend l'établissement du séjour et non l'étab de la fonction
        if ($this->targetObject instanceof CSejour) {
            $author_organization = $this->targetObject->loadRefEtablissement();
        } elseif ($this->targetObject instanceof COperation) {
            $author_organization = $this->targetObject->loadRefSejour()->loadRefEtablissement();
        } else {
            $author_organization = $praticien->loadRefFunction()->loadRefGroup();
        }

        // on prend le praticien (sas envoi)
        $preferences = CPreferences::getAllPrefs($praticien->_id);
        if ($author_organization && $author_organization->_id &&
            (CMbArray::get($preferences, 'authentification_directe') ==  '2')
            && CAppUI::gconf("dmp general authentification_indirecte", $author_organization->_id)
        ) {
            $legalAuthenticator = $this->getPersonEtab($praticien, $author_organization);
            $author             = $this->getPersonEtab($praticien, $author_organization);
        } else {
            $legalAuthenticator = $this->getPerson($praticien);
            $author = $this->getPerson($praticien);
        }

        $extrinsic->setSlot("legalAuthenticator", [$legalAuthenticator]);
        $extrinsic->setSlot("serviceStartTime", [$creation_time ?: CXDSTools::getTimeUtc()]);
        $extrinsic->setSlot("serviceStopTime", [$creation_time ?: CXDSTools::getTimeUtc()]);

        //patientId du lot de submission
        $extrinsic->setSlot("sourcePatientId", [$ins]);
        $extrinsic->setTitle("Source");

        //identique à celui qui envoie
        $document = new CXDSDocumentEntryAuthor("cla$cla_id", $id);
        $this->setClaId();
        // On prend le praticien de la consult/du séjour (sas envoi)
        $document->setAuthorPerson([$author]);
        //$document->setAuthorPerson(array($this->xcn_mediuser));
        //$document->setAuthorSpecialty(array($specialty));

        //author/assignedAuthor/code
        $spec = $praticien->loadRefOtherSpec();
        if ($spec->libelle) {
            $document->setAuthorSpecialty(["$spec->code^$spec->libelle^$spec->oid"]);
        }

        if ($author_organization->_id) {
            $institution = $this->getXONetablissement(
                $author_organization->text,
                $this->getIdEtablissement(false, $author_organization)
            );
            $document->setAuthorInstitution([$institution]);
        } else {
            //Institution qui envoie le document
            $document->setAuthorInstitution([$this->xon_etablissement]);
        }

        //$document->setAuthorInstitution(array($this->xon_etablissement));
        $extrinsic->appendDocumentEntryAuthor($document);

        $classification = new CXDSClass("cla$cla_id", $id, "urn:oid:1.3.6.1.4.1.19376.1.2.1.1.1");
        $this->setClaId();
        $classification->setCodingScheme(["URN"]);
        $classification->setName("Digital Signature");
        $extrinsic->setClass($classification);

        $confid = new CXDSConfidentiality("cla$cla_id", $id, "N");
        $this->setClaId();
        $confid->setCodingScheme(["2.16.840.1.113883.5.25"]);
        $confid->setName("Normal");
        $extrinsic->appendConfidentiality($confid);

        $confid2 = CXDSConfidentiality::getMasquage("cla$cla_id", $id, "MASQUE_PS");
        $this->setClaId();
        $extrinsic->appendConfidentiality($confid2);

        $confid3 = CXDSConfidentiality::getMasquage("cla$cla_id", $id, "INVISIBLE_PATIENT");
        $this->setClaId();
        $extrinsic->appendConfidentiality($confid3);

        $event = new CXDSEventCodeList("cla$cla_id", $id, "1.2.840.10065.1.12.1.14");
        $this->setClaId();
        $event->setCodingScheme(["1.2.840.10065.1.12"]);
        $event->setName("Source");
        $extrinsic->appendEventCodeList($event);

        $format = new CXDSFormat("cla$cla_id", $id, "http://www.w3.org/2000/09/xmldsig#");
        $this->setClaId();
        $format->setCodingScheme(["URN"]);
        $format->setName("Default Signature Style");
        $extrinsic->setFormat($format);

        $healtcare = $this->health_care_facility;
        $healt     = new CXDSHealthcareFacilityType("cla$cla_id", $id, $healtcare["code"]);
        $this->setClaId();
        $healt->setCodingScheme([$healtcare["codeSystem"]]);
        $healt->setName($healtcare["displayName"]);
        $extrinsic->setHealthcareFacilityType($healt);

        $industry = $this->practice_setting;
        $pratice  = new CXDSPracticeSetting("cla$cla_id", $id, $industry["code"]);
        $this->setClaId();
        $pratice->setCodingScheme([$industry["codeSystem"]]);
        $pratice->setName($industry["displayName"]);
        $extrinsic->setPracticeSetting($pratice);

        $type = new CXDSType("cla$cla_id", $id, "E1762");
        $this->setClaId();
        $type->setCodingScheme(["ASTM"]);
        $type->setName("Full Document");
        $extrinsic->setType($type);

        //identique au lot de submission
        $extrinsic->setPatientId("ei$ei_id", $id, $ins);
        $this->setEiId();

        //identifiant de la signature
        $this->oid["signature"] = $this->oid["lot"] . "0";
        $extrinsic->setUniqueId("ei$ei_id", $id, $this->oid["signature"]);
        $this->setEiId();

        return $extrinsic;
    }

    /**
     * Création des associations
     *
     * @param String $id     Identifiant
     * @param String $source Source
     * @param String $target Cible
     * @param bool   $sign   Association de type signature
     * @param bool   $rplc   Remplacement
     *
     * @return CXDSHasMemberAssociation
     */
    public function createAssociation($id, $source, $target, $sign = false, $rplc = false)
    {
        $hasmember = new CXDSHasMemberAssociation($id, $source, $target, $sign, $rplc);
        if (!$sign || !$rplc) {
            $hasmember->setSubmissionSetStatus(["Original"]);
        }

        return $hasmember;
    }

    /**
     * Retourne la personne
     *
     * @param CMediusers $praticien CMediusers
     * @param CGroups    $group     Group
     *
     * @return string
     */
    public function getPersonEtab(CMediusers $praticien, CGroups $group): ?string
    {
        $comp1  = "";
        $comp2  = $praticien->_p_last_name;
        $comp3  = $praticien->_p_first_name;
        $result = "$comp1^$comp2^$comp3";

        return $result;
    }

    /**
     * Retourne la person
     *
     * @param CMediusers $praticien CMediusers
     *
     * @return string
     */
    public function getPerson(CMediusers $praticien): ?string
    {
        $comp1  = "";
        $comp2  = $praticien->_p_last_name;
        $comp3  = $praticien->_p_first_name;
        $result = "$comp1^$comp2^$comp3";

        return $result;
    }

    /**
     * Retourne le type d'id passé en paramètre
     *
     * @param String $id String
     *
     * @return string
     */
    protected function getTypeId($id)
    {
        $result = "IDNPS";
        if (strpos($id, "/") !== false) {
            $result = "EI";
        }
        if (strlen($id) === 22) {
            $result = "INS-C";
        }

        /*if (strlen($id) === 12) {
          $result = "INS-A";
        }*/

        return $result;
    }

    /**
     * Transforme une chaine date au format time CDA
     *
     * @param String $date      String
     * @param bool   $naissance false
     *
     * @return string
     */
    protected function getTimeToUtc($date, $naissance = false)
    {
        if (!$date) {
            return null;
        }
        if ($naissance) {
            $date = Datetime::createFromFormat("Y-m-d", $date);

            return $date->format("Ymd");
        }
        $timezone = new DateTimeZone(CAppUI::conf("timezone"));
        $date     = new DateTime($date, $timezone);

        return $date->format("YmdHisO");
    }

    /**
     * Retourne le sourcepatientinfo
     *
     * @param CPatient $patient patient
     *
     * @return String[]
     */
    protected function getSourcepatientInfo($patient)
    {
        $source_info = [];
        if ($this::TYPE === self::TYPE_ZEPRA || $this::TYPE === self::TYPE_DMP || $this::TYPE == self::TYPE_ANS) {
            if ($patient->nom_jeune_fille) {
                $pid5          = "PID-5|$patient->nom_jeune_fille^$patient->prenom^^^^^L";
                $source_info[] = $pid5;
            }
        }

        if ($patient->prenom_usuel && $patient->nom) {
            $pid5 = "PID-5|$patient->nom^$patient->prenom_usuel^^^^^D";
            $source_info[] = $pid5;
        }
        $date          = $this->getTimeToUtc($patient->_p_birth_date, true);
        $pid7          = "PID-7|$date";
        if ($this::TYPE === self::TYPE_ZEPRA) {
            $pid7 = $pid7 . "000000";
        }
        $source_info[] = $pid7;
        $sexe          = mb_strtoupper($patient->sexe);
        $pid8          = "PID-8|$sexe";
        $source_info[] = $pid8;
        if ($this::TYPE !== self::TYPE_ZEPRA && ($patient->_p_street_address || $patient->_p_city || $patient->_p_postal_code)) {
            $addresses     = preg_replace("#[\t\n\v\f\r]+#", " ", $patient->_p_street_address, PREG_SPLIT_NO_EMPTY);
            $pid11         = "PID-11|$addresses^^$patient->_p_city^^$patient->_p_postal_code";
            $source_info[] = $pid11;
        }

        if ($this::TYPE === self::TYPE_ZEPRA) {
            // Ajout du lieu de naissance (si on a toutes les infos et que le patient est né en France)
            $pays_naissance_insee = CPaysInsee::getPaysByNumerique($patient->pays_naissance_insee);
            if ($patient->lieu_naissance && $patient->cp_naissance && $patient->pays_naissance_insee
                && $pays_naissance_insee->alpha_3 == "FRA"
            ) {
                $pid11_birth_location = "PID-11|^^$patient->lieu_naissance^^$patient->cp_naissance^$pays_naissance_insee->alpha_3^BDL";
            } else {
                $pid11_birth_location = "PID-11|^^^^00000^UKN^BDL";
            }
            $source_info[] = $pid11_birth_location;

            // Ajout du lieu de résidence
            $addresses     = preg_replace("#[\t\n\v\f\r]+#", " ", $patient->_p_street_address, PREG_SPLIT_NO_EMPTY);
            $city          = $patient->_p_city ?: "UKN";
            $city          = $patient->_p_postal_code ?: "00000";
            $pays_insee    = CPaysInsee::getPaysByNumerique($patient->pays_insee);
            $pid11_home    = "PID-11|$addresses^^$patient->_p_city^^$patient->_p_postal_code^$pays_insee->alpha_3^H";
            $source_info[] = $pid11_home;
        }

        if ($patient->_p_phone_number) {
            $pid13         = "PID-13|$patient->_p_phone_number";
            $source_info[] = $pid13;
        }
        if ($patient->_p_mobile_phone_number) {
            $pid14         = "PID-14|$patient->_p_mobile_phone_number";
            $source_info[] = $pid14;
        }
        $pid16         = "PID-16|{$this->getMaritalStatus($patient->situation_famille)}";
        $source_info[] = $pid16;

        // Pour Sisra : Il faut que des majuscules, pas d'accent, pas d'apostrophe, pas de caractères spéciaux
        if ($this::TYPE === self::TYPE_ZEPRA) {
            $source_info_formated = [];
            foreach ($source_info as $_source_info) {
                // Suppression des caracteres accentués
                $data_formated = CMbString::removeDiacritics($_source_info);
                // Passage uniquement de majuscule
                $data_formated = mb_strtoupper($data_formated);
                // transformation de l'apostrophe et du tiret pour les noms composés en espace
                //$data_formated = str_replace("-", " ", $data_formated);
                $data_formated          = str_replace("'", " ", $data_formated);
                $source_info_formated[] = $data_formated;
            }
        } else {
            $source_info_formated = $source_info;
        }

        return $source_info_formated;
    }

    /**
     * Return the Marital Status
     *
     * @param string|null $status mediboard status
     *
     * @return string
     */
    protected function getMaritalStatus(?string $status): string
    {
        switch ($status) {
            case "S":
                $ce = "S";
                break;
            case "M":
                $ce = "M";
                break;
            case "G":
                $ce = "G";
                break;
            case "D":
                $ce = "D";
                break;
            case "W":
                $ce = "W";
                break;
            case "A":
                $ce = "A";
                break;
            case "P":
                $ce = "P";
                break;
            default:
                $ce = "U";
        }

        return $ce;
    }

    /**
     * Retourne l'OID du patient
     *
     * @param CPatient              $patient  Patient
     * @param CInteropReceiver|null $receiver Receiver
     *
     * @return string
     */
    protected function getID(CPatient $patient, CInteropReceiver $receiver = null): string
    {
        $comp1 = $patient->_IPP ?: $patient->_id;

        $only_oid_root = !$this instanceof CXDSANS;
        $oid           = CMbOID::getOIDOfInstance($patient, $receiver, $only_oid_root);
        $comp4         = "&$oid&ISO";
        $comp5         = "^PI";

        return "$comp1^^^$comp4" . $comp5;
    }

    /**
     * @param CXDSExtrinsicObject $extrinsic
     * @param string|null         $id
     */
    protected function setExtrinsincPatientID(CXDSExtrinsicObject $extrinsic, ?string $id): void
    {
        $ei_id      = &$this->id_external;
        $patient_id = $this->patient_identifier;
        $extrinsic->setPatientId("ei$ei_id", $id, $patient_id);
    }
}
