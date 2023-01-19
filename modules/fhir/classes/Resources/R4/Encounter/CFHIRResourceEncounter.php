<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Encounter;

use Ox\Interop\Fhir\Contracts\Mapping\R4\EncounterMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourceEncounterInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Encounter\CFHIRDataTypeEncounterClassHistory;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Encounter\CFHIRDataTypeEncounterDiagnosis;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Encounter\CFHIRDataTypeEncounterHospitalization;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Encounter\CFHIRDataTypeEncounterLocation;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Encounter\CFHIRDataTypeEncounterParticipant;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Encounter\CFHIRDataTypeEncounterStatusHistory;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeDuration;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionRead;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionSearch;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;
use Ox\Interop\Fhir\Utilities\CCapabilitiesResource;

/**
 * FHIR encounter resource
 */
class CFHIRResourceEncounter extends CFHIRDomainResource implements ResourceEncounterInterface
{
    // constants
    /** @var string */
    public const RESOURCE_TYPE = 'Encounter';

    protected ?CFHIRDataTypeCode $status = null;

    /** @var CFHIRDataTypeEncounterStatusHistory[] */
    protected array $statusHistory = [];

    protected ?CFHIRDataTypeCoding $class = null;

    /** @var CFHIRDataTypeEncounterClassHistory[] */
    protected array $classHistory = [];

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $type = [];

    protected ?CFHIRDataTypeCodeableConcept $serviceType = null;

    protected ?CFHIRDataTypeCodeableConcept $priority = null;

    protected ?CFHIRDataTypeReference $subject = null;

    /** @var CFHIRDataTypeReference[] */
    protected array $episodeOfCare = [];

    /** @var CFHIRDataTypeReference[] */
    protected array $basedOn = [];

    /** @var CFHIRDataTypeEncounterParticipant[] */
    protected array $participant = [];

    /** @var CFHIRDataTypeReference[] */
    protected array $appointment = [];

    protected ?CFHIRDataTypePeriod $period = null;

    protected ?CFHIRDataTypeDuration $length = null;

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $reasonCode = [];

    /** @var CFHIRDataTypeReference[] */
    protected array $reasonReference = [];

    /** @var CFHIRDataTypeEncounterDiagnosis[] */
    protected array $diagnosis = [];

    /** @var CFHIRDataTypeReference[] */
    protected array $account = [];

    protected ?CFHIRDataTypeEncounterHospitalization $hospitalization = null;

    /** @var CFHIRDataTypeEncounterLocation[] */
    protected array $location = [];

    protected ?CFHIRDataTypeReference $serviceProvider = null;

    protected ?CFHIRDataTypeReference $partOf = null;

    /** @var EncounterMappingInterface */
    protected $object_mapping;


    /**
     * @return CCapabilitiesResource
     */
    public function generateCapabilities(): CCapabilitiesResource
    {
        return (parent::generateCapabilities())
            ->addInteractions(
                [
                    CFHIRInteractionRead::NAME,
                    CFHIRInteractionSearch::NAME,
                    CFHIRInteractionCreate::NAME,
                ]
            );
    }

    /**
     * @return CFHIRDataTypePeriod|null
     */
    public function getPeriod(): ?CFHIRDataTypePeriod
    {
        return $this->period;
    }

    /**
     * @param CFHIRDataTypeCode|null $status
     *
     * @return CFHIRResourceEncounter
     */
    public function setStatus(?CFHIRDataTypeCode $status): CFHIRResourceEncounter
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
     * @param CFHIRDataTypeEncounterStatusHistory ...$statusHistory
     *
     * @return CFHIRResourceEncounter
     */
    public function setStatusHistory(CFHIRDataTypeEncounterStatusHistory ...$statusHistory): CFHIRResourceEncounter
    {
        $this->statusHistory = $statusHistory;

        return $this;
    }

    /**
     * @param CFHIRDataTypeEncounterStatusHistory ...$statusHistory
     *
     * @return CFHIRResourceEncounter
     */
    public function addStatusHistory(CFHIRDataTypeEncounterStatusHistory ...$statusHistory): CFHIRResourceEncounter
    {
        $this->statusHistory = array_merge($this->statusHistory, $statusHistory);

        return $this;
    }

    /**
     * @return CFHIRDataTypeEncounterStatusHistory[]
     */
    public function getStatusHistory(): array
    {
        return $this->statusHistory;
    }

