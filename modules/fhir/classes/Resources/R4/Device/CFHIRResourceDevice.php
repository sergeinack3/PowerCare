<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Device;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Device\CFHIRDataTypeDeviceDeviceName;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Device\CFHIRDataTypeDeviceProperty;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Device\CFHIRDataTypeDeviceSpecialization;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Device\CFHIRDataTypeDeviceUdiCarrier;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Device\CFHIRDataTypeDeviceVersion;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAnnotation;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactPoint;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionDelete;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionRead;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionSearch;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionUpdate;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;
use Ox\Interop\Fhir\Resources\R4\Device\Mapper\Device;
use Ox\Interop\Fhir\Utilities\CCapabilitiesResource;

/**
 * FHIR device resource
 */
class CFHIRResourceDevice extends CFHIRDomainResource
{
    // constants
    /** @var string */
    public const RESOURCE_TYPE = 'Device';

    // attributes
    protected ?CFHIRDataTypeReference $definition = null;

    /** @var CFHIRDataTypeDeviceUdiCarrier[] */
    protected array $udiCarrier = [];

    protected ?CFHIRDataTypeCode $status = null;

    /** @var CFHIRDataTypeCodeableConcept[]|null */
    protected array $statusReason = [];

    protected ?CFHIRDataTypeString $distinctIdentifier = null;

    protected ?CFHIRDataTypeString $manufacturer = null;

    protected ?CFHIRDataTypeDateTime $manufactureDate = null;

    protected ?CFHIRDataTypeDateTime $expirationDate = null;

    protected ?CFHIRDataTypeString $lotNumber = null;

    protected ?CFHIRDataTypeString $serialNumber = null;

    /** @var CFHIRDataTypeDeviceDeviceName[] */
    protected array $deviceName = [];

    protected ?CFHIRDataTypeString $modelNumber = null;

    protected ?CFHIRDataTypeString $partNumber = null;

    protected ?CFHIRDataTypeCodeableConcept $type = null;

    /** @var CFHIRDataTypeDeviceSpecialization[] */
    protected array $specialization = [];

    /** @var CFHIRDataTypeDeviceVersion[] */
    protected array $version = [];

    /** @var CFHIRDataTypeDeviceProperty[] */
    protected array $property = [];

    protected ?CFHIRDataTypeReference $patient = null;

    protected ?CFHIRDataTypeReference $owner = null;

    /** @var CFHIRDataTypeContactPoint[] */
    protected array $contact = [];

    protected ?CFHIRDataTypeReference $location = null;

    protected ?CFHIRDataTypeUri $url = null;

    /** @var CFHIRDataTypeAnnotation[] */
    protected array $note = [];

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $safety = [];

    protected ?CFHIRDataTypeReference $parent = null;

