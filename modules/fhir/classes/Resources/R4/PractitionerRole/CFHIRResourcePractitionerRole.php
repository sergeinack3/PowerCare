<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\PractitionerRole;

use Ox\Interop\Fhir\Contracts\Mapping\R4\PractitionerRoleMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourcePractitionerRoleInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\PractitionerRole\CFHIRDataTypePractitionerRoleAvailableTime;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\PractitionerRole\CFHIRDataTypePractitionerRolerNotAvailable;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactPoint;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionDelete;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionRead;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionSearch;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionUpdate;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;
use Ox\Interop\Fhir\Utilities\CCapabilitiesResource;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterString;

/**
 * FHIR practitionerRole resource
 */
class CFHIRResourcePractitionerRole extends CFHIRDomainResource implements ResourcePractitionerRoleInterface
{
    // constants
    /** @var string Resource type */
    public const RESOURCE_TYPE = "PractitionerRole";

    // attributes
    protected ?CFHIRDataTypeBoolean $active = null;

    protected ?CFHIRDataTypePeriod $period = null;

    protected ?CFHIRDataTypeReference $practitioner = null;

    protected ?CFHIRDataTypeReference $organization = null;

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $code = [];

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $specialty = [];

    /** @var CFHIRDataTypeReference[] */
    protected array $location = [];

    /** @var CFHIRDataTypeReference[] */
    protected array $healthcareService = [];

    /** @var CFHIRDataTypeContactPoint[] */
    protected array $telecom = [];

    /** @var CFHIRDataTypePractitionerRoleAvailableTime[] */
    protected array $availableTime = [];

    /** @var CFHIRDataTypePractitionerRolerNotAvailable[] */
    protected array $notAvailable = [];

    protected ?CFHIRDataTypeString $availabilityExceptions = null;

    /** @var CFHIRDataTypeReference[] */
    protected array $endpoint = [];

    /** @var PractitionerRoleMappingInterface */
    public $object_mapping;

    /**
     * @return CCapabilitiesResource
     */
    public function generateCapabilities(): CCapabilitiesResource
    {
        return (parent::generateCapabilities())
            ->addInteractions(
                [
                    CFHIRInteractionRead::NAME,
                    CFHIRInteractionCreate::NAME,
                    CFHIRInteractionSearch::NAME,
                    CFHIRInteractionDelete::NAME,
                    CFHIRInteractionUpdate::NAME,
                ]
            )
            ->addSearchAttributes(
                [
                    new SearchParameterString('specialty'),
                ]
            );
    }

    /**
     * @param CFHIRDataTypeBoolean|null $active
     *
     * @return CFHIRResourcePractitionerRole
     */
    public function setActive(?CFHIRDataTypeBoolean $active): CFHIRResourcePractitionerRole
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
     * @param CFHIRDataTypePeriod|null $period
     *
     * @return CFHIRResourcePractitionerRole
     */
    public function setPeriod(?CFHIRDataTypePeriod $period): CFHIRResourcePractitionerRole
    {
        $this->period = $period;

        return $this;
    }

    /**
     * @return CFHIRDataTypePeriod|null
     */
    public function getPeriod(): ?CFHIRDataTypePeriod
    {
        return $this->period;
    }

