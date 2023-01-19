<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Patient;

use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Interop\Eai\CDomain;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Mapping\R4\PatientMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourcePatientInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataType;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDate;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInteger;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Patient\CFHIRDataTypePatientCommunication;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Patient\CFHIRDataTypePatientContact;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Patient\CFHIRDataTypePatientLink;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAddress;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAttachment;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactPoint;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeHumanName;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Exception\CFHIRExceptionBadRequest;
use Ox\Interop\Fhir\Exception\CFHIRExceptionEmptyResultSet;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionDelete;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionHistory;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionRead;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionSearch;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionUpdate;
use Ox\Interop\Fhir\Operations\CFHIROperationIhePix;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;
use Ox\Interop\Fhir\Resources\R4\Patient\Mapper\Patient;
use Ox\Interop\Fhir\Resources\R4\Practitioner\CFHIRResourcePractitioner;
use Ox\Interop\Fhir\Utilities\CCapabilitiesResource;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterDate;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterString;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterToken;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * FHIR patient resource
 */
class CFHIRResourcePatient extends CFHIRDomainResource implements ResourcePatientInterface
{
    /** @var string */
    public const VERSION_NORMATIVE = '4.0';

    /** @var string */
    public const RESOURCE_TYPE = "Patient";

    // attributes
    protected ?CFHIRDataTypeBoolean $active = null;

    /** @var CFHIRDataTypeHumanName[] */
    protected array $name = [];

    /** @var CFHIRDataTypeContactPoint[] */
    protected array $telecom = [];

    protected ?CFHIRDataTypeCode $gender = null;

    public ?CFHIRDataTypeDate $birthDate = null;

    /** @var CFHIRDataTypeBoolean|CFHIRDataTypeDateTime */
    protected ?CFHIRDataType $deceased = null;

    /** @var CFHIRDataTypeAddress[] */
    protected array $address = [];

    protected ?CFHIRDataTypeCodeableConcept $maritalStatus = null;

    /** @var CFHIRDataTypeBoolean|CFHIRDataTypeInteger|null */
    protected ?CFHIRDataType $multipleBirth = null;

    /** @var CFHIRDataTypeAttachment[] */
    protected array $photo = [];

    /** @var CFHIRDataTypePatientContact[] */
    protected array $contact = [];

    /** @var CFHIRDataTypePatientCommunication[] */
    protected array $communication = [];

    /** @var CFHIRDataTypeReference[] */
    protected array $generalPractitioner = [];

    protected ?CFHIRDataTypeReference $managingOrganization = null;

    /** @var CFHIRDataTypePatientLink[] */
    protected array $link = [];

