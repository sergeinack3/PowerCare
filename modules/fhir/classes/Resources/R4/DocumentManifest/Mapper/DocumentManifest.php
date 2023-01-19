<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\DocumentManifest\Mapper;

use Exception;
use Ox\Core\CAppUI;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Mapping\R4\DocumentManifestMappingInterface;
use Ox\Interop\Fhir\Controllers\CFHIRController;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceDomainTrait;
use Ox\Mediboard\Files\CDocumentManifest;
use Ox\Mediboard\Files\CDocumentReference;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Description
 */
class DocumentManifest implements DocumentManifestMappingInterface, DelegatedObjectMapperInterface
{
    use CStoredObjectResourceDomainTrait;

    /** @var CFHIRDomainResource */
    protected CFHIRResource $resource;

    /** @var CDocumentManifest */
    protected $object;

    /**
     * @inheritDoc
     */
    public function onlyProfiles(): array
    {
        return [CFhir::class];
    }

    /**
     * @inheritDoc
     */
    public function setResource(CFHIRResource $resource, $object): void
    {
        $this->resource = $resource;
        $this->object   = $object;
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
        return $object instanceof CDocumentManifest && $object->_id;
    }

    /**
     * @inheritDoc
     */
    public function mapMasterIdentifier(): ?CFHIRDataTypeIdentifier
    {
        return (new CFHIRDataTypeIdentifier())
            ->setValue($this->object->repositoryUniqueID)
            ->setSystem(CAppUI::conf("mb_oid"));
    }

    /**
     * @inheritDoc
     */
    public function mapStatus(): ?CFHIRDataTypeCode
    {
        return new CFHIRDataTypeCode("current");
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
    public function mapSubject(): ?CFHIRDataTypeReference
    {
        $url_patient   = CFHIRController::getUrl(
            "fhir_read",
            [
                'resource'    => "Patient",
                'resource_id' => $this->object->patient_id,
            ]
        );

        return (new CFHIRDataTypeReference())
            ->setReference($url_patient);
    }

    /**
     * @inheritDoc
     */
    public function mapCreated(): ?CFHIRDataTypeDateTime
    {
        return $this->object->created_datetime ? new CFHIRDataTypeDateTime($this->object->created_datetime) : null;
    }

    /**
     * @inheritDoc
     */
    public function mapAuthor(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapRecipient(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapSource(): ?CFHIRDataTypeUri
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapDescription(): ?CFHIRDataTypeString
    {
        return null;
    }

    /**
     * @inheritDoc
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function mapContent(): array
    {
        $contents = [];
        $docs_reference = $this->object->loadRefsDocumentsReferences();
        foreach ($docs_reference as $_doc_reference) {
            $contents[] = $this->resource->addReference(CDocumentReference::class, $_doc_reference);
        }

        return $contents;
    }

    /**
     * @inheritDoc
     */
    public function mapRelated(): array
    {
        return [];
    }
}
