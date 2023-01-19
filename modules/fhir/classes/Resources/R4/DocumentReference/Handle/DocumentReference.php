<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\DocumentReference\Handle;

use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CStoredObject;
use Ox\Interop\Eai\CDomain;
use Ox\Interop\Eai\Manager\Exceptions\FileManagerException;
use Ox\Interop\Eai\Manager\FileManager;
use Ox\Interop\Eai\Repository\PatientRepository;
use Ox\Interop\Eai\Repository\SejourRepository;
use Ox\Interop\Eai\Resolver\FileTargetResolver;
use Ox\Interop\Eai\Tools\TraceabilityTrait;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectHandleInterface;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\DocumentReference\CFHIRDataTypeDocumentReferenceContent;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Exception\CFHIRExceptionInvalidValue;
use Ox\Interop\Fhir\Exception\CFHIRExceptionNotSupported;
use Ox\Interop\Fhir\Interactions\CFHIRInteraction;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionUpdate;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\DocumentReference\CFHIRResourceDocumentReference;
use Ox\Interop\Fhir\Resources\R4\Encounter\CFHIRResourceEncounter;
use Ox\Interop\Fhir\Resources\R4\Patient\CFHIRResourcePatient;
use Ox\Interop\Fhir\Utilities\Helper\PatientHelper;
use Ox\Interop\Fhir\Utilities\Helper\ResourceHelper;
use Ox\Interop\Fhir\Utilities\Helper\SejourHelper;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Files\CFileTraceability;
use Ox\Mediboard\Loinc\CLoinc;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\System\CSenderHTTP;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Description
 */
class DocumentReference implements DelegatedObjectHandleInterface
{
    use TraceabilityTrait;

    protected ?CFHIRResourceDocumentReference $resource = null;
    protected ?CSenderHTTP $sender = null;
    protected ?CFileTraceability $traceability = null;
    protected ?string $ipp = null;
    protected ?CFHIRInteraction $interaction = null;
    protected ?string $nda = null;

    /**
     * @inheritDoc
     */
    public function onlyRessources(): array
    {
        return [CFHIRResourceDocumentReference::class];
    }

    /**
     * @inheritDoc
     */
    public function onlyProfiles(): array
    {
        return [CFHIR::class];
    }

    /**
     * @param CFHIRResource $resource
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource): bool
    {
        $has_interaction = $resource->getInteraction() instanceof CFHIRInteractionCreate
            || $resource->getInteraction() instanceof CFHIRInteractionUpdate;

        return $resource->getSender() instanceof CSenderHTTP && $has_interaction;
    }

    /**
     * @param CFHIRResourceDocumentReference $resource
     *
     * @throws CMbException
     * @throws InvalidArgumentException
     * @throws CFHIRException
     * @throws Exception
     * @inheritDoc
     */
    public function handle(CFHIRResource $resource): ?CFHIRResource
    {
        $this->resource    = $resource;
        $this->sender      = $sender = $resource->_sender;
        $this->interaction = $interaction = $resource->getInteraction();

        if (!$interaction instanceof CFHIRInteractionCreate && !$interaction instanceof CFHIRInteractionUpdate) {
            throw new CFHIRExceptionNotSupported('Invalid interaction given');
        }

        $patient = $this->determinePatient();

        $context = $this->determineContext($patient);

        $file = $this->makeFile($patient, $context);

        // determine category for file
        $files_category = $this->determineFileCategory($context);

        // store du file
        try {
            $stored_file = (new FileManager($sender->_configs["define_name"]))
                ->enableTraceability($this->generateTraceability($file->file_date))
                ->setCategory($files_category)
                ->setTag($sender->_self_tag)
                ->enableLoadMatching(true)
                ->store($file);
        } catch (FileManagerException $exception) {
            throw CFHIRException::convert($exception);
        }

        // integrate idex
        $this->handleIdentifiers($stored_file);

        // handle replacement
        $this->handleRelates($stored_file);

        $resource_stored = $resource->buildSelf();
        $resource_stored->setObject($stored_file);

        return $resource_stored;
    }

    /**
     * @return bool
     */
    private function isModeSas(): bool
    {
        return $this->sender->_configs['mode_sas'];
    }