    /**
     * @param CFHIRDataTypeCoding|null $class
     *
     * @return CFHIRResourceEncounter
     */
    public function setClass(?CFHIRDataTypeCoding $class): CFHIRResourceEncounter
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCoding|null
     */
    public function getClass(): ?CFHIRDataTypeCoding
    {
        return $this->class;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$type
     *
     * @return CFHIRResourceEncounter
     */
    public function setType(CFHIRDataTypeCodeableConcept ...$type): CFHIRResourceEncounter
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$type
     *
     * @return CFHIRResourceEncounter
     */
    public function addType(CFHIRDataTypeCodeableConcept ...$type): CFHIRResourceEncounter
    {
        $this->type = array_merge($this->type, $type);

        return $this;
    }

    /**
     * @return array
     */
    public function getType(): array
    {
        return $this->type;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept|null $serviceType
     *
     * @return CFHIRResourceEncounter
     */
    public function setServiceType(?CFHIRDataTypeCodeableConcept $serviceType): CFHIRResourceEncounter
    {
        $this->serviceType = $serviceType;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function getServiceType(): ?CFHIRDataTypeCodeableConcept
    {
        return $this->serviceType;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept|null $priority
     *
     * @return CFHIRResourceEncounter
     */
    public function setPriority(?CFHIRDataTypeCodeableConcept $priority): CFHIRResourceEncounter
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function getPriority(): ?CFHIRDataTypeCodeableConcept
    {
        return $this->priority;
    }

    /**
     * @param CFHIRDataTypeReference|null $subject
     *
     * @return CFHIRResourceEncounter
     */
    public function setSubject(?CFHIRDataTypeReference $subject): CFHIRResourceEncounter
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getSubject(): ?CFHIRDataTypeReference
    {
        return $this->subject;
    }

    /**
     * @param CFHIRDataTypeReference ...$episodeOfCare
     *
     * @return CFHIRResourceEncounter
     */
    public function setEpisodeOfCare(CFHIRDataTypeReference ...$episodeOfCare): CFHIRResourceEncounter
    {
        $this->episodeOfCare = $episodeOfCare;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$episodeOfCare
     *
     * @return CFHIRResourceEncounter
     */
    public function addEpisodeOfCare(CFHIRDataTypeReference ...$episodeOfCare): CFHIRResourceEncounter
    {
        $this->episodeOfCare = array_merge($this->episodeOfCare, $episodeOfCare);

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference[]
     */
    public function getEpisodeOfCare(): array
    {
        return $this->episodeOfCare;
    }

    /**
     * @param CFHIRDataTypeReference ...$basedOn
     *
     * @return CFHIRResourceEncounter
     */
    public function setBasedOn(CFHIRDataTypeReference ...$basedOn): CFHIRResourceEncounter
    {
        $this->basedOn = $basedOn;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$basedOn
     *
     * @return CFHIRResourceEncounter
     */
    public function addBasedOn(CFHIRDataTypeReference ...$basedOn): CFHIRResourceEncounter
    {
        $this->basedOn = array_merge($this->basedOn, $basedOn);

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference[]
     */
    public function getBasedOn(): array
    {
        return $this->basedOn;
    }

    /**
     * @param CFHIRDataTypeEncounterParticipant ...$participant
     *
     * @return CFHIRResourceEncounter
     */
    public function setParticipant(CFHIRDataTypeEncounterParticipant ...$participant): CFHIRResourceEncounter
    {
        $this->participant = $participant;

        return $this;
    }

    /**
     * @param CFHIRDataTypeEncounterParticipant ...$participant
     *
     * @return CFHIRResourceEncounter
     */
    public function addParticipant(CFHIRDataTypeEncounterParticipant ...$participant): CFHIRResourceEncounter
    {
        $this->participant = array_merge($this->participant, $participant);

        return $this;
    }

    /**
     * @return array
     */
    public function getParticipant(): array
    {
        return $this->participant;
    }

    /**
     * @param CFHIRDataTypeReference ...$appointment
     *
     * @return CFHIRResourceEncounter
     */
    public function setAppointment(CFHIRDataTypeReference ...$appointment): CFHIRResourceEncounter
    {
        $this->appointment = $appointment;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$appointment
     *
     * @return CFHIRResourceEncounter
     */
    public function addAppointment(CFHIRDataTypeReference ...$appointment): CFHIRResourceEncounter
    {
        $this->appointment = array_merge($this->appointment, $appointment);

        return $this;
    }

    /**
     * @return array
     */
    public function getAppointment(): array
    {
        return $this->appointment;
    }

    /**
     * @param CFHIRDataTypePeriod|null $period
     *
     * @return CFHIRResourceEncounter
     */
    public function setPeriod(?CFHIRDataTypePeriod $period): CFHIRResourceEncounter
    {
        $this->period = $period;

        return $this;
    }

    /**
     * @param CFHIRDataTypeDuration|null $length
     *
     * @return CFHIRResourceEncounter
     */
    public function setLength(?CFHIRDataTypeDuration $length): CFHIRResourceEncounter
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @return CFHIRDataTypeDuration|null
     */
    public function getLength(): ?CFHIRDataTypeDuration
    {
        return $this->length;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$reasonCode
     *
     * @return CFHIRResourceEncounter
     */
    public function setReasonCode(CFHIRDataTypeCodeableConcept ...$reasonCode): CFHIRResourceEncounter
    {
        $this->reasonCode = $reasonCode;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$reasonCode
     *
     * @return CFHIRResourceEncounter
     */
    public function addReasonCode(CFHIRDataTypeCodeableConcept ...$reasonCode): CFHIRResourceEncounter
    {
        $this->reasonCode = array_merge($this->reasonCode, $reasonCode);

        return $this;
    }

    /**
     * @return array
     */
    public function getReasonCode(): array
    {
        return $this->reasonCode;
    }

    /**
     * @param CFHIRDataTypeReference ...$reasonReference
     *
     * @return CFHIRResourceEncounter
     */
    public function setReasonReference(CFHIRDataTypeReference ...$reasonReference): CFHIRResourceEncounter
    {
        $this->reasonReference = $reasonReference;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$reasonReference
     *
     * @return CFHIRResourceEncounter
     */
    public function addReasonReference(CFHIRDataTypeReference ...$reasonReference): CFHIRResourceEncounter
    {
        $this->reasonReference = array_merge($this->reasonReference, $reasonReference);

        return $this;
    }

    /**
     * @return array
     */
    public function getReasonReference(): array
    {
        return $this->reasonReference;
    }

    /**
     * @param CFHIRDataTypeEncounterDiagnosis ...$diagnosis
     *
     * @return CFHIRResourceEncounter
     */
    public function setDiagnosis(CFHIRDataTypeEncounterDiagnosis ...$diagnosis): CFHIRResourceEncounter
    {
        $this->diagnosis = $diagnosis;

        return $this;
    }

    /**
     * @param CFHIRDataTypeEncounterDiagnosis ...$diagnosis
     *
     * @return CFHIRResourceEncounter
     */
    public function addDiagnosis(CFHIRDataTypeEncounterDiagnosis ...$diagnosis): CFHIRResourceEncounter
    {
        $this->diagnosis = array_merge($this->diagnosis, $diagnosis);

        return $this;
    }

    /**
     * @return array
     */
    public function getDiagnosis(): array
    {
        return $this->diagnosis;
    }


    /**
     * @return array
     */
    protected function mapDiagnosis(): void
    {
        $this->diagnosis = $this->object_mapping->mapDiagnosis();
    }


    /**
     * @param CFHIRDataTypeReference ...$account
     *
     * @return CFHIRResourceEncounter
     */
    public function setAccount(CFHIRDataTypeReference ...$account): CFHIRResourceEncounter
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$account
     *
     * @return CFHIRResourceEncounter
     */
    public function addAccount(CFHIRDataTypeReference ...$account): CFHIRResourceEncounter
    {
        $this->account = array_merge($this->account, $account);

        return $this;
    }

    /**
     * @return array
     */
    public function getAccount(): array
    {
        return $this->account;
    }

    /**
     * @param CFHIRDataTypeEncounterHospitalization|null $hospitalization
     *
     * @return CFHIRResourceEncounter
     */
    public function setHospitalization(?CFHIRDataTypeEncounterHospitalization $hospitalization): CFHIRResourceEncounter
    {
        $this->hospitalization = $hospitalization;

        return $this;
    }

    /**
     * @return CFHIRDataTypeEncounterHospitalization|null
     */
    public function getHospitalization(): ?CFHIRDataTypeEncounterHospitalization
    {
        return $this->hospitalization;
    }

    /**
     * Map property Hospitalization
     */
    protected function mapHospitalization(): void
    {
        $this->hospitalization = $this->object_mapping->mapHospitalization();
    }

    /**
     * @param CFHIRDataTypeEncounterLocation ...$location
     *
     * @return CFHIRResourceEncounter
     */
    public function setLocation(CFHIRDataTypeEncounterLocation ...$location): CFHIRResourceEncounter
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @param CFHIRDataTypeEncounterLocation ...$location
     *
     * @return CFHIRResourceEncounter
     */
    public function addLocation(CFHIRDataTypeEncounterLocation ...$location): CFHIRResourceEncounter
    {
        $this->location = array_merge($this->location, $location);

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference|null $serviceProvider
     *
     * @return CFHIRResourceEncounter
     */
    public function setServiceProvider(?CFHIRDataTypeReference $serviceProvider): CFHIRResourceEncounter
    {
        $this->serviceProvider = $serviceProvider;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getServiceProvider(): ?CFHIRDataTypeReference
    {
        return $this->serviceProvider;
    }

    /**
     * @param CFHIRDataTypeReference|null $partOf
     *
     * @return CFHIRResourceEncounter
     */
    public function setPartOf(?CFHIRDataTypeReference $partOf): CFHIRResourceEncounter
    {
        $this->partOf = $partOf;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getPartOf(): ?CFHIRDataTypeReference
    {
        return $this->partOf;
    }

    /**
     * @param CFHIRDataTypeEncounterClassHistory ...$classHistory
     *
     * @return CFHIRResourceEncounter
     */
    public function setClassHistory(CFHIRDataTypeEncounterClassHistory ...$classHistory): CFHIRResourceEncounter
    {
        $this->classHistory = $classHistory;

        return $this;
    }

    /**
     * @param CFHIRDataTypeEncounterClassHistory ...$classHistory
     *
     * @return CFHIRResourceEncounter
     */
    public function addClassHistory(CFHIRDataTypeEncounterClassHistory ...$classHistory): CFHIRResourceEncounter
    {
        $this->classHistory = array_merge($this->classHistory, $classHistory);

        return $this;
    }

    /**
     * @return array
     */
    public function getClassHistory(): array
    {
        return $this->classHistory;
    }

    /**
     * @return array
     */
    protected function mapClassHistory(): void
    {
        $this->classHistory = $this->object_mapping->mapClassHistory();
    }

    /**
     * @return array
     */
    public function getLocation(): array
    {
        return $this->location;
    }

    /**
     * Map property Location
     */
    protected function mapLocation(): void
    {
        $this->location = $this->object_mapping->mapLocation();
    }

    /**
     * Map property status
     */
    protected function mapStatus(): void
    {
        $this->status = $this->object_mapping->mapStatus();
    }

    /**
     * Map property status
     */
    protected function mapStatusHistory(): void
    {
        $this->statusHistory = $this->object_mapping->mapStatusHistory();
    }

    /**
     * Map property class
     */
    protected function mapClass(): void
    {
        $this->class = $this->object_mapping->mapClass();
    }

    /**
     * Map property type
     */
    protected function mapType(): void
    {
        $this->type = $this->object_mapping->mapType();
    }

    /**
     * Map property serviceType
     */
    protected function mapServiceType(): void
    {
        $this->serviceType = $this->object_mapping->mapServiceType();
    }

    /**
     * Map property priority
     */
    protected function mapPriority(): void
    {
        $this->priority = $this->object_mapping->mapPriority();
    }

    /**
     * Map property subject
     */
    protected function mapSubject(): void
    {
        $this->subject = $this->object_mapping->mapSubject();
    }

    /**
     * Map property episodeOfCare
     */
    protected function mapEpisodeOfCare(): void
    {
        $this->episodeOfCare = $this->object_mapping->mapEpisodeOfCare();
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
     * Map property appointment
     */
    protected function mapAppointment(): void
    {
        $this->appointment = $this->object_mapping->mapAppointment();
    }

    /**
     * Map property period
     */
    protected function mapPeriod(): void
    {
        $this->period = $this->object_mapping->mapPeriod();
    }

    /**
     * Map property length
     */
    protected function mapLength(): void
    {
        $this->length = $this->object_mapping->mapLength();
    }

    /**
     * Map property reasonCode
     */
    protected function mapReasonCode(): void
    {
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
     * Map property account
     */
    protected function mapAccount(): void
    {
        $this->account = $this->object_mapping->mapAccount();
    }

    /**
     * Map property serviceProvider
     */
    protected function mapServiceProvider(): void
    {
        $this->serviceProvider = $this->object_mapping->mapServiceProvider();
    }

    /**
     * Map property partOf
     */
    protected function mapPartOf(): void
    {
        $this->partOf = $this->object_mapping->mapPartOf();
    }
}
