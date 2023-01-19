<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Mapping\R4;

use Ox\Interop\Fhir\Contracts\Mapping\ResourceDomainMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Device\CFHIRDataTypeDeviceDeviceName;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Device\CFHIRDataTypeDeviceProperty;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Device\CFHIRDataTypeDeviceSpecialization;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Device\CFHIRDataTypeDeviceUdiCarrier;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Device\CFHIRDataTypeDeviceVersion;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAnnotation;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactPoint;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * Description
 */
interface DeviceMappingInterface extends ResourceDomainMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "Device";

    /**
     * @return CFHIRDataTypeIdentifier[]
     */
    public function mapIdentifier(): array;

    /**
     * Map property definition
     *
     * @return CFHIRDataTypeReference|null
     */
    public function mapDefinition(): ?CFHIRDataTypeReference;

    /**
     * Map property mapUdiCarrier
     *
     * @return CFHIRDataTypeDeviceUdiCarrier[]
     */
    public function mapUdiCarrier(): array;

    /**
     * Map property status
     *
     * @return CFHIRDataTypeCode|null
     */
    public function mapStatus(): ?CFHIRDataTypeCode;

    /**
     * Map property statusReason
     *
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function mapStatusReason(): array;

    /**
     * Map property distinctIdentifier
     *
     * @return CFHIRDataTypeString|null
     */
    public function mapDistinctIdentifier(): ?CFHIRDataTypeString;

    /**
     * Map property manufacturer
     *
     * @return CFHIRDataTypeString|null
     */
    public function mapManufacturer(): ?CFHIRDataTypeString;

    /**
     * Map property manufactureDate
     *
     * @return CFHIRDataTypeDateTime|null
     */
    public function mapManufactureDate(): ?CFHIRDataTypeDateTime;

    /**
     * Map property expirationDate
     *
     * @return CFHIRDataTypeDateTime|null
     */
    public function mapExpirationDate(): ?CFHIRDataTypeDateTime;

    /**
     * Map property lotNumber
     *
     * @return CFHIRDataTypeString|null
     */
    public function mapLotNumber(): ?CFHIRDataTypeString;

    /**
     * Map property serialNumber
     *
     * @return CFHIRDataTypeString|null
     */
    public function mapSerialNumber(): ?CFHIRDataTypeString;

    /**
     * Map property deviceName
     *
     * @return CFHIRDataTypeDeviceDeviceName[]
     */
    public function mapDeviceName(): array;

    /**
     * Map property modelNumber
     *
     * @return CFHIRDataTypeString|null
     */
    public function mapModelNumber(): ?CFHIRDataTypeString;

    /**
     * Map property partNumber
     *
     * @return CFHIRDataTypeString|null
     */
    public function mapPartNumber(): ?CFHIRDataTypeString;

    /**
     * Map property type
     *
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function mapType(): ?CFHIRDataTypeCodeableConcept;

    /**
     * Map property specialization
     *
     * @return CFHIRDataTypeDeviceSpecialization[]
     */
    public function mapSpecialization(): array;

    /**
     * Map property version
     *
     * @return CFHIRDataTypeDeviceVersion[]
     */
    public function mapVersion(): array;

    /**
     * Map property property
     *
     * @return CFHIRDataTypeDeviceProperty[]
     */
    public function mapProperty(): array;

    /**
     * Map property patient
     *
     * @return CFHIRDataTypeReference|null
     */
    public function mapPatient(): ?CFHIRDataTypeReference;

    /**
     * Map property owner
     *
     * @return CFHIRDataTypeReference|null
     */
    public function mapOwner(): ?CFHIRDataTypeReference;

    /**
     * Map property contact
     *
     * @return CFHIRDataTypeContactPoint[]
     */
    public function mapContact(): array;

    /**
     * Map property location
     *
     * @return CFHIRDataTypeReference|null
     */
    public function mapLocation(): ?CFHIRDataTypeReference;

    /**
     * Map property url
     *
     * @return CFHIRDataTypeUri|null
     */
    public function mapUrl(): ?CFHIRDataTypeUri;

    /**
     * Map property note
     *
     * @return CFHIRDataTypeAnnotation[]
     */
    public function mapNote(): array;

    /**
     * Map property safety
     *
     * @return CFHIRDataTypeCodeableConcept[]|null
     */
    public function mapSafety(): array;

    /**
     * Map property parent
     *
     * @return CFHIRDataTypeReference|null
     */
    public function mapParent(): ?CFHIRDataTypeReference;
}