    /**
     * Try to determine patient with multiple search
     *
     * @return CPatient|null
     * @throws CMbException
     */
    private function determinePatient(): ?CPatient
    {
        // try to find fhir resource patient
        if (!$resource_patient = $this->getResourcePatient()) {
            return null;
        }

        // search patient ins
        $group_id = $this->sender->group_id;
        $patient  = PatientHelper::primaryMapping($resource_patient);
        $ins      = [
            PatientHelper::getINS($resource_patient, PatientHelper::INS_NIR),
            PatientHelper::getINS($resource_patient, PatientHelper::INS_NIA),
        ];

        // search patient ipp
        $this->ipp = $ipp = PatientHelper::getIPP($resource_patient, $group_id);

        // search patient
        return (new PatientRepository($this->sender->_configs['search_patient_strategy']))
            ->withINS(...$ins)
            ->withIPP($ipp, $this->sender->_tag_patient)
            ->withPatientSearched($patient, $group_id)
            ->find();
    }

    /**
     * Search Resource patient in documentReference
     *
     * @return CFHIRResourcePatient|null
     */
    private function getResourcePatient(): ?CFHIRResourcePatient
    {
        $resource         = $this->resource;
        $resource_patient = null;

        // contained resources
        $reference_type = $resource->getSubject()->resolveTypeReference();
        if ($resource->getContained() && $reference_type === $resource->getSubject()::REFERENCE_TYPE_CONTAINED) {
            /** @var CFHIRResourcePatient $resource_patient */
            $resource_patient = $resource->getContainedOfType(CFHIRResourcePatient::class);
        }

        return $resource_patient;
    }

    /**
     * Determine the context that the file will be attach
     *
     * @param CPatient $patient
     *
     * @return CStoredObject|null
     * @throws InvalidArgumentException
     */
    private function determineContext(?CPatient $patient): ?CStoredObject
    {
        // fonctionnement mode sas
        if ($this->isModeSas()) {
            return null;
        }

        // for now only search sejour
        if (!$resource_encounter = $this->getResourceEncounter()) {
            return null;
        }

        // nda
        $domain_nda = CDomain::getMasterDomainSejour($this->sender->group_id);
        $NDA        = $domain_nda->OID ? SejourHelper::getNDA($resource_encounter, $domain_nda->OID) : null;

        // ox identifier
        $object_id = ResourceHelper::getResourceIdentifier($resource_encounter);

        // date of sejour
        $date_sejour = null;
        if (($period = $resource_encounter->getPeriod()) && !$period->isNull()) {
            $date_sejour = $period->start ? $period->start->getDatetime() : $period->end->getDatetime();
        }

        $sejour_repository = (new SejourRepository())
            ->setNDA($NDA, $this->sender->_tag_sejour)
            ->setPatient($patient)
            ->setDateSejour($date_sejour)
            ->setObjectId($object_id)
            ->setGroupId($this->sender->group_id);
        $sejour_repository->find();

        $object_attach = $this->sender->_configs["object_attach"] ?? 'CMbObject';

        return (new FileTargetResolver())
            ->setPatient($patient)
            ->setSejourRepository($sejour_repository)
            ->setModeSas($this->isModeSas())
            // ->setId400Category($id400_category) not supported for now
            ->resolve($this->sender, $object_attach);
    }

    /**
     * @param CPatient      $patient
     * @param CStoredObject $context
     *
     * @return CFile
     * @throws Exception
     */
    private function makeFile(?CPatient $patient, ?CStoredObject $context): CFile
    {
        $resource         = $this->resource;
        $contents          = $resource->getContent();
        $sender           = $this->sender;
        $resource_content = reset($contents);

        // check coherence content
        $this->checkContent($resource_content);

        // Get file data
        $file_name = $content = $file_type = null;
        if ($attachment = $resource_content->attachment) {
            if ($attachment->title) {
                $file_name = $attachment->title->getValue();
            }

            if ($attachment->data) {
                $content = $attachment->data->getDecodedData();
            }

            if ($attachment->contentType) {
                $file_type = $attachment->contentType->getValue();
            }
        }

        // file_date
        $file_date = CMbDT::dateTime();
        if ($resource->getDate()) {
            if (!CMbArray::get($sender->_configs, 'creation_date_file_like_treatment')) {
               $file_date = $resource->getDate()->getDatetime();
            }
        }

        // Gestion du CFile
        $file = new CFile();
        if ($context) {
            $file->setObject($context);
        }
        $file->file_name = $file_name;
        $file->file_type = $file_type;
        $file->file_date = $file_date;
        $file->setContent($content);
        $file->type_doc_dmp = $this->getTypeDocDmp();

        return $file;
    }

