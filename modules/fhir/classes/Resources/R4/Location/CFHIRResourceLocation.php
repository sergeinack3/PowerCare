<?php

/**
 * @package Mediboard\fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Location;

use Ox\Interop\Fhir\Contracts\Mapping\R4\LocationMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourceLocationInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Location\CFHIRDataTypeLocationHoursOfOperation;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Location\CFHIRDataTypeLocationPosition;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAddress;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactPoint;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionDelete;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionRead;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionSearch;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionUpdate;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;
use Ox\Interop\Fhir\Utilities\CCapabilitiesResource;

/**
 * Description
 */
class CFHIRResourceLocation extends CFHIRDomainResource implements ResourceLocationInterface
{
    // constants
    /** @var string */
    public const RESOURCE_TYPE = 'Location';

    // attributes
    protected ?CFHIRDataTypeCode $status = null;

    protected ?CFHIRDataTypeCoding $operationalStatus = null;

    protected ?CFHIRDataTypeString $name = null;

    /** @var CFHIRDataTypeString[] */
    protected array $alias = [];

    protected ?CFHIRDataTypeString $description = null;

    protected ?CFHIRDataTypeCode $mode = null;

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $type = [];

    /** @var CFHIRDataTypeContactPoint[] */
    protected array $telecom = [];

    protected ?CFHIRDataTypeAddress $address = null;

    protected ?CFHIRDataTypeCodeableConcept $physicalType = null;

    protected ?CFHIRDataTypeLocationPosition $position = null;

    protected ?CFHIRDataTypeReference $managingOrganization = null;

    protected ?CFHIRDataTypeReference $partOf = null;

    /** @var CFHIRDataTypeLocationHoursOfOperation[] */
    protected array $hoursOfOperation = [];

    protected ?CFHIRDataTypeString $availabilityExceptions = null;

    /** @var CFHIRDataTypeReference[] */
    protected array $endpoint = [];


