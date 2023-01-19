<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Mapping\R4;

use Ox\Interop\Fhir\Contracts\Mapping\ResourceDomainMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Location\CFHIRDataTypeLocationHoursOfOperation;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Location\CFHIRDataTypeLocationPosition;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAddress;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactPoint;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * Description
 */
interface LocationMappingInterface extends ResourceDomainMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "Location";

    /**
     * Map property status
     *
     * @return CFHIRDataTypeCode|null
     */
    public function mapStatus(): ?CFHIRDataTypeCode;

    /**
     * @return CFHIRDataTypeIdentifier[]
     */
    public function mapIdentifier(): array;

    /**
     * Map property operationStatus
     *
     * @return CFHIRDataTypeCoding|null
     */
    public function mapOperationalStatus(): ?CFHIRDataTypeCoding;

    /**
     * Map property name
     *
     * @return CFHIRDataTypeString|null
     */
    public function mapName(): ?CFHIRDataTypeString;

    /**
     * Map property alias
     *
     * @return CFHIRDataTypeString[]
     */
    public function mapAlias(): array;

    /**
     * Map property description
     *
     * @return CFHIRDataTypeString|null
     */
    public function mapDescription(): ?CFHIRDataTypeString;

    /**
     * Map property code
     *
     * @return CFHIRDataTypeCode|null
     */
    public function mapMode(): ?CFHIRDataTypeCode;

    /**
     * Map property type
     *
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function mapType(): array;

    /**
     * Map property telecom
     *
     * @return CFHIRDataTypeContactPoint[]
     */
    public function mapTelecom(): array;

    /**
     * Map property address
     *
     * @return CFHIRDataTypeAddress|null
     */
    public function mapAddress(): ?CFHIRDataTypeAddress;

    /**
     * Map property physicalType
     *
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function mapPhysicalType(): ?CFHIRDataTypeCodeableConcept;

    /**
     * Map property position
     *
     * @return CFHIRDataTypeLocationPosition|null
     */
    public function mapPosition(): ?CFHIRDataTypeLocationPosition;

    /**
     * Map property managingOrganization
     *
     * @return CFHIRDataTypeReference|null
     */
    public function mapManagingOrganization(): ?CFHIRDataTypeReference;

    /**
     * Map property partOf
     *
     * @return CFHIRDataTypeReference|null
     */
    public function mapPartOf(): ?CFHIRDataTypeReference;

    /**
     * Map property hoursOfOperation
     *
     * @return CFHIRDataTypeLocationHoursOfOperation[]
     */
    public function mapHoursOfOperation(): array;

    /**
     * Map property availabilityExceptions
     *
     * @return CFHIRDataTypeString|null
     */
    public function mapAvailabilityExceptions(): ?CFHIRDataTypeString;

    /**
     * Map property endpoint
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapEndpoint(): array;
}
