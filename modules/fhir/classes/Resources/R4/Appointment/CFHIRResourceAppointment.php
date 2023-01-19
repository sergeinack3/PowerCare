<?php

/**
 * @package Mediboard\fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Appointment;

use Exception;
use Ox\Interop\Fhir\Contracts\Mapping\R4\AppointmentMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourceAppointmentInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypePositiveInt;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUnsignedInt;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Appointment\CFHIRDataTypeAppointmentParticipant;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionDelete;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionRead;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionSearch;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionUpdate;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;
use Ox\Interop\Fhir\Resources\R4\Slot\CFHIRResourceSlot;
use Ox\Interop\Fhir\Utilities\CCapabilitiesResource;

/**
 * Description
 */
class CFHIRResourceAppointment extends CFHIRDomainResource implements ResourceAppointmentInterface
{
    // constants
    /** @var string */
    public const RESOURCE_TYPE = 'Appointment';

    // attributes
    protected ?CFHIRDataTypeCode $status = null;

    protected ?CFHIRDataTypeCodeableConcept $cancelationReason = null;

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $serviceCategory = [];

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $serviceType = [];

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $specialty = [];

    protected ?CFHIRDataTypeCodeableConcept $appointmentType = null;

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $reasonCode = [];

    /** @var CFHIRDataTypeReference[] */
    protected array $reasonReference = [];

    protected ?CFHIRDataTypeUnsignedInt $priority = null;

    protected ?CFHIRDataTypeString $description = null;

    /** @var CFHIRDataTypeReference[] */
    protected array $supportingInformation = [];

    protected ?CFHIRDataTypeInstant $start = null;

    protected ?CFHIRDataTypeInstant $end = null;

    protected ?CFHIRDataTypePositiveInt $minutesDuration = null;

    /** @var CFHIRDataTypeReference[] */
    protected array $slot = [];

    protected ?CFHIRDataTypeDateTime $created = null;

    protected ?CFHIRDataTypeString $comment = null;

    protected ?CFHIRDataTypeString $patientInstruction = null;

    /** @var CFHIRDataTypeReference[] */
    protected array $basedOn = [];

    /** @var CFHIRDataTypeAppointmentParticipant[] */
    protected array $participant = [];

    /** @var CFHIRDataTypePeriod[] */
    protected array $requestedPeriod = [];

    /** @var AppointmentMappingInterface */
    protected $object_mapping;

    /**
     * @return CCapabilitiesResource
     */
    public function generateCapabilities(): CCapabilitiesResource
    {
        return (parent::generateCapabilities())
            ->addInteractions(
                [
                    CFHIRInteractionCreate::NAME,
                    CFHIRInteractionRead::NAME,
                    CFHIRInteractionSearch::NAME,
                    CFHIRInteractionUpdate::NAME,
                    CFHIRInteractionDelete::NAME,
                ]
            );
    }

    /**
     * @return CFHIRDataTypeAppointmentParticipant[]
     */
    public function getParticipant(): array
    {
        return $this->participant ?? [];
    }

    /**
     * @param CFHIRDataTypeAppointmentParticipant ...$participant
     *
     * @return CFHIRResourceAppointment
     */
    public function setParticipant(CFHIRDataTypeAppointmentParticipant ...$participant): CFHIRResourceAppointment
    {
        $this->participant = $participant;

        return $this;
    }

    /**
     * @param CFHIRDataTypeAppointmentParticipant ...$participant
     *
     * @return CFHIRResourceAppointment
     */
    public function addParticipant(CFHIRDataTypeAppointmentParticipant ...$participant): CFHIRResourceAppointment
    {
        $this->participant = array_merge($this->participant ?? [], $participant);

        return $this;
    }