    /**
     * Check coherence of data
     *
     * @param CFHIRDataTypeDocumentReferenceContent|null $content
     *
     * @return void
     */
    private function checkContent(?CFHIRDataTypeDocumentReferenceContent $content): void
    {
        if (!$content || !($attachment = $content->attachment) || !($resource_size = $attachment->size) || !($resource_data = $attachment->data)) {
            return;
        }

        $size = $resource_size->getValue();
        if (($size) === null || ($decoded_data = $resource_data->getDecodedData()) === null) {
            return;
        }

        // size control
        if (strlen($decoded_data) !== $size) {
            throw new CFHIRExceptionInvalidValue("The size of content of file is not the same that size given");
        }

        // hash control
        if ($attachment->hash && (($hash = $attachment->hash->getDecodedData()) !== null)) {
            if (sha1($decoded_data) !== $hash) {
                throw new CFHIRExceptionInvalidValue("The hash of content of file is not the same that hash given");
            }
        }
    }

    /**
     * Generate traceability if config is active
     *
     * @param string $file_date
     *
     * @return CFileTraceability|null
     * @throws Exception
     */
    private function generateTraceability(string $file_date): ?CFileTraceability
    {
        if (!$this->isModeSas()) {
            return null;
        }

        $resource_patient = $this->getResourcePatient();
        $patient          = PatientHelper::primaryMapping($resource_patient);
        $traceability     = $this->generateTraceabilityHelper($this->sender, $patient);

        $received_dt = ($exchange = $this->interaction->_ref_fhir_exchange) &&
        $exchange->date_production ? $exchange->date_production : null;

        $traceability->received_datetime = $received_dt ?: CMbDT::dateTime();
        $traceability->IPP               = $this->ipp;
        $traceability->NDA               = $this->nda;
        $traceability->datetime_object   = $file_date;

        return $traceability;
    }

    /**
     * Determine file category
     *
     * @param $target
     * @param $files_category
     *
     * @return void
     * @throws Exception
     */
    private function determineFileCategory(?CStoredObject $object): ?CFilesCategory
    {
        $sender = $this->sender;

        /** @var CFilesCategory $files_category */
        // Chargement des objets associés à l'expéditeur
        if (!$files_category = $sender->getLinkedObjectOfType(CFilesCategory::class)) {
            return null;
        }

        $files_category_mb = null;
        if ($object instanceof CPatient && $sender->_configs["id_category_patient"] && ($sender->_configs["object_attach"] == "CPatient")) {
            $file_category_id = $sender->_configs["id_category_patient"];
            $category         = new CFilesCategory();
            $category->load($file_category_id);
            if ($category->_id) {
                $files_category_mb = $category;
            }
        } elseif ($files_category->_id && $sender->_configs["associate_category_to_a_file"]) {
            $files_category_mb = $files_category;
        }

        return $files_category_mb;
    }

    /**
     * @return CFHIRResourceEncounter|null
     */
    private function getResourceEncounter(): ?CFHIRResourceEncounter
    {
        return $this->resource->getContainedOfType(CFHIRResourceEncounter::class);
    }

    /**
     * Search code dmp in type of resource
     *
     * @return string|null
     */
    private function getTypeDocDmp(): ?string
    {
        $resource = $this->resource;

        if (!$resource->getType() || !($codings = $resource->getType()->coding)) {
            return null;
        }

        foreach ($codings as $coding) {
            if (ResourceHelper::isLoincSystem($coding->system)) {
                $code_loinc = CLoinc::get($coding->code->getValue());
                if ($code_loinc->_id) {
                    return $code_loinc->serialize();
                }
            }
        }

        return null;
    }

    /**
     * @param CFile $stored_file
     *
     * @return void
     */
    protected function handleIdentifiers(CFile $stored_file): void
    {
        // do nothing for now
    }

    protected function handleRelates(CFile $stored_file)
    {
    }
}