    /**
     * @param CFHIRDataTypeReference|null $practitioner
     *
     * @return CFHIRResourcePractitionerRole
     */
    public function setPractitioner(?CFHIRDataTypeReference $practitioner): CFHIRResourcePractitionerRole
    {
        $this->practitioner = $practitioner;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getPractitioner(): ?CFHIRDataTypeReference
    {
        return $this->practitioner;
    }

    /**
     * @param CFHIRDataTypeReference|null $organization
     *
     * @return CFHIRResourcePractitionerRole
     */
    public function setOrganization(?CFHIRDataTypeReference $organization): CFHIRResourcePractitionerRole
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getOrganization(): ?CFHIRDataTypeReference
    {
        return $this->organization;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$code
     *
     * @return CFHIRResourcePractitionerRole
     */
    public function setCode(CFHIRDataTypeCodeableConcept ...$code): CFHIRResourcePractitionerRole
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$code
     *
     * @return CFHIRResourcePractitionerRole
     */
    public function addCode(CFHIRDataTypeCodeableConcept ...$code): CFHIRResourcePractitionerRole
    {
        $this->code = array_merge($this->code, $code);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function getCode(): array
    {
        return $this->code;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$specialty
     *
     * @return CFHIRResourcePractitionerRole
     */
    public function setSpecialty(CFHIRDataTypeCodeableConcept ...$specialty): CFHIRResourcePractitionerRole
    {
        $this->specialty = $specialty;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$specialty
     *
     * @return CFHIRResourcePractitionerRole
     */
    public function addSpecialty(CFHIRDataTypeCodeableConcept ...$specialty): CFHIRResourcePractitionerRole
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
     * @param CFHIRDataTypeReference ...$location
     *
     * @return self
     */
    public function setLocation(CFHIRDataTypeReference ...$location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$location
     *
     * @return self
     */
    public function addLocation(CFHIRDataTypeReference ...$location): self
    {
        $this->location = array_merge($this->location, $location);

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference[]
     */
    public function getLocation(): array
    {
        return $this->location;
    }

    /**
     * @param CFHIRDataTypeReference ...$healthcareService
     *
     * @return self
     */
    public function setHealthcareService(CFHIRDataTypeReference ...$healthcareService): self
    {
        $this->healthcareService = $healthcareService;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$healthcareService
     *
     * @return self
     */
    public function addHealthcareService(CFHIRDataTypeReference ...$healthcareService): self
    {
        $this->healthcareService = array_merge($this->healthcareService, $healthcareService) ;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference[]
     */
    public function getHealthcareService(): array
    {
        return $this->healthcareService;
    }

    /**
     * @param CFHIRDataTypeContactPoint ...$telecom
     *
     * @return self
     */
    public function setTelecom(CFHIRDataTypeContactPoint ...$telecom): self
    {
        $this->telecom = $telecom;

        return $this;
    }

    /**
     * @param CFHIRDataTypeContactPoint ...$telecom
     *
     * @return self
     */
    public function addTelecom(CFHIRDataTypeContactPoint ...$telecom): self
    {
        $this->telecom = array_merge($this->telecom, $telecom);

        return $this;
    }

    /**
     * @return CFHIRDataTypeContactPoint[]
     */
    public function getTelecom(): array
    {
        return $this->telecom;
    }

    /**
     * @param CFHIRDataTypePractitionerRoleAvailableTime ...$availableTime
     *
     * @return self
     */
    public function setAvailableTime(CFHIRDataTypePractitionerRoleAvailableTime ...$availableTime): self
    {
        $this->availableTime = $availableTime;

        return $this;
    }

    /**
     * @param CFHIRDataTypePractitionerRoleAvailableTime ...$availableTime
     *
     * @return self
     */
    public function addAvailableTime(CFHIRDataTypePractitionerRoleAvailableTime ...$availableTime): self
    {
        $this->availableTime = array_merge($this->availableTime, $availableTime);

        return $this;
    }

    /**
     * @return CFHIRDataTypePractitionerRoleAvailableTime[]
     */
    public function getAvailableTime(): array
    {
        return $this->availableTime;
    }

    /**
     * @param CFHIRDataTypePractitionerRolerNotAvailable ...$notAvailable
     *
     * @return self
     */
    public function setNotAvailable(CFHIRDataTypePractitionerRolerNotAvailable ...$notAvailable): self
    {
        $this->notAvailable = $notAvailable;

        return $this;
    }

    /**
     * @param CFHIRDataTypePractitionerRolerNotAvailable ...$notAvailable
     *
     * @return self
     */
    public function addNotAvailable(CFHIRDataTypePractitionerRolerNotAvailable ...$notAvailable): self
    {
        $this->notAvailable = array_merge($this->notAvailable, $notAvailable);

        return $this;
    }

    /**
     * @return CFHIRDataTypePractitionerRolerNotAvailable[]
     */
    public function getNotAvailable(): array
    {
        return $this->notAvailable;
    }

    /**
     * @param CFHIRDataTypeString|null $availabilityExceptions
     *
     * @return CFHIRResourcePractitionerRole
     */
    public function setAvailabilityExceptions(
        ?CFHIRDataTypeString $availabilityExceptions
    ): CFHIRResourcePractitionerRole {
        $this->availabilityExceptions = $availabilityExceptions;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$endpoint
     *
     * @return self
     */
    public function setEndpoint(CFHIRDataTypeReference ...$endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$endpoint
     *
     * @return self
     */
    public function addEndpoint(CFHIRDataTypeReference ...$endpoint): self
    {
        $this->endpoint = array_merge($this->endpoint, $endpoint);

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference[]
     */
    public function getEndpoint(): array
    {
        return $this->endpoint;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getAvailabilityExceptions(): ?CFHIRDataTypeString
    {
        return $this->availabilityExceptions;
    }

    /**
     * Map property active
     */
    protected function mapActive(): void
    {
        $this->active = $this->object_mapping->mapActive();
    }

    /**
     * Map property period
     */
    protected function mapPeriod(): void
    {
        $this->period = $this->object_mapping->mapPeriod();
    }

    /**
     * Map property practitioner
     */
    protected function mapPractitioner(): void
    {
        $this->practitioner = $this->object_mapping->mapPractitioner();
    }

    /**
     * Map property organization
     */
    protected function mapOrganization(): void
    {
        $this->organization = $this->object_mapping->mapOrganization();
    }

    /**
     * Map property code
     */
    protected function mapCode(): void
    {
        $this->code = $this->object_mapping->mapCode();
    }

    /**
     * Map property speciality
     */
    protected function mapSpecialty(): void
    {
        $this->specialty = $this->object_mapping->mapSpecialty();
    }

    /**
     * Map property location
     */
    protected function mapLocation(): void
    {
        $this->location = $this->object_mapping->mapLocation();
    }

    /**
     * Map property healthcareService
     */
    protected function mapHealthCareService(): void
    {
        $this->healthcareService = $this->object_mapping->mapHealthCareService();
    }

    /**
     * Map property telecom
     */
    protected function mapTelecom(): void
    {
        $this->telecom = $this->object_mapping->mapTelecom();
    }

    /**
     * Map property availableTime
     */
    protected function mapAvailableTime(): void
    {
        $this->availableTime = $this->object_mapping->mapAvailableTime();
    }

    /**
     * Map property notAvailable
     */
    protected function mapNotAvailable(): void
    {
        $this->notAvailable = $this->object_mapping->mapNotAvailable();
    }

    /**
     * Map property availabilityExceptions
     */
    protected function mapAvailabilityExceptions(): void
    {
        $this->availabilityExceptions = $this->object_mapping->mapAvailabilityExceptions();
    }

    /**
     * Map property endpoint
     */
    protected function mapEndpoint(): void
    {
        $this->endpoint = $this->object_mapping->mapEndpoint();
    }
}
