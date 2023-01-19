<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Mapping\R4;

use Ox\Interop\Fhir\Contracts\Mapping\ResourceDomainMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * Description
 */
interface ScheduleMappingInterface extends ResourceDomainMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "Schedule";

    /**
     * Map property identifier
     *
     * @return CFHIRDataTypeIdentifier[]
     */
    public function mapIdentifier(): array;

    /**
     * Map property active
     *
     * @return CFHIRDataTypeCode|null
     */
    public function mapActive(): ?CFHIRDataTypeBoolean;

    /**
     * Map property serviceCategory
     *
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function mapServiceCategory(): array;

    /**
     * Map property serviceType
     *
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function mapServiceType(): array;

    /**
     * Map property specialty
     *
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function mapSpecialty(): array;

    /**
     * Map property actor
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapActor(): array;

    /**
     * Map property planningHorizon
     *
     * @return CFHIRDataTypePeriod|null
     */
    public function mapPlanningHorizon(): ?CFHIRDataTypePeriod;

    /**
     * Map property comment
     *
     * @return CFHIRDataTypeString|null
     */
    public function mapComment(): ?CFHIRDataTypeString;
}
