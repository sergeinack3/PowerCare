<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\CodeSystem;

use Ox\Interop\Fhir\Contracts\Mapping\R4\CodeSystemMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourceCodeSystemInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCanonical;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeMarkdown;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUnsignedInt;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CodeSystem\CFHIRDataTypeCodeSystemConcept;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CodeSystem\CFHIRDataTypeCodeSystemFilter;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CodeSystem\CFHIRDataTypeCodeSystemProperty;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactDetail;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeUsageContext;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;

/**
 * FHIR patient resource
 */
class CFHIRResourceCodeSystem extends CFHIRDomainResource implements ResourceCodeSystemInterface
{
    /** @var string */
    public const RESOURCE_TYPE = 'CodeSystem';

    protected ?CFHIRDataTypeUri $url = null;

    protected ?CFHIRDataTypeString $version = null;

    protected ?CFHIRDataTypeString $name = null;

    protected ?CFHIRDataTypeString $title = null;

    protected ?CFHIRDataTypeCode $status = null;

    protected ?CFHIRDataTypeBoolean $experimental = null;

    protected ?CFHIRDataTypeDateTime $date = null;

    protected ?CFHIRDataTypeString $publisher = null;

    /** @var CFHIRDataTypeContactDetail[] */
    protected array $contact = [];

    protected ?CFHIRDataTypeMarkdown $description = null;

    /** @var CFHIRDataTypeUsageContext[] */
    protected array $useContext = [];

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $jurisdiction = [];

    protected ?CFHIRDataTypeMarkdown $purpose = null;

    protected ?CFHIRDataTypeMarkdown $copyright = null;

    protected ?CFHIRDataTypeBoolean $caseSensitive = null;

    protected ?CFHIRDataTypeCanonical $valueSet = null;

    protected ?CFHIRDataTypeCode $hierarchyMeaning = null;

    protected ?CFHIRDataTypeBoolean $compositional = null;

    protected ?CFHIRDataTypeBoolean $versionNeeded = null;

    protected ?CFHIRDataTypeCode $content = null;

    protected ?CFHIRDataTypeCanonical $supplements = null;

    protected ?CFHIRDataTypeUnsignedInt $count = null;

    /** @var CFHIRDataTypeCodeSystemFilter[] */
    protected array $filter = [];

    /** @var CFHIRDataTypeCodeSystemProperty[] */
    protected array $property = [];

    /** @var CFHIRDataTypeCodeSystemConcept[] */
    protected array $concept = [];

    /** @var CodeSystemMappingInterface */
    protected $object_mapping;

    /**
     * @param CFHIRDataTypeUri|null $url
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setUrl(?CFHIRDataTypeUri $url): CFHIRResourceCodeSystem
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
     * @param CFHIRDataTypeString|null $version
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setVersion(?CFHIRDataTypeString $version): CFHIRResourceCodeSystem
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getVersion(): ?CFHIRDataTypeString
    {
        return $this->version;
    }

    /**
     * @param CFHIRDataTypeString|null $name
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setName(?CFHIRDataTypeString $name): CFHIRResourceCodeSystem
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param CFHIRDataTypeString|null $title
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setTitle(?CFHIRDataTypeString $title): CFHIRResourceCodeSystem
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCode|null $status
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setStatus(?CFHIRDataTypeCode $status): CFHIRResourceCodeSystem
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @param CFHIRDataTypeBoolean|null $experimental
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setExperimental(?CFHIRDataTypeBoolean $experimental): CFHIRResourceCodeSystem
    {
        $this->experimental = $experimental;

        return $this;
    }

    /**
     * @param CFHIRDataTypeDateTime|null $date
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setDate(?CFHIRDataTypeDateTime $date): CFHIRResourceCodeSystem
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @param CFHIRDataTypeString|null $publisher
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setPublisher(?CFHIRDataTypeString $publisher): CFHIRResourceCodeSystem
    {
        $this->publisher = $publisher;

        return $this;
    }

    /**
     * @param CFHIRDataTypeContactDetail ...$contact
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setContact(CFHIRDataTypeContactDetail ...$contact): CFHIRResourceCodeSystem
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @param CFHIRDataTypeContactDetail ...$contact
     *
     * @return CFHIRResourceCodeSystem
     */
    public function addContact(CFHIRDataTypeContactDetail ...$contact): CFHIRResourceCodeSystem
    {
        $this->contact = array_merge($this->contact, $contact);

        return $this;
    }


