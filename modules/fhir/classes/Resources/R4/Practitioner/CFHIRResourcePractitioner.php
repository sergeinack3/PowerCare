<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Practitioner;

use Ox\Interop\Fhir\Contracts\Mapping\R4\PractitionerMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourcePractitionerInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDate;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Practitioner\CFHIRDataTypePractitionerQualification;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAddress;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAttachment;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactPoint;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeHumanName;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionDelete;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionRead;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionSearch;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionUpdate;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;
use Ox\Interop\Fhir\Utilities\CCapabilitiesResource;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterString;

/**
 * FHIR practitioner resource
 */
class CFHIRResourcePractitioner extends CFHIRDomainResource implements ResourcePractitionerInterface
{
    // constants
    /** @var string */
    public const RESOURCE_TYPE = 'Practitioner';

    // attributes
    protected ?CFHIRDataTypeBoolean $active = null;

    /** @var CFHIRDataTypeHumanName[] */
    protected array $name = [];

    /** @var CFHIRDataTypeContactPoint[] */
    protected array $telecom = [];

    /** @var CFHIRDataTypeAddress[] */
    protected array $address = [];

    protected ?CFHIRDataTypeCode $gender = null;

    protected ?CFHIRDataTypeDate $birthDate = null;

    /** @var CFHIRDataTypeAttachment[] */
    protected array $photo = [];

    /** @var CFHIRDataTypePractitionerQualification[] */
    protected array $qualification = [];

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $communication = [];

    /** @var PractitionerMappingInterface */
    protected $object_mapping;

    /**
     * @param CFHIRDataTypeBoolean|null $active
     *
     * @return CFHIRResourcePractitioner
     */
    public function setActive(?CFHIRDataTypeBoolean $active): CFHIRResourcePractitioner
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
     * @param CFHIRDataTypeHumanName ...$name
     *
     * @return CFHIRResourcePractitioner
     */
    public function setName(CFHIRDataTypeHumanName ...$name): CFHIRResourcePractitioner
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param CFHIRDataTypeHumanName ...$name
     *
     * @return CFHIRResourcePractitioner
     */
    public function addName(CFHIRDataTypeHumanName ...$name): CFHIRResourcePractitioner
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
     * @return CFHIRResourcePractitioner
     */
    public function setTelecom(CFHIRDataTypeContactPoint ...$telecom): CFHIRResourcePractitioner
    {
        $this->telecom = $telecom;

        return $this;
    }

    /**
     * @param CFHIRDataTypeContactPoint ...$telecom
     *
     * @return CFHIRResourcePractitioner
     */
    public function addTelecom(CFHIRDataTypeContactPoint ...$telecom): CFHIRResourcePractitioner
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
     * @return CFHIRResourcePractitioner
     */
    public function setAddress(CFHIRDataTypeAddress ...$address): CFHIRResourcePractitioner
    {
        $this->address = $address;

        return $this;
    }


    /**
     * @param CFHIRDataTypeAddress ...$address
     *
     * @return CFHIRResourcePractitioner
     */
    public function addAddress(CFHIRDataTypeAddress ...$address): CFHIRResourcePractitioner
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
     * @param CFHIRDataTypeCode|null $gender
     *
     * @return CFHIRResourcePractitioner
     */
    public function setGender(?CFHIRDataTypeCode $gender): CFHIRResourcePractitioner
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
     * @return CFHIRResourcePractitioner
     */
    public function setBirthDate(?CFHIRDataTypeDate $birthDate): CFHIRResourcePractitioner
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
     * @param CFHIRDataTypeAttachment ...$photo
     *
     * @return CFHIRResourcePractitioner
     */
    public function setPhoto(CFHIRDataTypeAttachment ...$photo): CFHIRResourcePractitioner
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * @param CFHIRDataTypeAttachment ...$photo
     *
     * @return CFHIRResourcePractitioner
     */
    public function addPhoto(CFHIRDataTypeAttachment ...$photo): CFHIRResourcePractitioner
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
     * @param CFHIRDataTypePractitionerQualification ...$qualification
     *
     * @return CFHIRResourcePractitioner
     */
    public function setQualification(CFHIRDataTypePractitionerQualification ...$qualification): CFHIRResourcePractitioner
    {
        $this->qualification = $qualification;

        return $this;
    }

    /**
     * @param CFHIRDataTypePractitionerQualification ...$qualification
     *
     * @return CFHIRResourcePractitioner
     */
    public function addQualification(CFHIRDataTypePractitionerQualification ...$qualification): CFHIRResourcePractitioner
    {
        $this->qualification = array_merge($this->qualification, $qualification);

        return $this;
    }

    /**
     * @return CFHIRDataTypePractitionerQualification[]
     */
    public function getQualification(): array
    {
        return $this->qualification;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$communication
     *
     * @return CFHIRResourcePractitioner
     */
    public function setCommunication(CFHIRDataTypeCodeableConcept ...$communication): CFHIRResourcePractitioner
    {
        $this->communication = $communication;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$communication
     *
     * @return CFHIRResourcePractitioner
     */
    public function addCommunication(CFHIRDataTypeCodeableConcept ...$communication): CFHIRResourcePractitioner
    {
        $this->communication = array_merge($this->communication, $communication);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function getCommunication(): array
    {
        return $this->communication;
    }

    /**
     * @return CCapabilitiesResource
     */
    protected function generateCapabilities(): CCapabilitiesResource
    {
        return parent::generateCapabilities()
            ->addInteractions(
                [
                    CFHIRInteractionCreate::NAME,
                    CFHIRInteractionRead::NAME,
                    CFHIRInteractionSearch::NAME,
                    CFHIRInteractionUpdate::NAME,
                    CFHIRInteractionDelete::NAME,
                ]
            )
            ->addSearchAttributes(
                [
                    new SearchParameterString('family'),
                ]
            );
    }

    /**
     * Map property telecom
     */
    protected function mapTelecom(): void
    {
        $this->telecom = $this->object_mapping->mapTelecom();
    }

    /**
     * Map property address
     */
    protected function mapAddress(): void
    {
        $this->address = $this->object_mapping->mapAddress();
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
     * Map property photo
     */
    protected function mapPhoto(): void
    {
        $this->photo = $this->object_mapping->mapPhoto();
    }

    /**
     * Map property qualification
     */
    protected function mapQualification(): void
    {
        $this->qualification = $this->object_mapping->mapQualification();
    }

    /**
     * Map property communication
     */
    protected function mapCommunication(): void
    {
        $this->communication = $this->object_mapping->mapCommunication();
    }

    /**
     * Map property active
     */
    protected function mapActive(): void
    {
        $this->active = $this->object_mapping->mapActive();
    }

    /**
     * Map property name
     */
    protected function mapName(): void
    {
        $this->name = $this->object_mapping->mapName();
    }
}
