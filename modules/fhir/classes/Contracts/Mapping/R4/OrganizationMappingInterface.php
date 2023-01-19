<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Mapping\R4;

use Ox\Interop\Fhir\Contracts\Mapping\ResourceDomainMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Organization\CFHIRDataTypeOrganizationContact;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAddress;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactPoint;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * Description
 */
interface OrganizationMappingInterface extends ResourceDomainMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "Organization";

    /**
     * @return CFHIRDataTypeIdentifier[]
     */
    public function mapIdentifier(): array;

    /**
     * Map property active
     *
     * @return CFHIRDataTypeBoolean
     */
    public function mapActive(): ?CFHIRDataTypeBoolean;

    /**
     * Map property type
     *
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function mapType(): array;

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
     * Map property telecom
     *
     * @return CFHIRDataTypeContactPoint[]
     */
    public function mapTelecom(): array;

    /**
     * Map property address
     *
     * @return CFHIRDataTypeAddress[]
     */
    public function mapAddress(): array;

    /**
     * Map property partOf
     *
     * @return CFHIRDataTypeReference|null
     */
    public function mapPartOf(): ?CFHIRDataTypeReference;

    /**
     * Map property contact
     *
     * @return CFHIRDataTypeOrganizationContact[]
     */
    public function mapContact(): array;

    /**
     * Map property endpoint
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapEndpoint(): array;
}
