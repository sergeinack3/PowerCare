<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\RelatedPerson;

use Ox\Interop\Fhir\Contracts\Mapping\R4\RelatedPersonMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourceRelatedPersonInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDate;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\RelatedPerson\CFHIRDataTypeRelatedPersonCommunication;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAddress;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAttachment;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactPoint;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeHumanName;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionDelete;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionRead;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionSearch;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionUpdate;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;
use Ox\Interop\Fhir\Utilities\CCapabilitiesResource;

/**
 * FHIR RelatedPerson ressource
 */
class CFHIRResourceRelatedPerson extends CFHIRDomainResource implements ResourceRelatedPersonInterface
{
    // constants
    /** @var string */
    public const RESOURCE_TYPE = "RelatedPerson";

    // attributes
    protected ?CFHIRDataTypeBoolean $active = null;

    protected ?CFHIRDataTypeReference $patient = null;

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $relationship = [];

    /** @var CFHIRDataTypeHumanName[] */
    protected array $name = [];

    /** @var CFHIRDataTypeContactPoint[] */
    protected array $telecom = [];

    protected ?CFHIRDataTypeCode $gender = null;

    protected ?CFHIRDataTypeDate $birthDate = null;

    /** @var CFHIRDataTypeAddress[] */
    protected array $address = [];

    protected ?CFHIRDataTypePeriod $period = null;

    /** @var CFHIRDataTypeAttachment[] */
    protected array $photo = [];

    /** @var CFHIRDataTypeRelatedPersonCommunication[] */
    protected array $communication = [];

    /** @var RelatedPersonMappingInterface */
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
     * @param CFHIRDataTypeBoolean|null $active
     *
     * @return CFHIRResourceRelatedPerson
     */
    public function setActive(?CFHIRDataTypeBoolean $active): CFHIRResourceRelatedPerson
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
     * @param CFHIRDataTypeReference|null $patient
     *
     * @return CFHIRResourceRelatedPerson
     */
    public function setPatient(?CFHIRDataTypeReference $patient): CFHIRResourceRelatedPerson
    {
        $this->patient = $patient;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getPatient(): ?CFHIRDataTypeReference
    {
        return $this->patient;
    }

    /**
     * @param CFHIRDataTypeCode|null $gender
     *
     * @return CFHIRResourceRelatedPerson
     */
    public function setGender(?CFHIRDataTypeCode $gender): CFHIRResourceRelatedPerson
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function getGender(): ?CFHIRDataTypeCode
    {
        return $this->gender;
    }

    /**
     * @param CFHIRDataTypeDate|null $birthDate
     *
     * @return CFHIRResourceRelatedPerson
     */
    public function setBirthDate(?CFHIRDataTypeDate $birthDate): CFHIRResourceRelatedPerson
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * @return CFHIRDataTypeDate|null
     */
    public function getBirthDate(): ?CFHIRDataTypeDate
    {
        return $this->birthDate;
    }

    /**
     * @param CFHIRDataTypePeriod|null $period
     *
     * @return CFHIRResourceRelatedPerson
     */
    public function setPeriod(?CFHIRDataTypePeriod $period): CFHIRResourceRelatedPerson
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
     * Map property active
     */
    protected function mapActive(): void
    {
        $this->active = $this->object_mapping->mapActive();
    }

    /**
     * Map property patient
     */
    protected function mapPatient(): void
    {
        $this->patient = $this->object_mapping->mapPatient();
    }

    /**
     * Map property relationship
     */
    protected function mapRelationship(): void
    {
        $this->object_mapping = $this->object_mapping->mapRelationship();
    }

    /**
     * Map property name
     */
    protected function mapName(): void
    {
        $this->name = $this->object_mapping->mapName();
    }

    /**
     * Map property telecom
     */
    protected function mapTelecom(): void
    {
        $this->telecom = $this->object_mapping->mapTelecom();
    }

    /**
     * Map property gender
     */
    protected function mapGender(): void
    {
        $this->gender = $this->object_mapping->mapGender();
    }

    /**
     * Map property birthDate
     */
    protected function mapBirthDate(): void
    {
        $this->birthDate = $this->object_mapping->mapBirthDate();
    }

    /**
     * Map property address
     */
    protected function mapAddress(): void
    {
        $this->address = $this->object_mapping->mapAddress();
    }

    /**
     * Map property photo
     */
    protected function mapPhoto(): void
    {
        $this->photo = $this->object_mapping->mapPhoto();
    }

    /**
     * Map property period
     */
    protected function mapPeriod(): void
    {
        $this->period = $this->object_mapping->mapPeriod();
    }

    /**
     * Map property communication
     */
    protected function mapCommunication(): void
    {
        $this->communication = $this->object_mapping->mapCommunication();
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$relationship
     *
     * @return self
     */
    public function setRelationship(CFHIRDataTypeCodeableConcept ...$relationship): self
    {
        $this->relationship = $relationship;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$relationship
     *
     * @return self
     */
    public function addRelationship(CFHIRDataTypeCodeableConcept ...$relationship): self
    {
        $this->relationship = array_merge($this->relationship, $relationship);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function getRelationship(): array
    {
        return $this->relationship;
    }

    /**
     * @param CFHIRDataTypeHumanName ...$name
     *
     * @return self
     */
    public function setName(CFHIRDataTypeHumanName ...$name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param CFHIRDataTypeHumanName ...$name
     *
     * @return self
     */
    public function addName(CFHIRDataTypeHumanName ...$name): self
    {
        $this->name = array_merge($this->name, $name);

        return $this;
    }

    /**
     * @return CFHIRDataTypeHumanName[]
     */
    public function getName(): array
    {
        return $this->name;
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
     * @param CFHIRDataTypeAddress ...$address
     *
     * @return self
     */
    public function setAddress(CFHIRDataTypeAddress ...$address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @param CFHIRDataTypeAddress ...$address
     *
     * @return self
     */
    public function addAddress(CFHIRDataTypeAddress ...$address): self
    {
        $this->address = array_merge($this->address, $address);

        return $this;
    }

    /**
     * @return CFHIRDataTypeAddress[]
     */
    public function getAddress(): array
    {
        return $this->address;
    }

    /**
     * @param CFHIRDataTypeAttachment ...$photo
     *
     * @return self
     */
    public function setPhoto(CFHIRDataTypeAttachment ...$photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * @param CFHIRDataTypeAttachment ...$photo
     *
     * @return self
     */
    public function addPhoto(CFHIRDataTypeAttachment ...$photo): self
    {
        $this->photo = array_merge($this->photo, $photo);

        return $this;
    }

    /**
     * @return CFHIRDataTypeAttachment[]
     */
    public function getPhoto(): array
    {
        return $this->photo;
    }

    /**
     * @param CFHIRDataTypeRelatedPersonCommunication ...$communication
     *
     * @return self
     */
    public function setCommunication(CFHIRDataTypeRelatedPersonCommunication ...$communication): self
    {
        $this->communication = $communication;

        return $this;
    }

    /**
     * @param CFHIRDataTypeRelatedPersonCommunication ...$communication
     *
     * @return self
     */
    public function addCommunication(CFHIRDataTypeRelatedPersonCommunication ...$communication): self
    {
        $this->communication = array_merge($this->communication, $communication);

        return $this;
    }

    /**
     * @return CFHIRDataTypeRelatedPersonCommunication[]
     */
    public function getCommunication(): array
    {
        return $this->communication;
    }
}