    /** @var PatientMappingInterface */
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
                    CFHIRInteractionHistory::NAME,
                    CFHIROperationIhePix::NAME,
                ]
            )
            ->addSearchAttributes(
                [
                    new SearchParameterToken('identifier'),
                    new SearchParameterString('family'),
                    new SearchParameterString('given'),
                    new SearchParameterDate('birthdate'),
                    new SearchParameterString('email'),
                    new SearchParameterString('address'),
                    new SearchParameterString('address-city'),
                    new SearchParameterString('address-postalcode'),
                    new SearchParameterToken('gender'),
                ]
            );
    }

    /**
     * @param CFHIRDataTypeContactPoint ...$telecom
     *
     * @return CFHIRResourcePatient
     */
    public function setTelecom(CFHIRDataTypeContactPoint ...$telecom): CFHIRResourcePatient
    {
        $this->telecom = $telecom;

        return $this;
    }

    /**
     * @param CFHIRDataTypeContactPoint ...$telecom
     *
     * @return CFHIRResourcePatient
     */
    public function addTelecom(CFHIRDataTypeContactPoint ...$telecom): CFHIRResourcePatient
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
     * @param CFHIRDataTypeCode|null $gender
     *
     * @return CFHIRResourcePatient
     */
    public function setGender(?CFHIRDataTypeCode $gender): CFHIRResourcePatient
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
     * @return CFHIRResourcePatient
     */
    public function setBirthDate(?CFHIRDataTypeDate $birthDate): CFHIRResourcePatient
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
     * @param CFHIRDataType|null $deceased
     *
     * @return CFHIRResourcePatient
     */
    public function setDeceased(?CFHIRDataType $deceased): CFHIRResourcePatient
    {
        $this->deceased = $deceased;

        return $this;
    }

    /**
     * @return CFHIRDataType|null
     */
    public function getDeceased(): ?CFHIRDataType
    {
        return $this->deceased;
    }

    /**
     * @param CFHIRDataTypeAddress ...$address
     *
     * @return CFHIRResourcePatient
     */
    public function setAddress(CFHIRDataTypeAddress ...$address): CFHIRResourcePatient
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @param CFHIRDataTypeAddress ...$address
     *
     * @return CFHIRResourcePatient
     */
    public function addAddress(CFHIRDataTypeAddress ...$address): CFHIRResourcePatient
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
     * @param CFHIRDataTypeCodeableConcept|null $maritalStatus
     *
     * @return CFHIRResourcePatient
     */
    public function setMaritalStatus(?CFHIRDataTypeCodeableConcept $maritalStatus): CFHIRResourcePatient
    {
        $this->maritalStatus = $maritalStatus;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function getMaritalStatus(): ?CFHIRDataTypeCodeableConcept
    {
        return $this->maritalStatus;
    }

    /**
     * @param CFHIRDataType|null $multipleBirth
     *
     * @return CFHIRResourcePatient
     */
    public function setMultipleBirth(?CFHIRDataType $multipleBirth): CFHIRResourcePatient
    {
        $this->multipleBirth = $multipleBirth;

        return $this;
    }

    /**
     * @return CFHIRDataType|null
     */
    public function getMultipleBirth(): ?CFHIRDataType
    {
        return $this->multipleBirth;
    }

    /**
     * @param CFHIRDataTypeAttachment ...$photo
     *
     * @return CFHIRResourcePatient
     */
    public function setPhoto(CFHIRDataTypeAttachment ...$photo): CFHIRResourcePatient
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * @param CFHIRDataTypeAttachment ...$photo
     *
     * @return CFHIRResourcePatient
     */
    public function addPhoto(CFHIRDataTypeAttachment ...$photo): CFHIRResourcePatient
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
     * @param CFHIRDataTypePatientContact ...$contact
     *
     * @return CFHIRResourcePatient
     */
    public function setContact(CFHIRDataTypePatientContact ...$contact): CFHIRResourcePatient
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @param CFHIRDataTypePatientContact ...$contact
     *
     * @return CFHIRResourcePatient
     */
    public function addContact(CFHIRDataTypePatientContact ...$contact): CFHIRResourcePatient
    {
        $this->contact = array_merge($this->contact, $contact);

        return $this;
    }

    /**
     * @return array
     */
    public function getContact(): array
    {
        return $this->contact;
    }

    /**
     * @param CFHIRDataTypePatientCommunication ...$communication
     *
     * @return CFHIRResourcePatient
     */
    public function setCommunication(CFHIRDataTypePatientCommunication ...$communication): CFHIRResourcePatient
    {
        $this->communication = $communication;

        return $this;
    }

    /**
     * @param CFHIRDataTypePatientCommunication ...$communication
     *
     * @return CFHIRResourcePatient
     */
    public function addCommunication(CFHIRDataTypePatientCommunication ...$communication): CFHIRResourcePatient
    {
        $this->communication = array_merge($this->communication, $communication);

        return $this;
    }

    /**
     * @return CFHIRDataTypePatientCommunication[]
     */
    public function getCommunication(): array
    {
        return $this->communication;
    }

    /**
     * @param CFHIRDataTypeReference ...$generalPractitioner
     *
     * @return CFHIRResourcePatient
     */
    public function setGeneralPractitioner(CFHIRDataTypeReference ...$generalPractitioner): CFHIRResourcePatient
    {
        $this->generalPractitioner = $generalPractitioner;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$generalPractitioner
     *
     * @return CFHIRResourcePatient
     */
    public function addGeneralPractitioner(CFHIRDataTypeReference ...$generalPractitioner): CFHIRResourcePatient
    {
        $this->generalPractitioner = array_merge($this->generalPractitioner, $generalPractitioner);

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference[]
     */
    public function getGeneralPractitioner(): array
    {
        return $this->generalPractitioner;
    }

    /**
     * @param CFHIRDataTypeReference|null $managingOrganization
     *
     * @return CFHIRResourcePatient
     */
    public function setManagingOrganization(?CFHIRDataTypeReference $managingOrganization): CFHIRResourcePatient
    {
        $this->managingOrganization = $managingOrganization;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getManagingOrganization(): ?CFHIRDataTypeReference
    {
        return $this->managingOrganization;
    }

    /**
     * @param CFHIRDataTypePatientLink ...$link
     *
     * @return CFHIRResourcePatient
     */
    public function setLink(CFHIRDataTypePatientLink ...$link): CFHIRResourcePatient
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @param CFHIRDataTypePatientLink ...$link
     *
     * @return CFHIRResourcePatient
     */
    public function addLink(CFHIRDataTypePatientLink ...$link): CFHIRResourcePatient
    {
        $this->link = array_merge($this->link, $link);

        return $this;
    }

    /**
     * @return CFHIRDataTypePatientLink[]
     */
    public function getLink(): array
    {
        return $this->link;
    }

    /**
     * @param array $name
     *
     * @return CFHIRResourcePatient
     */
    public function setName(CFHIRDataTypeHumanName ...$name): CFHIRResourcePatient
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param array $name
     *
     * @return CFHIRResourcePatient
     */
    public function addName(CFHIRDataTypeHumanName ...$name): CFHIRResourcePatient
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
     * @param CFHIRDataTypeBoolean|null $active
     *
     * @return CFHIRResourcePatient
     */
    public function setActive(?CFHIRDataTypeBoolean $active): CFHIRResourcePatient
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
     * @param CStoredObject $object
     *
     * @return DelegatedObjectMapperInterface
     */
    protected function setMapperOld(CStoredObject $object): DelegatedObjectMapperInterface
    {
        $mapping_object = Patient::class;

        return new $mapping_object();
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
     * Map property deceased
     */
    protected function mapDeceased(): void
    {
        $this->deceased = $this->object_mapping->mapDeceased();
    }

    /**
     * Map property address
     */
    protected function mapAddress(): void
    {
        $this->address = $this->object_mapping->mapAddress();
    }

    /**
     * Map property maritalStatus
     */
    protected function mapMaritalStatus(): void
    {
        $this->maritalStatus = $this->object_mapping->mapMaritalStatus();
    }

    /**
     * Map property multipleBirth
     */
    protected function mapMultipleBirth(): void
    {
        $this->multipleBirth = $this->object_mapping->mapMultipleBirth();
    }

    /**
     * Map property photo
     */
    protected function mapPhoto(): void
    {
        $this->photo = $this->object_mapping->mapPhoto();
    }

    /**
     * Map property contact
     */
    protected function mapContact(): void
    {
        $this->contact = $this->object_mapping->mapContact();
    }

    /**
     * Map property communication
     */
    protected function mapCommunication(): void
    {
        $this->communication = $this->object_mapping->mapCommunication();
    }

    /**
     * Map property generalPractitioner
     *
     * @param string $resource_class
     */
    protected function mapGeneralPractitioner(): void
    {
        $this->generalPractitioner = $this->object_mapping->mapGeneralPractitioner(CFHIRResourcePractitioner::class);
    }

    /**
     * Map property managingOrganization
     */
    protected function mapManagingOrganization(): void
    {
        $this->managingOrganization = $this->object_mapping->mapManagingOrganization();
    }

    /**
     * Map property link
     * @throws Exception
     */
    protected function mapLink(): void
    {
        $this->link = $this->object_mapping->mapLink();
    }

    /**
     * Perform a search query based on the current object data
     *
     * @param array $data Data to handle
     *
     * @return CStoredObject
     * @throws CFHIRException
     */
    public function operation_ihe_pix($data)
    {
        if (!$sourceIdentifier = $this->getParameterSearch('sourceIdentifier')) {
            throw new CFHIRException("Invalid number of source identifiers");
        }

        if (strpos($sourceIdentifier, "urn:oid:") === 0) {
            [$system, $value] = explode("|", substr($sourceIdentifier, 8));

            $patient = $this->findPatientByIdentifier($system, $value);

            if ($targetSystem = $this->allParameters->get('targetSystem')) {
                $targetSystem = explode(',', $targetSystem);

                $identifiers = [];
                foreach ($targetSystem as $_system) {
                    if (strpos($_system, "urn:oid:") !== 0) {
                        continue;
                    }

                    $array  = explode("|", str_replace("urn:oid:", "", $_system));
                    $system = CMbArray::get($array, 0);

                    $identifiers[] = $system;
                }

                $patient->_returned_oids = $identifiers;
            }

            return $patient;
        }

        throw new CFHIRExceptionEmptyResultSet();
    }

    /**
     * // todo [Fhir - recherche]
     * Finds a patient by his identifier
     *
     * @param string $system Identifying system (OID)
     * @param string $value  Identifier value
     *
     * @return CMbObject
     * @throws CFHIRExceptionBadRequest
     * @throws CFHIRExceptionEmptyResultSet
     * @throws Exception
     */
    public function findPatientByIdentifier(string $system, string $value): CMbObject
    {
        $domain      = new CDomain();
        $domain->OID = $system;
        $domain->loadMatchingObject();

        if (!$domain->_id) {
            throw new CFHIRExceptionBadRequest("sourceIdentifier Assigning Authority not found");
        }

        $idex = CIdSante400::getMatch("CPatient", $domain->tag, $value);

        if (!$idex->_id) {
            throw new CFHIRExceptionEmptyResultSet("Unknown Patient identified by '$system|$value'");
        }

        return $idex->loadTargetObject();
    }
}