    /**
     * @param CFHIRDataTypeCode|null $status
     *
     * @return CFHIRResourceAppointment
     */
    public function setStatus(?CFHIRDataTypeCode $status): CFHIRResourceAppointment
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function getStatus(): ?CFHIRDataTypeCode
    {
        return $this->status;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept|null $cancelationReason
     *
     * @return CFHIRResourceAppointment
     */
    public function setCancelationReason(?CFHIRDataTypeCodeableConcept $cancelationReason): CFHIRResourceAppointment
    {
        $this->cancelationReason = $cancelationReason;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function getCancelationReason(): ?CFHIRDataTypeCodeableConcept
    {
        return $this->cancelationReason;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$serviceCategory
     *
     * @return CFHIRResourceAppointment
     */
    public function setServiceCategory(CFHIRDataTypeCodeableConcept ...$serviceCategory): CFHIRResourceAppointment
    {
        $this->serviceCategory = $serviceCategory;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$serviceCategory
     *
     * @return CFHIRResourceAppointment
     */
    public function addServiceCategory(CFHIRDataTypeCodeableConcept ...$serviceCategory): CFHIRResourceAppointment
    {
        $this->serviceCategory = array_merge($this->serviceCategory ?? [], $serviceCategory);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function getServiceCategory(): array
    {
        return $this->serviceCategory ?? [];
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$serviceType
     *
     * @return CFHIRResourceAppointment
     */
    public function setServiceType(CFHIRDataTypeCodeableConcept ...$serviceType): CFHIRResourceAppointment
    {
        $this->serviceType = $serviceType;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$serviceType
     *
     * @return CFHIRResourceAppointment
     */
    public function addServiceType(CFHIRDataTypeCodeableConcept ...$serviceType): CFHIRResourceAppointment
    {
        $this->serviceType = array_merge($this->serviceType ?? [], $serviceType);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function getServiceType(): array
    {
        return $this->serviceType ?? [];
    }

    /**
     * @param CFHIRDataTypeCodeableConcept[] $specialty
     *
     * @return CFHIRResourceAppointment
     */
    public function setSpecialty(CFHIRDataTypeCodeableConcept ...$specialty): CFHIRResourceAppointment
    {
        $this->specialty = $specialty === null ? [] : $specialty;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept[] $specialty
     *
     * @return CFHIRResourceAppointment
     */
    public function addSpecialty(CFHIRDataTypeCodeableConcept ...$specialty): CFHIRResourceAppointment
    {
        $this->specialty = array_merge($this->specialty ?? [], $specialty);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function getSpecialty(): array
    {
        return $this->specialty ?? [];
    }

    /**
     * @param CFHIRDataTypeCodeableConcept|null $appointmentType
     *
     * @return CFHIRResourceAppointment
     */
    public function setAppointmentType(?CFHIRDataTypeCodeableConcept $appointmentType): CFHIRResourceAppointment
    {
        $this->appointmentType = $appointmentType;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function getAppointmentType(): ?CFHIRDataTypeCodeableConcept
    {
        return $this->appointmentType;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept[] $reasonCode
     *
     * @return CFHIRResourceAppointment
     */
    public function setReasonCode(CFHIRDataTypeCodeableConcept ...$reasonCode): CFHIRResourceAppointment
    {
        $this->reasonCode = $reasonCode;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept[] $reasonCode
     *
     * @return CFHIRResourceAppointment
     */
    public function addReasonCode(CFHIRDataTypeCodeableConcept ...$reasonCode): CFHIRResourceAppointment
    {
        $this->reasonCode = array_merge($this->reasonCode ?? [], $reasonCode);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function getReasonCode(): array
    {
        return $this->reasonCode ?? [];
    }

    /**
     * @param CFHIRDataTypeReference[] $reasonReference
     *
     * @return CFHIRResourceAppointment
     */
    public function setReasonReference(CFHIRDataTypeReference ...$reasonReference): CFHIRResourceAppointment
    {
        $this->reasonReference = $reasonReference;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference[] $reasonReference
     *
     * @return CFHIRResourceAppointment
     */
    public function addReasonReference(CFHIRDataTypeReference ...$reasonReference): CFHIRResourceAppointment
    {
        $this->reasonReference = array_merge($this->reasonReference ?? [], $reasonReference);

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference[]
     */
    public function getReasonReference(): array
    {
        return $this->reasonReference ?? [];
    }

    /**
     * @param CFHIRDataTypeUnsignedInt|null $priority
     *
     * @return CFHIRResourceAppointment
     */
    public function setPriority(?CFHIRDataTypeUnsignedInt $priority): CFHIRResourceAppointment
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return CFHIRDataTypeUnsignedInt|null
     */
    public function getPriority(): ?CFHIRDataTypeUnsignedInt
    {
        return $this->priority;
    }

    /**
     * @param CFHIRDataTypeString|null $description
     *
     * @return CFHIRResourceAppointment
     */
    public function setDescription(?CFHIRDataTypeString $description): CFHIRResourceAppointment
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getDescription(): ?CFHIRDataTypeString
    {
        return $this->description;
    }

    /**
     * @param CFHIRDataTypeReference ...$supportingInformation
     *
     * @return CFHIRResourceAppointment
     */
    public function setSupportingInformation(CFHIRDataTypeReference ...$supportingInformation): CFHIRResourceAppointment
    {
        $this->supportingInformation = $supportingInformation;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$supportingInformation
     *
     * @return CFHIRResourceAppointment
     */
    public function addSupportingInformation(CFHIRDataTypeReference ...$supportingInformation): CFHIRResourceAppointment
    {
        $this->supportingInformation = array_merge($this->supportingInformation ?? [], $supportingInformation);

        return $this;
    }

    /**
     * @param CFHIRDataTypeInstant|null $start
     *
     * @return CFHIRResourceAppointment
     */
    public function setStart(?CFHIRDataTypeInstant $start): CFHIRResourceAppointment
    {
        $this->start = $start;

        return $this;
    }

    /**
     * @return CFHIRDataTypeInstant|null
     */
    public function getStart(): ?CFHIRDataTypeInstant
    {
        return $this->start;
    }

    /**
     * @param CFHIRDataTypeInstant|null $end
     *
     * @return CFHIRResourceAppointment
     */
    public function setEnd(?CFHIRDataTypeInstant $end): CFHIRResourceAppointment
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return CFHIRDataTypeInstant|null
     */
    public function getEnd(): ?CFHIRDataTypeInstant
    {
        return $this->end;
    }

    /**
     * @param CFHIRDataTypePositiveInt|null $minutesDuration
     *
     * @return CFHIRResourceAppointment
     */
    public function setMinutesDuration(?CFHIRDataTypePositiveInt $minutesDuration): CFHIRResourceAppointment
    {
        $this->minutesDuration = $minutesDuration;

        return $this;
    }

    /**
     * @return CFHIRDataTypePositiveInt|null
     */
    public function getMinutesDuration(): ?CFHIRDataTypePositiveInt
    {
        return $this->minutesDuration;
    }

    /**
     * @param CFHIRDataTypeReference ...$slot
     *
     * @return CFHIRResourceAppointment
     */
    public function setSlot(CFHIRDataTypeReference ...$slot): CFHIRResourceAppointment
    {
        $this->slot = $slot;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$slot
     *
     * @return CFHIRResourceAppointment
     */
    public function addSlot(CFHIRDataTypeReference ...$slot): CFHIRResourceAppointment
    {
        $this->slot = array_merge($this->slot ?? [], $slot);

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference[]
     */
    public function getSlot(): array
    {
        return $this->slot ?? [];
    }

    /**
     * @param CFHIRDataTypeDateTime|null $created
     *
     * @return CFHIRResourceAppointment
     */
    public function setCreated(?CFHIRDataTypeDateTime $created): CFHIRResourceAppointment
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return CFHIRDataTypeDateTime|null
     */
    public function getCreated(): ?CFHIRDataTypeDateTime
    {
        return $this->created;
    }

    /**
     * @param CFHIRDataTypeString|null $comment
     *
     * @return CFHIRResourceAppointment
     */
    public function setComment(?CFHIRDataTypeString $comment): CFHIRResourceAppointment
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getComment(): ?CFHIRDataTypeString
    {
        return $this->comment;
    }

    /**
     * @param CFHIRDataTypeString|null $patientInstruction
     *
     * @return CFHIRResourceAppointment
     */
    public function setPatientInstruction(?CFHIRDataTypeString $patientInstruction): CFHIRResourceAppointment
    {
        $this->patientInstruction = $patientInstruction;

        return $this;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getPatientInstruction(): ?CFHIRDataTypeString
    {
        return $this->patientInstruction;
    }

    /**
     * @param CFHIRDataTypeReference ...$basedOn
     *
     * @return CFHIRResourceAppointment
     */
    public function setBasedOn(CFHIRDataTypeReference ...$basedOn): CFHIRResourceAppointment
    {
        $this->basedOn = $basedOn;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$basedOn
     *
     * @return CFHIRResourceAppointment
     */
    public function addBasedOn(CFHIRDataTypeReference ...$basedOn): CFHIRResourceAppointment
    {
        $this->basedOn = array_merge($this->basedOn ?? [], $basedOn);

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference[]
     */
    public function getBasedOn(): array
    {
        return $this->basedOn ?? [];
    }

    /**
     * @param CFHIRDataTypePeriod ...$requestedPeriod
     *
     * @return CFHIRResourceAppointment
     */
    public function setRequestedPeriod(CFHIRDataTypePeriod ...$requestedPeriod): CFHIRResourceAppointment
    {
        $this->requestedPeriod = $requestedPeriod;

        return $this;
    }

    /**
     * @param CFHIRDataTypePeriod ...$requestedPeriod
     *
     * @return CFHIRResourceAppointment
     */
    public function addRequestedPeriod(CFHIRDataTypePeriod ...$requestedPeriod): CFHIRResourceAppointment
    {
        $this->requestedPeriod = array_merge($this->requestedPeriod ?? [], $requestedPeriod);

        return $this;
    }

    /**
     * @return CFHIRDataTypePeriod[]
     */
    public function getRequestedPeriod(): array
    {
        return $this->requestedPeriod ?? [];
    }

    /**
     * @return CFHIRDataTypeReference[]
     */
    public function getSupportingInformation(): array
    {
        return $this->supportingInformation ?? [];
    }

    /**
     * Map property status
     */
    protected function mapStatus(): void
    {
        $this->status = $this->object_mapping->mapStatus();
    }

    /**
     * Map property cancelationReason
     */
    protected function mapCancelationReason(): void
    {
        $this->cancelationReason = $this->object_mapping->mapCancelationReason();
    }

    /**
     * Map property serviceCategory
     */
    protected function mapServiceCategory(): void
    {
        $this->serviceCategory = $this->object_mapping->mapServiceCategory();
    }

    /**
     * Map property serviceType
     * @throws Exception
     */
    protected function mapServiceType(): void
    {
        $this->serviceType = $this->object_mapping->mapServiceType();
    }

    /**
     * Map property specialty
     */
    protected function mapSpecialty(): void
    {
        $this->specialty = $this->object_mapping->mapSpecialty();
    }

    /**
     * Map property appointmentType
     */
    protected function mapAppointmentType(): void
    {
        // not implemented
        $this->appointmentType = $this->object_mapping->mapAppointmentType();
    }

    /**
     * Map property reasonCode
     */
    protected function mapReasonCode(): void
    {
        // not implemented
        $this->reasonCode = $this->object_mapping->mapReasonCode();
    }

    /**
     * Map property reasonReference
     */
    protected function mapReasonReference(): void
    {
        $this->reasonReference = $this->object_mapping->mapReasonReference();
    }

    /**
     * Map property priority
     */
    protected function mapPriority(): void
    {
        $this->priority = $this->object_mapping->mapPriority();
    }

    /**
     * Map property description
     */
    protected function mapDescription(): void
    {
        $this->description = $this->object_mapping->mapDescription();
    }

    /**
     * Map property supportingInformation
     */
    protected function mapSupportingInformation(): void
    {
        $this->supportingInformation = $this->object_mapping->mapSupportingInformation();
    }

    /**
     * Map property start
     */
    protected function mapStart(): void
    {
        $this->start = $this->object_mapping->mapStart();
    }

    /**
     * Map property end
     */
    protected function mapEnd(): void
    {
        $this->end = $this->object_mapping->mapEnd();
    }

    /**
     * Map property minutesDuration
     */
    protected function mapMinutesDuration(): void
    {
        $this->minutesDuration = $this->object_mapping->mapMinutesDuration();
    }

    /**
     * Map property slot
     */
    protected function mapSlot(string $resource_class = CFHIRResourceSlot::class): void
    {
        $this->slot = $this->object_mapping->mapSlot($resource_class);
    }

    /**
     * Map property created
     */
    protected function mapCreated(): void
    {
        $this->created = $this->object_mapping->mapCreated();
    }

    /**
     * Map property comment
     */
    protected function mapComment(): void
    {
        $this->comment = $this->object_mapping->mapComment();
    }

    /**
     * Map property patientInstruction
     */
    protected function mapPatientInstruction(): void
    {
        $this->patientInstruction = $this->object_mapping->mapPatientInstruction();
    }

    /**
     * Map property basedOn
     */
    protected function mapBasedOn(): void
    {
        $this->basedOn = $this->object_mapping->mapBasedOn();
    }

    /**
     * Map property participant
     */
    protected function mapParticipant(): void
    {
        $this->participant = $this->object_mapping->mapParticipant();
    }

    /**
     * Map property requestedPeriod
     * @throws Exception
     */
    protected function mapRequestedPeriod(): void
    {
        $this->requestedPeriod = $this->object_mapping->mapRequestedPeriod();
    }
}
