<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\DocumentReference\Mapper;

use Exception;
use Ox\Core\CStoredObject;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Mapping\R4\DocumentReferenceMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\DocumentReference\CFHIRDataTypeDocumentReferenceContent;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\DocumentReference\CFHIRDataTypeDocumentReferenceContext;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAttachment;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceDomainTrait;
use Ox\Interop\Fhir\Resources\R4\Encounter\CFHIRResourceEncounter;
use Ox\Interop\Fhir\Resources\R4\Patient\CFHIRResourcePatient;
use Ox\Interop\Fhir\Resources\R4\Practitioner\CFHIRResourcePractitioner;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\CNote;

/**
 * Description
 */
class DocumentReference implements DocumentReferenceMappingInterface, DelegatedObjectMapperInterface
{
    use CStoredObjectResourceDomainTrait;

    /** @var CFHIRDomainResource */
    protected CFHIRResource $resource;

    /** @var CDocumentItem */
    protected $object;

    /** @var CStoredObject */
    protected $target;

    /**
     * @inheritDoc
     */
    public function onlyProfiles(): array
    {
        return [CFhir::class];
    }

    /**
     * @return string[]
     */
    public function onlyRessources(): array
    {
        return [CFHIRDomainResource::class];
    }

    /**
     * @param CFHIRResource $resource
     * @param mixed         $object
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource, $object): bool
    {
        return $object instanceof CDocumentItem && $object->_id;
    }

    /**
     * @inheritDoc
     */
    public function setResource(CFHIRResource $resource, $object): void
    {
        $this->resource = $resource;
        $this->object   = $object;
        $this->target   = $this->object->loadTargetObject();
    }

    public function mapMasterIdentifier(): ?CFHIRDataTypeIdentifier
    {
        return new CFHIRDataTypeIdentifier($this->object->getUuid());
    }

    /**
     * @inheritDoc
     */
    public function mapStatus(): ?CFHIRDataTypeCode
    {
        return (new CFHIRDataTypeCode('current'));
    }

    /**
     * @inheritDoc
     */
    public function mapDocStatus(): ?CFHIRDataTypeCode
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapType(): ?CFHIRDataTypeCodeableConcept
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapCategory(): array
    {
        $categories = [];

        if ($this->object->type_doc_dmp) {
            [$codeSystem, $code] = explode('^', $this->object->type_doc_dmp);

            if ($codes = CANSValueSet::getTypeCode($code)) {
                $categories[] = CFHIRDataTypeCodeableConcept::fromValues($codes);
            }
        }

        return $categories;
    }

    /**
     * @inheritDoc
     */
    public function mapSubject(): ?CFHIRDataTypeReference
    {
        if (!$patient = $this->object->getFromStore(CPatient::class)) {
            $patient = $this->object->getIndexablePatient();
            if (!$patient->_id) {
                return null;
            }
        }

        return $this->resource->addReference(CFHIRResourcePatient::class, $patient);
    }

    /**
     * @inheritDoc
     */
    public function mapDate(): ?CFHIRDataTypeInstant
    {
        return new CFHIRDataTypeInstant($this->object->_file_date);
    }

    /**
     * @inheritDoc
     */
    public function mapAuthor(): array
    {
        $available_author = [
            CCorrespondantPatient::class,
            CMedecin::class,
        ];

        $authors = [];
        foreach ($available_author as $author_class) {
            if ($author = $this->object->getFromStore($author_class)) {
                 $authors[] = $this->resource->addReference(CFHIRResourcePractitioner::class, $author);
            }
        }

        if ($author = $this->object->loadRefAuthor()) {
            $authors[] = $this->resource->addReference(CFHIRResourcePractitioner::class, $author);
        }

        return $authors;
    }

    /**
     * @inheritDoc
     */
    public function mapAuthenticator(): ?CFHIRDataTypeReference
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapCustodian(): ?CFHIRDataTypeReference
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapRelatesTo(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapDescription(): ?CFHIRDataTypeString
    {
        /** @var CNote $note */
        $note = $this->object->loadLastBackRef('notes');
        if ($note && $note->_id) {
            return new CFHIRDataTypeString($note->text ?: $note->libelle);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapSecurityLabel(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapContent(): array
    {
        try {
            if (!$content = $this->object->getBinaryContent(true, false)) {
                return [];
            }
        } catch (Exception $e) {
            return [];
        }

        $content_type = $this->object instanceof CFile ? $this->object->file_type : "application/pdf";
        $title = $this->object instanceof CFile ? $this->object->file_name : $this->object->nom;
        $reference_content = CFHIRDataTypeDocumentReferenceContent::build(
            [
                'attachment' => CFHIRDataTypeAttachment::build(
                    [
                        'data'        => $content,
                        'size'        => strlen($content),
                        'hash'        => sha1($content),
                        'title'       => $title,
                        'creation'    => $this->object->_file_date,
                        'contentType' => $content_type,
                    ]
                ),
            ]
        );

        return [$reference_content];
    }

    /**
     * @inheritDoc
     */
    public function mapContext(): ?CFHIRDataTypeDocumentReferenceContext
    {
        $context = new CFHIRDataTypeDocumentReferenceContext();
        $target  = $this->target;

        if ($target instanceof COperation) {
            $target = $target->loadRefSejour();
        }

        if ($target instanceof CSejour) {
            $context->encounter = $this->resource->addReference(CFHIRResourceEncounter::class, $target);
            $context->period    = CFHIRDataTypePeriod::from($target->entree, $target->sortie);
        }

        return !$context->isNull() ? $context : null;
    }
}