    /** @var LocationMappingInterface */
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
     * @param CFHIRDataTypeCode|null $status
     *
     * @return CFHIRResourceLocation
     */
    public function setStatus(?CFHIRDataTypeCode $status): CFHIRResourceLocation
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
     * @param CFHIRDataTypeCoding|null $operationalStatus
     *
     * @return CFHIRResourceLocation
     */
    public function setOperationalStatus(?CFHIRDataTypeCoding $operationalStatus): CFHIRResourceLocation
    {
        $this->operationalStatus = $operationalStatus;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCoding|null
     */
    public function getOperationalStatus(): ?CFHIRDataTypeCoding
    {
        return $this->operationalStatus;
    }

    /**
     * @param CFHIRDataTypeString|null $name
     *
     * @return CFHIRResourceLocation
     */
    public function setName(?CFHIRDataTypeString $name): CFHIRResourceLocation
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getName(): ?CFHIRDataTypeString
    {
        return $this->name;
    }

    /**
     * @param CFHIRDataTypeString ...$alias
     *
     * @return CFHIRResourceLocation
     */
    public function setAlias(CFHIRDataTypeString ...$alias): CFHIRResourceLocation
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * @param CFHIRDataTypeString ...$alias
     *
     * @return CFHIRResourceLocation
     */
    public function addAlias(CFHIRDataTypeString ...$alias): CFHIRResourceLocation
    {
        $this->alias = array_merge($this->alias, $alias);

        return $this;
    }

    /**
     * @return CFHIRDataTypeString[]
     */
    public function getAlias(): array
    {
        return $this->alias;
    }

    /**
     * @param CFHIRDataTypeString|null $description
     *
     * @return CFHIRResourceLocation
     */
    public function setDescription(?CFHIRDataTypeString $description): CFHIRResourceLocation
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
     * @param CFHIRDataTypeCode|null $mode
     *
     * @return CFHIRResourceLocation
     */
    public function setMode(?CFHIRDataTypeCode $mode): CFHIRResourceLocation
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function getMode(): ?CFHIRDataTypeCode
    {
        return $this->mode;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$type
     *
     * @return CFHIRResourceLocation
     */
    public function setType(CFHIRDataTypeCodeableConcept ...$type): CFHIRResourceLocation
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$type
     *
     * @return CFHIRResourceLocation
     */
    public function addType(CFHIRDataTypeCodeableConcept ...$type): CFHIRResourceLocation
    {
        $this->type = array_merge($this->type, $type);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function getType(): array
    {
        return $this->type;
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
     * @param CFHIRDataTypeAddress|null $address
     *
     * @return CFHIRResourceLocation
     */
    public function setAddress(?CFHIRDataTypeAddress $address): CFHIRResourceLocation
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return CFHIRDataTypeAddress|null
     */
    public function getAddress(): ?CFHIRDataTypeAddress
    {
        return $this->address;
    }

    /**
     * Map property status
     */
    protected function mapStatus(): void
    {
        $this->status = $this->object_mapping->mapStatus();
    }

    /**
     * Map property operationalStatus
     */
    protected function mapOperationalStatus(): void
    {
        $this->operationalStatus = $this->object_mapping->mapOperationalStatus();
    }

    /**
     * Map property name
     */
    protected function mapName(): void
    {
        $this->name = $this->object_mapping->mapName();
    }

    /**
     * Map property alias
     */
    protected function mapAlias(): void
    {
        $this->alias = $this->object_mapping->mapAlias();
    }

    /**
     * Map property description
     */
    protected function mapDescription(): void
    {
        $this->description = $this->object_mapping->mapDescription();
    }

    /**
     * Map property mode
     */
    protected function mapMode(): void
    {
        $this->mode = $this->object_mapping->mapMode();
    }

    /**
     * Map property type
     */
    protected function mapType(): void
    {
        $this->type = $this->object_mapping->mapType();
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
     * Map property physicalType
     */
    protected function mapPhysicalType(): void
    {
        $this->physicalType = $this->object_mapping->mapPhysicalType();
    }

    /**
     * Map property position
     */
    protected function mapPosition(): void
    {
        $this->position = $this->object_mapping->mapPosition();
    }

    /**
     * Map property managingOrganization
     */
    protected function mapManagingOrganization(): void
    {
        $this->managingOrganization = $this->object_mapping->mapManagingOrganization();
    }

    /**
     * Map property partOf
     */
    protected function mapPartOf(): void
    {
        $this->partOf = $this->object_mapping->mapPartOf();
    }

    /**
     * Map property hoursOfOperation
     */
    protected function mapHoursOfOperation(): void
    {
        $this->hoursOfOperation = $this->object_mapping->mapHoursOfOperation();
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

    /**
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function getPhysicalType(): ?CFHIRDataTypeCodeableConcept
    {
        return $this->physicalType;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept|null $physicalType
     *
     * @return CFHIRResourceLocation
     */
    public function setPhysicalType(?CFHIRDataTypeCodeableConcept $physicalType): CFHIRResourceLocation
    {
        $this->physicalType = $physicalType;

        return $this;
    }

    /**
     * @return CFHIRDataTypeLocationPosition|null
     */
    public function getPosition(): ?CFHIRDataTypeLocationPosition
    {
        return $this->position;
    }

    /**
     * @param CFHIRDataTypeLocationPosition|null $position
     *
     * @return CFHIRResourceLocation
     */
    public function setPosition(?CFHIRDataTypeLocationPosition $position): CFHIRResourceLocation
    {
        $this->position = $position;

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
     * @param CFHIRDataTypeReference|null $managingOrganization
     *
     * @return CFHIRResourceLocation
     */
    public function setManagingOrganization(?CFHIRDataTypeReference $managingOrganization): CFHIRResourceLocation
    {
        $this->managingOrganization = $managingOrganization;

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
     * @param CFHIRDataTypeReference|null $partOf
     *
     * @return CFHIRResourceLocation
     */
    public function setPartOf(?CFHIRDataTypeReference $partOf): CFHIRResourceLocation
    {
        $this->partOf = $partOf;

        return $this;
    }

    /**
     * @return CFHIRDataTypeLocationHoursOfOperation[]
     */
    public function getHoursOfOperation(): array
    {
        return $this->hoursOfOperation;
    }

    /**
     * @param CFHIRDataTypeLocationHoursOfOperation ...$hoursOfOperation
     *
     * @return CFHIRResourceLocation
     */
    public function setHoursOfOperation(
        CFHIRDataTypeLocationHoursOfOperation ...$hoursOfOperation
    ): CFHIRResourceLocation {
        $this->hoursOfOperation = $hoursOfOperation;

        return $this;
    }

    /**
     * @param CFHIRDataTypeLocationHoursOfOperation ...$hoursOfOperation
     *
     * @return CFHIRResourceLocation
     */
    public function addHoursOfOperation(
        CFHIRDataTypeLocationHoursOfOperation ...$hoursOfOperation
    ): CFHIRResourceLocation {
        $this->hoursOfOperation = array_merge($this->hoursOfOperation, $hoursOfOperation);

        return $this;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getAvailabilityExceptions(): ?CFHIRDataTypeString
    {
        return $this->availabilityExceptions;
    }

    /**
     * @param CFHIRDataTypeString|null $availabilityExceptions
     *
     * @return CFHIRResourceLocation
     */
    public function setAvailabilityExceptions(?CFHIRDataTypeString $availabilityExceptions): CFHIRResourceLocation
    {
        $this->availabilityExceptions = $availabilityExceptions;

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
     * @param CFHIRDataTypeReference ...$endpoint
     *
     * @return CFHIRResourceLocation
     */
    public function setEndpoint(CFHIRDataTypeReference ...$endpoint): CFHIRResourceLocation
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$endpoint
     *
     * @return CFHIRResourceLocation
     */
    public function addEndpoint(CFHIRDataTypeReference ...$endpoint): CFHIRResourceLocation
    {
        $this->endpoint = array_merge($this->endpoint, $endpoint);

        return $this;
    }
}
