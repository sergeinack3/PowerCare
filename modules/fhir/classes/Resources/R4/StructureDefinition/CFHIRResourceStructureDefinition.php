<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\StructureDefinition;

use Ox\Interop\Fhir\Contracts\Mapping\R4\StructureDefinitionMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourceStructureDefinitionInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCanonical;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeMarkdown;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactDetail;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeUsageContext;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;

/**
 * FIHR patient resource
 */
class CFHIRResourceStructureDefinition extends CFHIRDomainResource implements ResourceStructureDefinitionInterface
{
    // constants
    /** @var string */
    public const RESOURCE_TYPE = "StructureDefinition";

    protected ?CFHIRDataTypeUri $url = null;

    protected ?CFHIRDataTypeString $version = null;

    protected ?CFHIRDataTypeString $name = null;

    protected ?CFHIRDataTypeString $title = null;

    protected ?CFHIRDataTypeCode $status = null;

    protected ?CFHIRDataTypeBoolean $experimental = null;

    protected ?CFHIRDataTypeDateTime $date = null;

    protected ?CFHIRDataTypeString $publisher = null;

    protected ?CFHIRDataTypeMarkdown $description = null;

    protected ?CFHIRDataTypeMarkdown $purpose = null;

    protected ?CFHIRDataTypeMarkdown $copyright = null;

    /**
     * @var CFHIRDataTypeContactDetail[]
     */
    protected array $contact = [];

    /**
     * @var CFHIRDataTypeUsageContext[]
     */
    protected array $useContext = [];

    /**
     * @var CFHIRDataTypeCodeableConcept[]
     */
    protected array $jurisdiction = [];

    /**
     * @var CFHIRDataTypeCoding[]
     */
    protected array $keyword = [];

    protected ?CFHIRDataTypeCode $fhirVersion = null;

    protected ?CFHIRDataTypeCode $kind = null;

    protected ?CFHIRDataTypeBoolean $abstract = null;

    /**
     * @var CFHIRDataTypeString[]
     */
    protected array $contextInvariant = [];

    protected ?CFHIRDataTypeUri $type = null;

    protected ?CFHIRDataTypeCanonical $baseDefinition = null;

    protected ?CFHIRDataTypeCode $derivation = null;

    /** @var StructureDefinitionMappingInterface */
    protected $object_mapping;

    /**
     * @param CFHIRDataTypeUri|null $url
     *
     * @return CFHIRResourceStructureDefinition
     */
    public function setUrl(?CFHIRDataTypeUri $url): CFHIRResourceStructureDefinition
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
     * @return CFHIRResourceStructureDefinition
     */
    public function setVersion(?CFHIRDataTypeString $version): CFHIRResourceStructureDefinition
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
     * @return CFHIRResourceStructureDefinition
     */
    public function setName(?CFHIRDataTypeString $name): CFHIRResourceStructureDefinition
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
     * @param CFHIRDataTypeString|null $title
     *
     * @return CFHIRResourceStructureDefinition
     */
    public function setTitle(?CFHIRDataTypeString $title): CFHIRResourceStructureDefinition
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getTitle(): ?CFHIRDataTypeString
    {
        return $this->title;
    }

    /**
     * @param CFHIRDataTypeCode|null $status
     *
     * @return CFHIRResourceStructureDefinition
     */
    public function setStatus(?CFHIRDataTypeCode $status): CFHIRResourceStructureDefinition
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
     * @param CFHIRDataTypeBoolean|null $experimental
     *
     * @return CFHIRResourceStructureDefinition
     */
    public function setExperimental(?CFHIRDataTypeBoolean $experimental): CFHIRResourceStructureDefinition
    {
        $this->experimental = $experimental;

        return $this;
    }

    /**
     * @return CFHIRDataTypeBoolean|null
     */
    public function getExperimental(): ?CFHIRDataTypeBoolean
    {
        return $this->experimental;
    }

    /**
     * @param CFHIRDataTypeDateTime|null $date
     *
     * @return CFHIRResourceStructureDefinition
     */
    public function setDate(?CFHIRDataTypeDateTime $date): CFHIRResourceStructureDefinition
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return CFHIRDataTypeDateTime|null
     */
    public function getDate(): ?CFHIRDataTypeDateTime
    {
        return $this->date;
    }