    /**
     * @param CFHIRDataTypeMarkdown|null $description
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setDescription(?CFHIRDataTypeMarkdown $description): CFHIRResourceCodeSystem
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param CFHIRDataTypeUsageContext ...$useContext
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setUseContext(CFHIRDataTypeUsageContext ...$useContext): CFHIRResourceCodeSystem
    {
        $this->useContext = $useContext;

        return $this;
    }

    /**
     * @param CFHIRDataTypeUsageContext ...$useContext
     *
     * @return CFHIRResourceCodeSystem
     */
    public function addUseContext(CFHIRDataTypeUsageContext ...$useContext): CFHIRResourceCodeSystem
    {
        $this->useContext = array_merge($this->useContext, $useContext);

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$jurisdiction
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setJurisdiction(CFHIRDataTypeCodeableConcept ...$jurisdiction): CFHIRResourceCodeSystem
    {
        $this->jurisdiction = $jurisdiction;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$jurisdiction
     *
     * @return CFHIRResourceCodeSystem
     */
    public function addJurisdiction(CFHIRDataTypeCodeableConcept ...$jurisdiction): CFHIRResourceCodeSystem
    {
        $this->jurisdiction = array_merge($this->jurisdiction, $jurisdiction);

        return $this;
    }

    /**
     * @param CFHIRDataTypeMarkdown|null $purpose
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setPurpose(?CFHIRDataTypeMarkdown $purpose): CFHIRResourceCodeSystem
    {
        $this->purpose = $purpose;

        return $this;
    }

    /**
     * @param CFHIRDataTypeMarkdown|null $copyright
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setCopyright(?CFHIRDataTypeMarkdown $copyright): CFHIRResourceCodeSystem
    {
        $this->copyright = $copyright;

        return $this;
    }

    /**
     * @param CFHIRDataTypeBoolean|null $caseSensitive
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setCaseSensitive(?CFHIRDataTypeBoolean $caseSensitive): CFHIRResourceCodeSystem
    {
        $this->caseSensitive = $caseSensitive;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCanonical|null $valueSet
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setValueSet(?CFHIRDataTypeCanonical $valueSet): CFHIRResourceCodeSystem
    {
        $this->valueSet = $valueSet;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCode|null $hierarchyMeaning
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setHierarchyMeaning(?CFHIRDataTypeCode $hierarchyMeaning): CFHIRResourceCodeSystem
    {
        $this->hierarchyMeaning = $hierarchyMeaning;

        return $this;
    }

    /**
     * @param CFHIRDataTypeBoolean|null $compositional
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setCompositional(?CFHIRDataTypeBoolean $compositional): CFHIRResourceCodeSystem
    {
        $this->compositional = $compositional;

        return $this;
    }

    /**
     * @param CFHIRDataTypeBoolean|null $versionNeeded
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setVersionNeeded(?CFHIRDataTypeBoolean $versionNeeded): CFHIRResourceCodeSystem
    {
        $this->versionNeeded = $versionNeeded;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCode|null $content
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setContent(?CFHIRDataTypeCode $content): CFHIRResourceCodeSystem
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCanonical|null $supplements
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setSupplements(?CFHIRDataTypeCanonical $supplements): CFHIRResourceCodeSystem
    {
        $this->supplements = $supplements;

        return $this;
    }

    /**
     * @param CFHIRDataTypeUnsignedInt|null $count
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setCount(?CFHIRDataTypeUnsignedInt $count): CFHIRResourceCodeSystem
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeSystemFilter ...$filter
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setFilter(CFHIRDataTypeCodeSystemFilter ...$filter): CFHIRResourceCodeSystem
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeSystemFilter ...$filter
     *
     * @return CFHIRResourceCodeSystem
     */
    public function addFilter(CFHIRDataTypeCodeSystemFilter ...$filter): CFHIRResourceCodeSystem
    {
        $this->filter = array_merge($this->filter, $filter);

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeSystemProperty ...$property
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setProperty(CFHIRDataTypeCodeSystemProperty ...$property): CFHIRResourceCodeSystem
    {
        $this->property = $property;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeSystemProperty ...$property
     *
     * @return CFHIRResourceCodeSystem
     */
    public function addProperty(CFHIRDataTypeCodeSystemProperty ...$property): CFHIRResourceCodeSystem
    {
        $this->property = array_merge($this->property, $property);

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeSystemConcept ...$concept
     *
     * @return CFHIRResourceCodeSystem
     */
    public function setConcept(CFHIRDataTypeCodeSystemConcept ...$concept): CFHIRResourceCodeSystem
    {
        $this->concept = $concept;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeSystemConcept ...$concept
     *
     * @return CFHIRResourceCodeSystem
     */
    public function addConcept(CFHIRDataTypeCodeSystemConcept ...$concept): CFHIRResourceCodeSystem
    {
        $this->concept = array_merge($this->concept, $concept);

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
     * @return CFHIRDataTypeString|null
     */
    public function getTitle(): ?CFHIRDataTypeString
    {
        return $this->title;
    }

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function getStatus(): ?CFHIRDataTypeCode
    {
        return $this->status;
    }

    /**
     * @return CFHIRDataTypeBoolean|null
     */
    public function getExperimental(): ?CFHIRDataTypeBoolean
    {
        return $this->experimental;
    }

    /**
     * @return CFHIRDataTypeDateTime|null
     */
    public function getDate(): ?CFHIRDataTypeDateTime
    {
        return $this->date;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getPublisher(): ?CFHIRDataTypeString
    {
        return $this->publisher;
    }

    /**
     * @return array
     */
    public function getContact(): array
    {
        return $this->contact;
    }

    /**
     * @return CFHIRDataTypeMarkdown|null
     */
    public function getDescription(): ?CFHIRDataTypeMarkdown
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function getUseContext(): array
    {
        return $this->useContext;
    }

    /**
     * @return array
     */
    public function getJurisdiction(): array
    {
        return $this->jurisdiction;
    }

    /**
     * @return CFHIRDataTypeMarkdown|null
     */
    public function getPurpose(): ?CFHIRDataTypeMarkdown
    {
        return $this->purpose;
    }

    /**
     * @return CFHIRDataTypeMarkdown|null
     */
    public function getCopyright(): ?CFHIRDataTypeMarkdown
    {
        return $this->copyright;
    }

    /**
     * @return CFHIRDataTypeBoolean|null
     */
    public function getCaseSensitive(): ?CFHIRDataTypeBoolean
    {
        return $this->caseSensitive;
    }

    /**
     * @return CFHIRDataTypeCanonical|null
     */
    public function getValueSet(): ?CFHIRDataTypeCanonical
    {
        return $this->valueSet;
    }

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function getHierarchyMeaning(): ?CFHIRDataTypeCode
    {
        return $this->hierarchyMeaning;
    }

    /**
     * @return CFHIRDataTypeBoolean|null
     */
    public function getCompositional(): ?CFHIRDataTypeBoolean
    {
        return $this->compositional;
    }

    /**
     * @return CFHIRDataTypeBoolean|null
     */
    public function getVersionNeeded(): ?CFHIRDataTypeBoolean
    {
        return $this->versionNeeded;
    }

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function getContent(): ?CFHIRDataTypeCode
    {
        return $this->content;
    }

    /**
     * @return CFHIRDataTypeCanonical|null
     */
    public function getSupplements(): ?CFHIRDataTypeCanonical
    {
        return $this->supplements;
    }

    /**
     * @return CFHIRDataTypeUnsignedInt|null
     */
    public function getCount(): ?CFHIRDataTypeUnsignedInt
    {
        return $this->count;
    }

    /**
     * @return CFHIRDataTypeCodeSystemFilter[]
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    /**
     * @return array
     */
    public function getProperty(): array
    {
        return $this->property;
    }

    /**
     * @return array
     */
    public function getConcept(): array
    {
        return $this->concept;
    }

    /**
     * * Map property url
     */
    protected function mapUrl(): void
    {
        $this->url = $this->object_mapping->mapUrl();
    }

    /**
     * * Map property identifier
     */
    protected function mapIdentifier(): void
    {
        $this->identifier = $this->object_mapping->mapIdentifier();
    }

    /**
     * * Map property version
     */
    protected function mapVersion(): void
    {
        $this->version = $this->object_mapping->mapVersion();
    }

    /**
     * * Map property name
     */
    protected function mapName(): void
    {
        $this->name = $this->object_mapping->mapName();
    }

    /**
     * * Map property title
     */
    protected function mapTitle(): void
    {
        $this->title = $this->object_mapping->mapTitle();
    }

    /**
     * * Map property status
     */
    protected function mapStatus(): void
    {
        $this->status = $this->object_mapping->mapStatus();
    }

    /**
     * * Map property experimental
     */
    protected function mapExperimental(): void
    {
        $this->experimental = $this->object_mapping->mapExperimental();
    }

    /**
     * * Map property date
     */
    protected function mapDate(): void
    {
        $this->date = $this->object_mapping->mapDate();
    }

    /**
     * * Map property publisher
     */
    protected function mapPublisher(): void
    {
        $this->publisher = $this->object_mapping->mapPublisher();
    }

    /**
     * * Map property contact
     */
    protected function mapContact(): void
    {
        $this->contact = $this->object_mapping->mapContact();
    }

    /**
     * * Map property description
     */
    protected function mapDescription(): void
    {
        $this->description = $this->object_mapping->mapDescription();
    }

    /**
     * * Map property useContext
     */
    protected function mapUseContext(): void
    {
        $this->useContext = $this->object_mapping->mapUseContext();
    }

    /**
     * * Map property jurisdiction
     */
    protected function mapJurisdiction(): void
    {
        $this->jurisdiction = $this->object_mapping->mapJurisdiction();
    }

    /**
     * * Map property purpose
     */
    protected function mapPurpose(): void
    {
        $this->purpose = $this->object_mapping->mapPurpose();
    }

    /**
     * * Map property copyright
     */
    protected function mapCopyright(): void
    {
        $this->copyright = $this->object_mapping->mapCopyright();
    }

    /**
     * * Map property caseSensitive
     */
    protected function mapCaseSensitive(): void
    {
        $this->caseSensitive = $this->object_mapping->mapCaseSensitive();
    }

    /**
     * * Map property valueSet
     */
    protected function mapValueSet(): void
    {
        $this->valueSet = $this->object_mapping->mapValueSet();
    }

    /**
     * * Map property hierarchyMeaning
     */
    protected function mapHierarchyMeaning(): void
    {
        $this->hierarchyMeaning = $this->object_mapping->mapHierarchyMeaning();
    }

    /**
     * * Map property compositional
     */
    protected function mapCompositional(): void
    {
        $this->compositional = $this->object_mapping->mapCompositional();
    }

    /**
     * * Map property versionNeeded
     */
    protected function mapVersionNeeded(): void
    {
        $this->versionNeeded = $this->object_mapping->mapVersionNeeded();
    }

    /**
     * * Map property content
     */
    protected function mapContent(): void
    {
        $this->content = $this->object_mapping->mapContent();
    }

    /**
     * * Map property supplements
     */
    protected function mapSupplements(): void
    {
        $this->supplements = $this->object_mapping->mapSupplements();
    }

    /**
     * * Map property count
     */
    protected function mapCount(): void
    {
        $this->count = $this->object_mapping->mapCount();
    }

    /**
     * * Map property filter
     */
    protected function mapFilter(): void
    {
        $this->filter = $this->object_mapping->mapFilter();
    }

    /**
     * * Map property property
     */
    protected function mapProperty(): void
    {
        $this->property = $this->object_mapping->mapProperty();
    }

    /**
     * * Map property concept
     */
    protected function mapConcept(): void
    {
        $this->concept = $this->object_mapping->mapConcept();
    }
}
