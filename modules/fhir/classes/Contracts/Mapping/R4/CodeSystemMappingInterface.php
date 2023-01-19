<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Mapping\R4;

use Ox\Interop\Fhir\Contracts\Mapping\ResourceDomainMappingInterface;
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
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeUsageContext;

/**
 * Description
 */
interface CodeSystemMappingInterface extends ResourceDomainMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "CodeSystem";

    /**
     * Map property url
     *
     *      @return CFHIRDataTypeUri|null
     */
    public function mapUrl(): ?CFHIRDataTypeUri;

    /**
     * Map property identifier
     *
     *      @return CFHIRDataTypeIdentifier[]
     */
    public function mapIdentifier(): array;

    /**
     * Map property version
     *
     *      @return CFHIRDataTypeString|null
     */
    public function mapVersion(): ?CFHIRDataTypeString;

    /**
     * Map property name
     *
     *      @return CFHIRDataTypeString|null
     */
    public function mapName(): ?CFHIRDataTypeString;

    /**
     * Map property title
     *
     *      @return CFHIRDataTypeString|null
     */
    public function mapTitle(): ?CFHIRDataTypeString;

    /**
     * Map property status
     *
     *      @return CFHIRDataTypeCode
     */
    public function mapStatus(): ?CFHIRDataTypeCode;

    /**
     * Map property experimental
     *
     *      @return CFHIRDataTypeBoolean|null
     */
    public function mapExperimental(): ?CFHIRDataTypeBoolean;

    /**
     * Map property date
     *
     *      @return CFHIRDataTypeDateTime|null
     */
    public function mapDate(): ?CFHIRDataTypeDateTime;

    /**
     * Map property publisher
     *
     *      @return CFHIRDataTypeString|null
     */
    public function mapPublisher(): ?CFHIRDataTypeString;

    /**
     * Map property contact
     *
     *      @return CFHIRDataTypeContactDetail[]
     */
    public function mapContact(): array;

    /**
     * Map property description
     *
     *      @return CFHIRDataTypeMarkdown|null
     */
    public function mapDescription(): ?CFHIRDataTypeMarkdown;

    /**
     * Map property useContext
     *
     *      @return CFHIRDataTypeUsageContext[]
     */
    public function mapUseContext(): array;

    /**
     * Map property jurisdiction
     *
     *      @return CFHIRDataTypeCodeableConcept[]
     */
    public function mapJurisdiction(): array;

    /**
     * Map property purpose
     *
     *      @return CFHIRDataTypeMarkdown|null
     */
    public function mapPurpose(): ?CFHIRDataTypeMarkdown;

    /**
     * Map property copyright
     *
     *      @return CFHIRDataTypeMarkdown
     */
    public function mapCopyright(): CFHIRDataTypeMarkdown;


    /**
     * Map property caseSensitive
     *
     *      @return CFHIRDataTypeBoolean|null
     */
    public function mapCaseSensitive(): ?CFHIRDataTypeBoolean;

    /**
     * Map property valueSet
     *
     *      @return CFHIRDataTypeCanonical|null
     */
    public function mapValueSet(): ?CFHIRDataTypeCanonical;

    /**
     * Map property hierarchyMeaning
     *
     *      @return CFHIRDataTypeCode|null
     */
    public function mapHierarchyMeaning(): ?CFHIRDataTypeCode;

    /**
     * Map property compositional
     *
     *      @return CFHIRDataTypeBoolean|null
     */
    public function mapCompositional(): ?CFHIRDataTypeBoolean;

    /**
     * Map property versionNeeded
     *
     *      @return CFHIRDataTypeBoolean|null
     */
    public function mapVersionNeeded(): ?CFHIRDataTypeBoolean;

    /**
     * Map property content
     *
     *      @return CFHIRDataTypeCode|null
     */
    public function mapContent(): ?CFHIRDataTypeCode;

    /**
     * Map property supplements
     *
     *      @return CFHIRDataTypeCanonical|null
     */
    public function mapSupplements(): ?CFHIRDataTypeCanonical;

    /**
     * Map property count
     *
     *      @return CFHIRDataTypeUnsignedInt|null
     */
    public function mapCount(): ?CFHIRDataTypeUnsignedInt;

    /**
     * Map property filter
     *
     *      @return CFHIRDataTypeCodeSystemFilter[]
     */
    public function mapFilter(): array;

    /**
     * Map property property
     *
     *      @return CFHIRDataTypeCodeSystemProperty[]
     */
    public function mapProperty(): array;

    /**
     * Map property concept
     *
     *      @return CFHIRDataTypeCodeSystemConcept[]
     */
    public function mapConcept(): array;
}