    /** @var Device */
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
     * @param CFHIRDataTypeReference|null $definition
     *
     * @return CFHIRResourceDevice
     */
    public function setDefinition(?CFHIRDataTypeReference $definition): CFHIRResourceDevice
    {
        $this->definition = $definition;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getDefinition(): ?CFHIRDataTypeReference
    {
        return $this->definition;
    }

    /**
     * @param CFHIRDataTypeCode|null $status
     *
     * @return CFHIRResourceDevice
     */
    public function setStatus(?CFHIRDataTypeCode $status): CFHIRResourceDevice
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
     * @param CFHIRDataTypeCodeableConcept ...$statusReason
     *
     * @return CFHIRResourceDevice
     */
    public function setStatusReason(CFHIRDataTypeCodeableConcept ...$statusReason): CFHIRResourceDevice
    {
        $this->statusReason = $statusReason;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$statusReason
     *
     * @return CFHIRResourceDevice
     */
    public function addStatusReason(CFHIRDataTypeCodeableConcept ...$statusReason): CFHIRResourceDevice
    {
        $this->statusReason = array_merge($this->statusReason, $statusReason);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function getStatusReason(): array
    {
        return $this->statusReason;
    }

    /**
     * @param CFHIRDataTypeString|null $distinctIdentifier
     *
     * @return CFHIRResourceDevice
     */
    public function setDistinctIdentifier(?CFHIRDataTypeString $distinctIdentifier): CFHIRResourceDevice
    {
        $this->distinctIdentifier = $distinctIdentifier;

        return $this;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getDistinctIdentifier(): ?CFHIRDataTypeString
    {
        return $this->distinctIdentifier;
    }

    /**
     * @param CFHIRDataTypeString|null $manufacturer
     *
     * @return CFHIRResourceDevice
     */
    public function setManufacturer(?CFHIRDataTypeString $manufacturer): CFHIRResourceDevice
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getManufacturer(): ?CFHIRDataTypeString
    {
        return $this->manufacturer;
    }

    /**
     * @param CFHIRDataTypeDateTime|null $manufactureDate
     *
     * @return CFHIRResourceDevice
     */
    public function setManufactureDate(?CFHIRDataTypeDateTime $manufactureDate): CFHIRResourceDevice
    {
        $this->manufactureDate = $manufactureDate;

        return $this;
    }

    /**
     * @return CFHIRDataTypeDateTime|null
     */
    public function getManufactureDate(): ?CFHIRDataTypeDateTime
    {
        return $this->manufactureDate;
    }

    /**
     * @param CFHIRDataTypeDateTime|null $expirationDate
     *
     * @return CFHIRResourceDevice
     */
    public function setExpirationDate(?CFHIRDataTypeDateTime $expirationDate): CFHIRResourceDevice
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    /**
     * @return CFHIRDataTypeDateTime|null
     */
    public function getExpirationDate(): ?CFHIRDataTypeDateTime
    {
        return $this->expirationDate;
    }

    /**
     * @param CFHIRDataTypeString|null $lotNumber
     *
     * @return CFHIRResourceDevice
     */
    public function setLotNumber(?CFHIRDataTypeString $lotNumber): CFHIRResourceDevice
    {
        $this->lotNumber = $lotNumber;

        return $this;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getLotNumber(): ?CFHIRDataTypeString
    {
        return $this->lotNumber;
    }

    /**
     * @param CFHIRDataTypeString|null $serialNumber
     *
     * @return CFHIRResourceDevice
     */
    public function setSerialNumber(?CFHIRDataTypeString $serialNumber): CFHIRResourceDevice
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getSerialNumber(): ?CFHIRDataTypeString
    {
        return $this->serialNumber;
    }

    /**
     * @param CFHIRDataTypeString|null $modelNumber
     *
     * @return CFHIRResourceDevice
     */
    public function setModelNumber(?CFHIRDataTypeString $modelNumber): CFHIRResourceDevice
    {
        $this->modelNumber = $modelNumber;

        return $this;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getModelNumber(): ?CFHIRDataTypeString
    {
        return $this->modelNumber;
    }

    /**
     * @param CFHIRDataTypeString|null $partNumber
     *
     * @return CFHIRResourceDevice
     */
    public function setPartNumber(?CFHIRDataTypeString $partNumber): CFHIRResourceDevice
    {
        $this->partNumber = $partNumber;

        return $this;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getPartNumber(): ?CFHIRDataTypeString
    {
        return $this->partNumber;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept|null $type
     *
     * @return CFHIRResourceDevice
     */
    public function setType(?CFHIRDataTypeCodeableConcept $type): CFHIRResourceDevice
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function getType(): ?CFHIRDataTypeCodeableConcept
    {
        return $this->type;
    }

    /**
     * @param CFHIRDataTypeReference|null $patient
     *
     * @return CFHIRResourceDevice
     */
    public function setPatient(?CFHIRDataTypeReference $patient): CFHIRResourceDevice
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
     * @param CFHIRDataTypeReference|null $owner
     *
     * @return CFHIRResourceDevice
     */
    public function setOwner(?CFHIRDataTypeReference $owner): CFHIRResourceDevice
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getOwner(): ?CFHIRDataTypeReference
    {
        return $this->owner;
    }

    /**
     * @param CFHIRDataTypeContactPoint ...$contact
     *
     * @return CFHIRResourceDevice
     */
    public function setContact(CFHIRDataTypeContactPoint ...$contact): CFHIRResourceDevice
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @param CFHIRDataTypeContactPoint ...$contact
     *
     * @return CFHIRResourceDevice
     */
    public function addContact(CFHIRDataTypeContactPoint ...$contact): CFHIRResourceDevice
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
     * @param CFHIRDataTypeReference|null $location
     *
     * @return CFHIRResourceDevice
     */
    public function setLocation(?CFHIRDataTypeReference $location): CFHIRResourceDevice
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getLocation(): ?CFHIRDataTypeReference
    {
        return $this->location;
    }

    /**
     * @param CFHIRDataTypeUri|null $url
     *
     * @return CFHIRResourceDevice
     */
    public function setUrl(?CFHIRDataTypeUri $url): CFHIRResourceDevice
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return CFHIRDataTypeUri|null
     */
    public function getUrl(): ?CFHIRDataTypeUri
    {
        return $this->url;
    }

    /**
     * @param CFHIRDataTypeAnnotation ...$note
     *
     * @return CFHIRResourceDevice
     */
    public function setNote(CFHIRDataTypeAnnotation ...$note): CFHIRResourceDevice
    {
        $this->note = $note;

        return $this;
    }

    /**
     * @param CFHIRDataTypeAnnotation ...$note
     *
     * @return CFHIRResourceDevice
     */
    public function addNote(CFHIRDataTypeAnnotation ...$note): CFHIRResourceDevice
    {
        $this->note = array_merge($this->note, $note);

        return $this;
    }

    /**
     * @return array
     */
    public function getNote(): array
    {
        return $this->note;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$safety
     *
     * @return CFHIRResourceDevice
     */
    public function setSafety(CFHIRDataTypeCodeableConcept ...$safety): CFHIRResourceDevice
    {
        $this->safety = $safety;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$safety
     *
     * @return CFHIRResourceDevice
     */
    public function addSafety(CFHIRDataTypeCodeableConcept ...$safety): CFHIRResourceDevice
    {
        $this->safety = array_merge($this->safety, $safety);

        return $this;
    }

    /**
     * @return array
     */
    public function getSafety(): array
    {
        return $this->safety;
    }

    /**
     * @param CFHIRDataTypeReference|null $parent
     *
     * @return CFHIRResourceDevice
     */
    public function setParent(?CFHIRDataTypeReference $parent): CFHIRResourceDevice
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getParent(): ?CFHIRDataTypeReference
    {
        return $this->parent;
    }

    /**
     * @param CFHIRDataTypeDeviceSpecialization ...$specialization
     *
     * @return CFHIRResourceDevice
     */
    public function setSpecialization(CFHIRDataTypeDeviceSpecialization ...$specialization): CFHIRResourceDevice
    {
        $this->specialization = $specialization;

        return $this;
    }

    /**
     * @param CFHIRDataTypeDeviceSpecialization ...$specialization
     *
     * @return CFHIRResourceDevice
     */
    public function addSpecialization(CFHIRDataTypeDeviceSpecialization ...$specialization): CFHIRResourceDevice
    {
        $this->specialization = array_merge($this->specialization, $specialization);

        return $this;
    }

    /**
     * @return CFHIRDataTypeDeviceSpecialization[]
     */
    public function getSpecialization(): array
    {
        return $this->specialization;
    }

    /**
     * @param CFHIRDataTypeDeviceVersion ...$version
     *
     * @return CFHIRResourceDevice
     */
    public function setVersion(CFHIRDataTypeDeviceVersion ...$version): CFHIRResourceDevice
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @param CFHIRDataTypeDeviceVersion ...$version
     *
     * @return CFHIRResourceDevice
     */
    public function addVersion(CFHIRDataTypeDeviceVersion ...$version): CFHIRResourceDevice
    {
        $this->version = array_merge($this->version, $version);

        return $this;
    }

    /**
     * @return array
     */
    public function getVersion(): array
    {
        return $this->version;
    }

    /**
     * @param array $property
     *
     * @return CFHIRResourceDevice
     */
    public function setProperty(CFHIRDataTypeDeviceProperty ...$property): CFHIRResourceDevice
    {
        $this->property = $property;

        return $this;
    }

    /**
     * @param array $property
     *
     * @return CFHIRResourceDevice
     */
    public function addProperty(CFHIRDataTypeDeviceProperty ...$property): CFHIRResourceDevice
    {
        $this->property = array_merge($this->property, $property);

        return $this;
    }

    /**
     * @return array
     */
    public function getProperty(): array
    {
        return $this->property;
    }

    /**
     * @param CFHIRDataTypeDeviceDeviceName ...$deviceName
     *
     * @return CFHIRResourceDevice
     */
    public function setDeviceName(CFHIRDataTypeDeviceDeviceName ...$deviceName): CFHIRResourceDevice
    {
        $this->deviceName = $deviceName;

        return $this;
    }

    /**
     * @param CFHIRDataTypeDeviceDeviceName ...$deviceName
     *
     * @return CFHIRResourceDevice
     */
    public function addDeviceName(CFHIRDataTypeDeviceDeviceName ...$deviceName): CFHIRResourceDevice
    {
        $this->deviceName = array_merge($this->deviceName, $deviceName);

        return $this;
    }

    /**
     * @return array
     */
    public function getDeviceName(): array
    {
        return $this->deviceName;
    }

    /**
     * Map property definition
     */
    protected function mapDefinition(): void
    {
        $this->definition = $this->object_mapping->mapDefinition();
    }

    /**
     * Map property udiCarrier
     */
    protected function mapUdiCarrier(): void
    {
        $this->udiCarrier = $this->object_mapping->mapUdiCarrier();
    }

    /**
     * Map property status
     */
    protected function mapStatus(): void
    {
        $this->status = $this->object_mapping->mapStatus();
    }

    /**
     * Map property statusReason
     */
    protected function mapStatusReason(): void
    {
        $this->statusReason = $this->object_mapping->mapStatusReason();
    }

    /**
     * Map property mapDistinctIdentifier
     */
    protected function mapDistinctIdentifier(): void
    {
        $this->distinctIdentifier = $this->object_mapping->mapDistinctIdentifier();
    }

    /**
     * Map property manufacturer
     */
    protected function mapManufacturer(): void
    {
        $this->manufacturer = $this->object_mapping->mapManufacturer();
    }

    /**
     * Map property manufactureDate
     */
    protected function mapManufactureDate(): void
    {
        $this->manufactureDate = $this->object_mapping->mapManufactureDate();
    }

    /**
     * Map property expirationDate
     */
    protected function mapExpirationDate(): void
    {
        $this->expirationDate = $this->object_mapping->mapExpirationDate();
    }

    /**
     * Map property lotNumber
     */
    protected function mapLotNumber(): void
    {
        $this->lotNumber = $this->object_mapping->mapLotNumber();
    }

    /**
     * Map property serialNumber
     */
    protected function mapSerialNumber(): void
    {
        $this->serialNumber = $this->object_mapping->mapSerialNumber();
    }

    /**
     * Map property deviceName
     */
    protected function mapDeviceName(): void
    {
        $this->deviceName = $this->object_mapping->mapDeviceName();
    }

    /**
     * Map property modelNumber
     */
    protected function mapModelNumber(): void
    {
        $this->modelNumber = $this->object_mapping->mapModelNumber();
    }

    /**
     * Map property partNumber
     */
    protected function mapPartNumber(): void
    {
        $this->partNumber = $this->object_mapping->mapPartNumber();
    }

    /**
     * Map property type
     */
    protected function mapType(): void
    {
        $this->type = $this->object_mapping->mapType();
    }

    /**
     * Map property specialization
     */
    protected function mapSpecialization(): void
    {
        $this->specialization = $this->object_mapping->mapSpecialization();
    }

    /**
     * Map property version
     */
    protected function mapVersion(): void
    {
        $this->version = $this->object_mapping->mapVersion();
    }

    /**
     * Map property property
     */
    protected function mapProperty(): void
    {
        $this->property = $this->object_mapping->mapProperty();
    }

    /**
     * Map property patient
     */
    protected function mapPatient(): void
    {
        $this->patient = $this->object_mapping->mapPatient();
    }

    /**
     * Map property owner
     */
    protected function mapOwner(): void
    {
        $this->owner = $this->object_mapping->mapOwner();
    }

    /**
     * Map property contact
     */
    protected function mapContact(): void
    {
        $this->contact = $this->object_mapping->mapContact();
    }

    /**
     * Map property location
     */
    protected function mapLocation(): void
    {
        $this->location = $this->object_mapping->mapLocation();
    }

    /**
     * Map property url
     */
    protected function mapUrl(): void
    {
        $this->url = $this->object_mapping->mapUrl();
    }

    /**
     * Map property note
     */
    protected function mapNote(): void
    {
        $this->note = $this->object_mapping->mapNote();
    }

    /**
     * Map property safety
     */
    protected function mapSafety(): void
    {
        $this->safety = $this->object_mapping->mapSafety();
    }

    /**
     * Map property parent
     */
    protected function mapParent(): void
    {
        $this->parent = $this->object_mapping->mapParent();
    }

    /**
     * @param CFHIRDataTypeDeviceUdiCarrier ...$udiCarrier
     *
     * @return CFHIRResourceDevice
     */
    public function setUdiCarrier(CFHIRDataTypeDeviceUdiCarrier ...$udiCarrier): CFHIRResourceDevice
    {
        $this->udiCarrier = $udiCarrier;

        return $this;
    }

    /**
     * @param CFHIRDataTypeDeviceUdiCarrier ...$udiCarrier
     *
     * @return CFHIRResourceDevice
     */
    public function addUdiCarrier(CFHIRDataTypeDeviceUdiCarrier ...$udiCarrier): CFHIRResourceDevice
    {
        $this->udiCarrier = array_merge($this->udiCarrier, $udiCarrier);

        return $this;
    }

    /**
     * @return array
     */
    public function getUdiCarrier(): array
    {
        return $this->udiCarrier;
    }
}
