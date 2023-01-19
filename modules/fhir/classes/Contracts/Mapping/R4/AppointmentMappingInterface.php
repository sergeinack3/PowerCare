<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Mapping\R4;

use Ox\Interop\Fhir\Contracts\Mapping\ResourceDomainMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypePositiveInt;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUnsignedInt;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Appointment\CFHIRDataTypeAppointmentParticipant;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * Description
 */
interface AppointmentMappingInterface extends ResourceDomainMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "Appointment";

    /**
     * @return CFHIRDataTypeIdentifier[]
     */
    public function mapIdentifier(): array;

    /**
     * Map property status
     *
     * @return CFHIRDataTypeCode|null
     */
    public function mapStatus(): ?CFHIRDataTypeCode;

    /**
     * Map property cancelationReason
     *
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function mapCancelationReason(): ?CFHIRDataTypeCodeableConcept;

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
     * Map property appointmentType
     *
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function mapAppointmentType(): ?CFHIRDataTypeCodeableConcept;

    /**
     * Map property reasonCode
     *
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function mapReasonCode(): array;

    /**
     * Map property reasonReference
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapReasonReference(): array;

    /**
     * Map property priority
     *
     * @return CFHIRDataTypeUnsignedInt|null
     */
    public function mapPriority(): ?CFHIRDataTypeUnsignedInt;

    /**
     * Map property description
     *
     * @return CFHIRDataTypeString|null
     */
    public function mapDescription(): ?CFHIRDataTypeString;

    /**
     * Map property supportingInformation
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapSupportingInformation(): array;

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
     * Map property minutesDuration
     *
     * @return CFHIRDataTypePositiveInt|null
     */
    public function mapMinutesDuration(): ?CFHIRDataTypePositiveInt;

    /**
     * Map property slot
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapSlot(string $resource_class): array;

    /**
     * Map property created
     *
     * @return CFHIRDataTypeDateTime|null
     */
    public function mapCreated(): ?CFHIRDataTypeDateTime;

    /**
     * Map property comment
     *
     * @return CFHIRDataTypeString|null
     */
    public function mapComment(): ?CFHIRDataTypeString;

    /**
     * Map property patientInstruction
     *
     * @return CFHIRDataTypeString|null
     */
    public function mapPatientInstruction(): ?CFHIRDataTypeString;

    /**
     * Map property baseOn
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapBasedOn(): array;

    /**
     * Map property participant
     *
     * @return CFHIRDataTypeAppointmentParticipant[]
     */
    public function mapParticipant(): array;

    /**
     * Map property requestedPeriod
     *
     * @return CFHIRDataTypePeriod[]
     */
    public function mapRequestedPeriod(): array;
}
