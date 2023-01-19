<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\ValueSet;

use Ox\Interop\Fhir\Contracts\Mapping\R4\ValueSetMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourceValueSetInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeMarkdown;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\ValueSet\CFHIRDataTypeValueSetCompose;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\ValueSet\CFHIRDataTypeValueSetExpansion;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactDetail;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeUsageContext;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;

/**
 * FIHR patient resource
 */
class CFHIRResourceValueSet extends CFHIRDomainResource implements ResourceValueSetInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "ValueSet";

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

    /** @var CFHIRDataTypeUsageContext[]|null */
    protected array $useContext = [];

    /** @var CFHIRDataTypeCodeableConcept[]|null */
    protected array $jurisdiction = [];

    protected ?CFHIRDataTypeBoolean $immutable = null;

    protected ?CFHIRDataTypeMarkdown $purpose = null;

    protected ?CFHIRDataTypeMarkdown $copyright = null;

    protected ?CFHIRDataTypeValueSetCompose $compose = null;

    protected ?CFHIRDataTypeValueSetExpansion $expansion = null;

    /** @var ValueSetMappingInterface */
    protected $object_mapping;

    /**
     * Search a coding in the value set among code systems
     *
     * @param string $code_system
     * @param string $code
     *
     * @return CFHIRDataTypeCoding|null
     */
    public function searchCoding(string $code_system, string $code): ?CFHIRDataTypeCoding
    {
        if (!$this->compose || !$this->compose->include) {
            return null;
        }

        foreach ($this->compose->include as $include_code_system) {
            if (!$include_code_system->system->isSystemMatch($code_system) || !$include_code_system->concept) {
                continue;
            }

            foreach ($include_code_system->concept as $concept) {
                if ($concept->code && $concept->code->getValue() === $code) {
                    return (new CFHIRDataTypeCoding())
                        ->setSystem($include_code_system->system->getValue())
                        ->setCode($concept->code->getValue())
                        ->setDisplay($concept->display ? $concept->display->getValue() : null)
                        ->setVersion($include_code_system->version ? $include_code_system->version->getValue() : null);
                }
            }
        }

        return null;
    }

    /**
     * @param CFHIRDataTypeUri|null $url
     *
     * @return CFHIRResourceValueSet
     */
    public function setUrl(?CFHIRDataTypeUri $url): CFHIRResourceValueSet
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
     * @return CFHIRResourceValueSet
     */
    public function setVersion(?CFHIRDataTypeString $version): CFHIRResourceValueSet
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
     * @return CFHIRResourceValueSet
     */
    public function setName(?CFHIRDataTypeString $name): CFHIRResourceValueSet
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
     * @return CFHIRResourceValueSet
     */
    public function setTitle(?CFHIRDataTypeString $title): CFHIRResourceValueSet
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
     * @return CFHIRResourceValueSet
     */
    public function setStatus(?CFHIRDataTypeCode $status): CFHIRResourceValueSet
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
     * @return CFHIRResourceValueSet
     */
    public function setExperimental(?CFHIRDataTypeBoolean $experimental): CFHIRResourceValueSet
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
     * @return CFHIRResourceValueSet
     */
    public function setDate(?CFHIRDataTypeDateTime $date): CFHIRResourceValueSet
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
     * @return CFHIRResourceValueSet
     */
    public function setPublisher(?CFHIRDataTypeString $publisher): CFHIRResourceValueSet
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
     * @return CFHIRResourceValueSet
     */
    public function setDescription(?CFHIRDataTypeMarkdown $description): CFHIRResourceValueSet
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
     * @param CFHIRDataTypeBoolean|null $immutable
     *
     * @return CFHIRResourceValueSet
     */
    public function setImmutable(?CFHIRDataTypeBoolean $immutable): CFHIRResourceValueSet
    {
        $this->immutable = $immutable;

        return $this;
    }

    /**
     * @return CFHIRDataTypeBoolean|null
     */
    public function getImmutable(): ?CFHIRDataTypeBoolean
    {
        return $this->immutable;
    }

    /**
     * @param CFHIRDataTypeMarkdown|null $purpose
     *
     * @return CFHIRResourceValueSet
     */
    public function setPurpose(?CFHIRDataTypeMarkdown $purpose): CFHIRResourceValueSet
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
     * @return CFHIRResourceValueSet
     */
    public function setCopyright(?CFHIRDataTypeMarkdown $copyright): CFHIRResourceValueSet
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
     * @param CFHIRDataTypeValueSetCompose|null $compose
     *
     * @return CFHIRResourceValueSet
     */
    public function setCompose(?CFHIRDataTypeValueSetCompose $compose): CFHIRResourceValueSet
    {
        $this->compose = $compose;

        return $this;
    }

    /**
     * @return CFHIRDataTypeValueSetCompose|null
     */
    public function getCompose(): ?CFHIRDataTypeValueSetCompose
    {
        return $this->compose;
    }

    /**
     * @param CFHIRDataTypeValueSetExpansion|null $expansion
     *
     * @return CFHIRResourceValueSet
     */
    public function setExpansion(?CFHIRDataTypeValueSetExpansion $expansion): CFHIRResourceValueSet
    {
        $this->expansion = $expansion;

        return $this;
    }

    /**
     * @return CFHIRDataTypeValueSetExpansion|null
     */
    public function getExpansion(): ?CFHIRDataTypeValueSetExpansion
    {
        return $this->expansion;
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
     * * Map property immutable
     */
    protected function mapImmutable(): void
    {
        $this->immutable = $this->object_mapping->mapImmutable();
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
     * * Map property compose
     */
    protected function mapCompose(): void
    {
        $this->compose = $this->object_mapping->mapCompose();
    }

    /**
     * * Map property expansion
     */
    protected function mapExpansion(): void
    {
        $this->expansion = $this->object_mapping->mapExpansion();
    }
}
