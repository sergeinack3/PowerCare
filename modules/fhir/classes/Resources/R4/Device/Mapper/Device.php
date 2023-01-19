<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Device\Mapper;

use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Mapping\R4\DeviceMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceDomainTrait;
use Ox\Interop\Fhir\Resources\R4\Device\CFHIRResourceDevice;

/**
 * Description
 */
class Device implements DelegatedObjectMapperInterface, DeviceMappingInterface
{
    use CStoredObjectResourceDomainTrait;

    /** @var \Ox\Interop\Mes\Device */
    protected $object;

    /** @var CFHIRResourceDevice */
    protected CFHIRResource $resource;

    public function setResource(CFHIRResource $resource, $object): void
    {
        $this->object   = $object;
        $this->resource = $resource;
    }

    /**
     * @return string[]
     */
    public function onlyProfiles(): array
    {
        return [CFHIR::class];
    }

    /**
     * @return string[]
     */
    public function onlyRessources(): array
    {
        return [CFHIRResourceDevice::class];
    }

    /**
     * @inheritDoc
     * @throws CFHIRException
     */
    public function mapIdentifier(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapDefinition(): ?CFHIRDataTypeReference
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapUdiCarrier(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapStatus(): ?CFHIRDataTypeCode
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapStatusReason(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapDistinctIdentifier(): ?CFHIRDataTypeString
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapManufacturer(): ?CFHIRDataTypeString
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapManufactureDate(): ?CFHIRDataTypeDateTime
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapExpirationDate(): ?CFHIRDataTypeDateTime
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapLotNumber(): ?CFHIRDataTypeString
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapSerialNumber(): ?CFHIRDataTypeString
    {
        return null;
    }

    /**
     * @inheritDoc
     * @throws CFHIRException
     */
    public function mapDeviceName(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapModelNumber(): ?CFHIRDataTypeString
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapPartNumber(): ?CFHIRDataTypeString
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
    public function mapSpecialization(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapVersion(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapProperty(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapPatient(): ?CFHIRDataTypeReference
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapOwner(): ?CFHIRDataTypeReference
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapContact(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapLocation(): ?CFHIRDataTypeReference
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapUrl(): ?CFHIRDataTypeUri
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapNote(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapSafety(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapParent(): ?CFHIRDataTypeReference
    {
        return null;
    }
}
