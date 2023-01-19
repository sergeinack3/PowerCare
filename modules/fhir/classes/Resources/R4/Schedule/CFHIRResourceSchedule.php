<?php

/**
 * @package Mediboard\fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Schedule;

use Exception;
use Ox\Interop\Fhir\Contracts\Mapping\R4\ScheduleMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourceScheduleInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionHistory;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionRead;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionSearch;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;
use Ox\Interop\Fhir\Utilities\CCapabilitiesResource;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterDate;

/**
 * @package Mediboard\fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Class CFHIRResourceSchedule
 */
class CFHIRResourceSchedule extends CFHIRDomainResource implements ResourceScheduleInterface
{
    // constants
    /** @var string */
    public const RESOURCE_TYPE = 'Schedule';

    // attributes
    protected ?CFHIRDataTypeBoolean $active = null;

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $serviceCategory = [];

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $serviceType = [];

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $specialty = [];

    /** @var CFHIRDataTypeReference[] */
    protected array $actor = [];

    protected ?CFHIRDataTypePeriod $planningHorizon = null;

    protected ?CFHIRDataTypeString $comment = null;

    /** @var ScheduleMappingInterface */
    protected $object_mapping;

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
            )
            ->addSearchAttributes(
                [
                    new SearchParameterDate('start'),
                ]
            );
    }

    /**
     * @param CFHIRDataTypeBoolean|null $active
     *
     * @return CFHIRResourceSchedule
     */
    public function setActive(?CFHIRDataTypeBoolean $active): CFHIRResourceSchedule
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return CFHIRDataTypeBoolean|null
     */
    public function getActive(): ?CFHIRDataTypeBoolean
    {
        return $this->active;
    }

    /**
     * @param CFHIRDataTypePeriod|null $planningHorizon
     *
     * @return CFHIRResourceSchedule
     */
    public function setPlanningHorizon(?CFHIRDataTypePeriod $planningHorizon): CFHIRResourceSchedule
    {
        $this->planningHorizon = $planningHorizon;

        return $this;
    }

    /**
     * @return CFHIRDataTypePeriod|null
     */
    public function getPlanningHorizon(): ?CFHIRDataTypePeriod
    {
        return $this->planningHorizon;
    }

    /**
     * @param CFHIRDataTypeString|null $comment
     *
     * @return CFHIRResourceSchedule
     */
    public function setComment(?CFHIRDataTypeString $comment): CFHIRResourceSchedule
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
     * Map property active
     */
    protected function mapActive(): void
    {
        // not implemented
        $this->active = $this->object_mapping->mapActive();
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
     * Map property actor
     * @throws Exception
     */
    protected function mapActor(): void
    {
        $this->actor = $this->object_mapping->mapActor();
    }

    /**
     * Map property planningHorizon
     * @throws Exception
     */
    protected function mapPlanningHorizon(): void
    {
        $this->planningHorizon = $this->object_mapping->mapPlanningHorizon();
    }

    /**
     * Map property comment
     */
    protected function mapComment(): void
    {
        $this->comment = $this->object_mapping->mapComment();
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
     * @param CFHIRDataTypeReference ...$actor
     *
     * @return self
     */
    public function setActor(CFHIRDataTypeReference ...$actor): self
    {
        $this->actor = $actor;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$actor
     *
     * @return self
     */
    public function addActor(CFHIRDataTypeReference ...$actor): self
    {
        $this->actor = array_merge($this->actor, $actor);

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference[]
     */
    public function getActor(): array
    {
        return $this->actor;
    }
}