    /**
     * @param CFHIRDataTypeString|null $publisher
     *
     * @return CFHIRResourceStructureDefinition
     */
    public function setPublisher(?CFHIRDataTypeString $publisher): CFHIRResourceStructureDefinition
    {
        $this->publisher = $publisher;

        return $this;
    }

    /**
     * @return CFHIRDataTypeString|null
     */
    public function getPublisher(): ?CFHIRDataTypeString
    {
        return $this->publisher;
    }

    /**
     * @param CFHIRDataTypeMarkdown|null $description
     *
     * @return CFHIRResourceStructureDefinition
     */
    public function setDescription(?CFHIRDataTypeMarkdown $description): CFHIRResourceStructureDefinition
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return CFHIRDataTypeMarkdown|null
     */
    public function getDescription(): ?CFHIRDataTypeMarkdown
    {
        return $this->description;
    }

    /**
     * @param CFHIRDataTypeMarkdown|null $purpose
     *
     * @return CFHIRResourceStructureDefinition
     */
    public function setPurpose(?CFHIRDataTypeMarkdown $purpose): CFHIRResourceStructureDefinition
    {
        $this->purpose = $purpose;

        return $this;
    }

    /**
     * @return CFHIRDataTypeMarkdown|null
     */
    public function getPurpose(): ?CFHIRDataTypeMarkdown
    {
        return $this->purpose;
    }

    /**
     * @param CFHIRDataTypeMarkdown|null $copyright
     *
     * @return CFHIRResourceStructureDefinition
     */
    public function setCopyright(?CFHIRDataTypeMarkdown $copyright): CFHIRResourceStructureDefinition
    {
        $this->copyright = $copyright;

        return $this;
    }

    /**
     * @return CFHIRDataTypeMarkdown|null
     */
    public function getCopyright(): ?CFHIRDataTypeMarkdown
    {
        return $this->copyright;
    }

