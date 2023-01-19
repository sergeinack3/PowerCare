<?php

/**
 * @package Mediboard\fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Slot;

use Exception;
use Ox\Interop\Fhir\Contracts\Mapping\R4\SlotMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourceSlotInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionHistory;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionRead;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionSearch;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;
use Ox\Interop\Fhir\Utilities\CCapabilitiesResource;

/**
 * Class CFHIRResourceSlot
 * @package Ox\Interop\Fhir\Resources
 */
class CFHIRResourceSlot extends CFHIRDomainResource implements ResourceSlotInterface
{
    // constants
    /** @var string */
    public const RESOURCE_TYPE = "Slot";

    // attributes
    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $serviceCategory = [];

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $serviceType = [];

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $specialty = [];

    protected ?CFHIRDataTypeCodeableConcept $appointmentType = null;

    protected ?CFHIRDataTypeReference $schedule = null;

    protected ?CFHIRDataTypeCode $status = null;

    protected ?CFHIRDataTypeInstant $start = null;

    protected ?CFHIRDataTypeInstant $end = null;

    protected ?CFHIRDataTypeBoolean $overbooked = null;

    protected ?CFHIRDataTypeString $comment = null;

    /** @var SlotMappingInterface */
    public $object_mapping;

    /**
     * @return CCapabilitiesResource
     */
    public function generateCapabilities(): CCapabilitiesResource
    {
        return (parent::generateCapabilities())
            ->setInteractions(
                [
                    CFHIRInteractionRead::NAME,
                    CFHIRInteractionCreate::NAME,
                    CFHIRInteractionSearch::NAME,
                    CFHIRInteractionHistory::NAME,
                ]
            );
    }

    /**
     * @param CFHIRDataTypeCodeableConcept|null $appointmentType
     *
     * @return CFHIRResourceSlot
     */
    public function setAppointmentType(?CFHIRDataTypeCodeableConcept $appointmentType): CFHIRResourceSlot
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
     * @param CFHIRDataTypeReference|null $schedule
     *
     * @return CFHIRResourceSlot
     */
    public function setSchedule(?CFHIRDataTypeReference $schedule): CFHIRResourceSlot
    {
        $this->schedule = $schedule;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getSchedule(): ?CFHIRDataTypeReference
    {
        return $this->schedule;
    }

    /**
     * @param CFHIRDataTypeCode|null $status
     *
     * @return CFHIRResourceSlot
     */
    public function setStatus(?CFHIRDataTypeCode $status): CFHIRResourceSlot
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
     * @param CFHIRDataTypeInstant|null $start
     *
     * @return CFHIRResourceSlot
     */
    public function setStart(?CFHIRDataTypeInstant $start): CFHIRResourceSlot
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
     * @return CFHIRResourceSlot
     */
    public function setEnd(?CFHIRDataTypeInstant $end): CFHIRResourceSlot
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
     * @param CFHIRDataTypeBoolean|null $overbooked
     *
     * @return CFHIRResourceSlot
     */
    public function setOverbooked(?CFHIRDataTypeBoolean $overbooked): CFHIRResourceSlot
    {
        $this->overbooked = $overbooked;

        return $this;
    }

    /**
     * @return CFHIRDataTypeBoolean|null
     */
    public function getOverbooked(): ?CFHIRDataTypeBoolean
    {
        return $this->overbooked;
    }

    /**
     * @param CFHIRDataTypeString|null $comment
     *
     * @return CFHIRResourceSlot
     */
    public function setComment(?CFHIRDataTypeString $comment): CFHIRResourceSlot
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
        $this->appointmentType = $this->object_mapping->mapAppointmentType();
    }

    /**
     * Map property schedule
     */
    protected function mapSchedule(): void
    {
        $this->schedule = $this->object_mapping->mapSchedule();
    }

    /**
     * Map property status
     */
    protected function mapStatus(): void
    {
        $this->status = $this->object_mapping->mapStatus();
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
     * Map property overbooked
     */
    protected function mapOverbooked(): void
    {
        $this->overbooked = $this->object_mapping->mapOverbooked();
    }

    /**
     * Map property comment
     */
    protected function mapComment(): void
    {
        $this->comment = $this->object_mapping->mapComment();
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$specialty
     *
     * @return self
     */
    public function setSpecialty(CFHIRDataTypeCodeableConcept ...$specialty): self
    {
        $this->specialty = $specialty;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$specialty
     *
     * @return self
     */
    public function addSpecialty(CFHIRDataTypeCodeableConcept ...$specialty): self
    {
        $this->specialty = array_merge($this->specialty, $specialty);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function getSpecialty(): array
    {
        return $this->specialty;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$serviceType
     *
     * @return self
     */
    public function setServiceType(CFHIRDataTypeCodeableConcept ...$serviceType): self
    {
        $this->serviceType = $serviceType;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$serviceType
     *
     * @return self
     */
    public function addServiceType(CFHIRDataTypeCodeableConcept ...$serviceType): self
    {
        $this->serviceType = array_merge($this->serviceType, $serviceType);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function getServiceType(): array
    {
        return $this->serviceType;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$serviceCategory
     *
     * @return self
     */
    public function setServiceCategory(CFHIRDataTypeCodeableConcept ...$serviceCategory): self
    {
        $this->serviceCategory = $serviceCategory;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$serviceCategory
     *
     * @return self
     */
    public function addServiceCategory(CFHIRDataTypeCodeableConcept ...$serviceCategory): self
    {
        $this->serviceCategory = array_merge($this->serviceCategory, $serviceCategory);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function getServiceCategory(): array
    {
        return $this->serviceCategory;
    }
}
