<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Organization;

use Ox\Interop\Fhir\Contracts\Mapping\R4\OrganizationMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourceOrganizationInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Organization\CFHIRDataTypeOrganizationContact;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAddress;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
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
 * FHIR organization resource
 */
class CFHIRResourceOrganization extends CFHIRDomainResource implements ResourceOrganizationInterface
{
    // constants
    /** @var string */
    public const RESOURCE_TYPE = 'Organization';

    protected ?CFHIRDataTypeBoolean $active = null;

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $type = [];

    protected ?CFHIRDataTypeString $name = null;

    /** @var CFHIRDataTypeString[] */
    protected array $alias = [];

    /** @var CFHIRDataTypeContactPoint[] */
    protected array $telecom = [];

    /** @var CFHIRDataTypeAddress[] */
    protected array $address = [];

    protected ?CFHIRDataTypeReference $partOf = null;

    /** @var CFHIRDataTypeOrganizationContact[] */
    protected array $contact = [];

    /** @var CFHIRDataTypeReference[] */
    protected array $endpoint = [];

    /** @var OrganizationMappingInterface */
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
     * @return CFHIRResourceOrganization
     */
    public function setActive(?CFHIRDataTypeBoolean $active): CFHIRResourceOrganization
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
     * @param CFHIRDataTypeString|null $name
     *
     * @return CFHIRResourceOrganization
     */
    public function setName(?CFHIRDataTypeString $name): CFHIRResourceOrganization
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
     * @param CFHIRDataTypeReference|null $partOf
     *
     * @return CFHIRResourceOrganization
     */
    public function setPartOf(?CFHIRDataTypeReference $partOf): CFHIRResourceOrganization
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
     * @param CFHIRDataTypeReference ...$endpoint
     *
     * @return CFHIRResourceOrganization
     */
    public function setEndpoint(CFHIRDataTypeReference ...$endpoint): CFHIRResourceOrganization
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$endpoint
     *
     * @return CFHIRResourceOrganization
     */
    public function addEndpoint(CFHIRDataTypeReference ...$endpoint): CFHIRResourceOrganization
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
     * Map property active
     */
    protected function mapActive(): void
    {
        $this->active = $this->object_mapping->mapActive();
    }

    /**
     * Map property type
     */
    protected function mapType(): void
    {
        $this->type = $this->object_mapping->mapType();
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
     * Map property partOf
     */
    protected function mapPartOf(): void
    {
        $this->partOf = $this->object_mapping->mapPartOf();
    }

    /**
     * Map property contact
     */
    protected function mapContact(): void
    {
        $this->contact = $this->object_mapping->mapContact();
    }

    /**
     * Map property endpoint
     */
    protected function mapEndpoint(): void
    {
        $this->endpoint = $this->object_mapping->mapEndpoint();
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$type
     *
     * @return self
     */
    public function setType(CFHIRDataTypeCodeableConcept ...$type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$type
     *
     * @return self
     */
    public function addType(CFHIRDataTypeCodeableConcept ...$type): self
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
     * @param CFHIRDataTypeString ...$alias
     *
     * @return self
     */
    public function setAlias(CFHIRDataTypeString ...$alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * @param CFHIRDataTypeString ...$alias
     *
     * @return self
     */
    public function addAlias(CFHIRDataTypeString ...$alias): self
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
     * @param CFHIRDataTypeOrganizationContact ...$contact
     *
     * @return self
     */
    public function setContact(CFHIRDataTypeOrganizationContact ...$contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @param CFHIRDataTypeOrganizationContact ...$contacts
     *
     * @return self
     */
    public function addContact(CFHIRDataTypeOrganizationContact ...$contacts): self
    {
        $this->contact = array_merge($this->contact, $contacts);

        return $this;
    }

    /**
     * @return CFHIRDataTypeOrganizationContact[]
     */
    public function getContact(): array
    {
        return $this->contact;
    }
}