    /**
     * @param CFHIRDataTypeCode|null $fhirVersion
     *
     * @return CFHIRResourceStructureDefinition
     */
    public function setFhirVersion(?CFHIRDataTypeCode $fhirVersion): CFHIRResourceStructureDefinition
    {
        $this->fhirVersion = $fhirVersion;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function getFHIRVersion(): ?CFHIRDataTypeCode
    {
        return $this->fhirVersion;
    }

    /**
     * @param CFHIRDataTypeCode|null $kind
     *
     * @return CFHIRResourceStructureDefinition
     */
    public function setKind(?CFHIRDataTypeCode $kind): CFHIRResourceStructureDefinition
    {
        $this->kind = $kind;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function getKind(): ?CFHIRDataTypeCode
    {
        return $this->kind;
    }

    /**
     * @param CFHIRDataTypeBoolean|null $abstract
     *
     * @return CFHIRResourceStructureDefinition
     */
    public function setAbstract(?CFHIRDataTypeBoolean $abstract): CFHIRResourceStructureDefinition
    {
        $this->abstract = $abstract;

        return $this;
    }

    /**
     * @return CFHIRDataTypeBoolean|null
     */
    public function getAbstract(): ?CFHIRDataTypeBoolean
    {
        return $this->abstract;
    }

    /**
     * @param CFHIRDataTypeUri|null $type
     *
     * @return CFHIRResourceStructureDefinition
     */
    public function setType(?CFHIRDataTypeUri $type): CFHIRResourceStructureDefinition
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return CFHIRDataTypeUri|null
     */
    public function getType(): ?CFHIRDataTypeUri
    {
        return $this->type;
    }

    /**
     * @param CFHIRDataTypeCanonical|null $baseDefinition
     *
     * @return CFHIRResourceStructureDefinition
     */
    public function setBaseDefinition(?CFHIRDataTypeCanonical $baseDefinition): CFHIRResourceStructureDefinition
    {
        $this->baseDefinition = $baseDefinition;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCanonical|null
     */
    public function getBaseDefinition(): ?CFHIRDataTypeCanonical
    {
        return $this->baseDefinition;
    }

    /**
     * @param CFHIRDataTypeCode|null $derivation
     *
     * @return CFHIRResourceStructureDefinition
     */
    public function setDerivation(?CFHIRDataTypeCode $derivation): CFHIRResourceStructureDefinition
    {
        $this->derivation = $derivation;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function getDerivation(): ?CFHIRDataTypeCode
    {
        return $this->derivation;
    }

    /**
     * * Map property url
     */
    protected function mapUrl(): void
    {
        $this->url = $this->object_mapping->mapUrl();
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
     * * Map property keyword
     */
    protected function mapKeyword(): void
    {
        $this->keyword = $this->object_mapping->mapKeyword();
    }

    /**
     * * Map property fhirVersion
     */
    protected function mapFhirVersion(): void
    {
        $this->fhirVersion = $this->object_mapping->mapFhirVersion();
    }

    /**
     * * Map property kind
     */
    protected function mapKind(): void
    {
        $this->kind = $this->object_mapping->mapKind();
    }

    /**
     * * Map property abstract
     */
    protected function mapAbstract(): void
    {
        $this->abstract = $this->object_mapping->mapAbstract();
    }

    /**
     * * Map property contextInvariant
     */
    protected function mapContextInvariant(): void
    {
        $this->contextInvariant = $this->object_mapping->mapContextInvariant();
    }

    /**
     * * Map property type
     */
    protected function mapType(): void
    {
        $this->type = $this->object_mapping->mapType();
    }

    /**
     * * Map property baseDefinition
     */
    protected function mapBaseDefinition(): void
    {
        $this->baseDefinition = $this->object_mapping->mapBaseDefinition();
    }

    /**
     * * Map property derivation
     */
    protected function mapDerivation(): void
    {
        $this->derivation = $this->object_mapping->mapDerivation();
    }

    /**
     * @param CFHIRDataTypeContactDetail ...$contact
     *
     * @return self
     */
    public function setContact(CFHIRDataTypeContactDetail ...$contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @param CFHIRDataTypeContactDetail ...$contact
     *
     * @return self
     */
    public function addContact(CFHIRDataTypeContactDetail ...$contact): self
    {
        $this->contact = array_merge($this->contact, $contact);

        return $this;
    }

    /**
     * @return CFHIRDataTypeContactDetail[]
     */
    public function getContact(): array
    {
        return $this->contact;
    }

    /**
     * @param CFHIRDataTypeUsageContext ...$useContext
     *
     * @return self
     */
    public function setUseContext(CFHIRDataTypeUsageContext ...$useContext): self
    {
        $this->useContext = $useContext;

        return $this;
    }

    /**
     * @param CFHIRDataTypeUsageContext ...$useContext
     *
     * @return self
     */
    public function addUseContext(CFHIRDataTypeUsageContext ...$useContext): self
    {
        $this->useContext = array_merge($this->useContext, $useContext);

        return $this;
    }

    /**
     * @return CFHIRDataTypeUsageContext[]
     */
    public function getUseContext(): array
    {
        return $this->useContext;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$jurisdiction
     *
     * @return self
     */
    public function setJurisdiction(CFHIRDataTypeCodeableConcept ...$jurisdiction): self
    {
        $this->jurisdiction = $jurisdiction;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$jurisdiction
     *
     * @return self
     */
    public function addJurisdiction(CFHIRDataTypeCodeableConcept ...$jurisdiction): self
    {
        $this->jurisdiction = array_merge($this->jurisdiction, $jurisdiction);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function getJurisdiction(): array
    {
        return $this->jurisdiction;
    }

    /**
     * @param CFHIRDataTypeCoding ...$keyword
     *
     * @return self
     */
    public function setKeyword(CFHIRDataTypeCoding ...$keyword): self
    {
        $this->keyword = $keyword;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCoding ...$keyword
     *
     * @return self
     */
    public function addKeyword(CFHIRDataTypeCoding ...$keyword): self
    {
        $this->keyword = array_merge($this->keyword, $keyword);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCoding[]
     */
    public function getKeyword(): array
    {
        return $this->keyword;
    }

    /**
     * @param CFHIRDataTypeString ...$contextInvariant
     *
     * @return self
     */
    public function setContextInvariant(CFHIRDataTypeString ...$contextInvariant): self
    {
        $this->contextInvariant = $contextInvariant;

        return $this;
    }

    /**
     * @param CFHIRDataTypeString ...$contextInvariant
     *
     * @return self
     */
    public function addContextInvariant(CFHIRDataTypeString ...$contextInvariant): self
    {
        $this->contextInvariant = array_merge($this->contextInvariant, $contextInvariant);

        return $this;
    }

    /**
     * @return CFHIRDataTypeString[]
     */
    public function getContextInvariant(): array
    {
        return $this->contextInvariant;
    }
}
