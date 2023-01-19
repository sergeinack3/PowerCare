<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Mapping\R4;

use Ox\Interop\Fhir\Contracts\Mapping\ResourceDomainMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * Description
 */
interface SlotMappingInterface extends ResourceDomainMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "Slot";

    /**
     * Map property identifier
     *
     * @return CFHIRDataTypeIdentifier[]
     */
    public function mapIdentifier(): array;

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
     * Map property appointment
     *
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function mapAppointmentType(): ?CFHIRDataTypeCodeableConcept;

    /**
     * Map property schedule
     *
     * @return CFHIRDataTypeReference
     */
    public function mapSchedule(): ?CFHIRDataTypeReference;

    /**
     * Map property status
     *
     * @return CFHIRDataTypeCode|null
     */
    public function mapStatus(): ?CFHIRDataTypeCode;

    /**
     * Map property start
     *
     * @return CFHIRDataTypeInstant|null
     */
    public function mapStart(): ?CFHIRDataTypeInstant;

    /**
     * Map property end
     *
     * @return CFHIRDataTypeInstant|null
     */
    public function mapEnd(): ?CFHIRDataTypeInstant;

    /**
     * Map property overbooked
     *
     * @return CFHIRDataTypeBoolean|null
     */
    public function mapOverbooked(): ?CFHIRDataTypeBoolean;

    /**
     * Map property comment
     *
     * @return CFHIRDataTypeString|null
     */
    public function mapComment(): ?CFHIRDataTypeString;
}
