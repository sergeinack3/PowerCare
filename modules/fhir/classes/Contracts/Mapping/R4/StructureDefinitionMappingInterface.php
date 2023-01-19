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
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactDetail;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeUsageContext;

/**
 * Description
 */
interface StructureDefinitionMappingInterface extends ResourceDomainMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "StructureDefinition";

    /**
     * Map property url
     *
     * @return CFHIRDataTypeUri
     */
    public function mapUrl(): ?CFHIRDataTypeUri;

    /**
     * Map property identifier
     *
     * @return CFHIRDataTypeIdentifier[]
     */
    public function mapIdentifier(): array;

    /**
     * Map property version
     *
     * @return CFHIRDataTypeString|null
     */
    public function mapVersion(): ?CFHIRDataTypeString;

    /**
     * Map property name
     *
     * @return CFHIRDataTypeString|null
     */
    public function mapName(): ?CFHIRDataTypeString;

    /**
     * Map property title
     *
     * @return CFHIRDataTypeString|null
     */
    public function mapTitle(): ?CFHIRDataTypeString;

    /**
     * Map property status
     *
     * @return CFHIRDataTypeCode|null
     */
    public function mapStatus(): ?CFHIRDataTypeCode;

    /**
     * Map property experimental
     *
     * @return CFHIRDataTypeBoolean|null
     */
    public function mapExperimental(): ?CFHIRDataTypeBoolean;

    /**
     * Map property date
     *
     * @return CFHIRDataTypeDateTime|null
     */
    public function mapDate(): ?CFHIRDataTypeDateTime;

    /**
     * Map property publisher
     *
     * @return CFHIRDataTypeString|null
     */
    public function mapPublisher(): ?CFHIRDataTypeString;

    /**
     * Map property contact
     *
     * @return CFHIRDataTypeContactDetail[]
     */
    public function mapContact(): array;

    /**
     * Map property description
     *
     * @return CFHIRDataTypeMarkdown|null
     */
    public function mapDescription(): ?CFHIRDataTypeMarkdown;

    /**
     * Map property useContext
     *
     * @return CFHIRDataTypeUsageContext[]
     */
    public function mapUseContext(): array;

    /**
     * Map property jurisdiction
     *
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function mapJurisdiction(): array;

    /**
     * Map property purpose
     *
     * @return CFHIRDataTypeMarkdown|null
     */
    public function mapPurpose(): ?CFHIRDataTypeMarkdown;

    /**
     * Map property copyright
     *
     * @return CFHIRDataTypeMarkdown|null
     */
    public function mapCopyright(): ?CFHIRDataTypeMarkdown;

    /**
     * Map property keyword
     *
     * @return CFHIRDataTypeCoding[]
     */
    public function mapKeyword(): array;

    /**
     * Map property fhirVersion
     *
     * @return CFHIRDataTypeCode|null
     */
    public function mapFhirVersion(): ?CFHIRDataTypeCode;

    /**
     * Map property kind
     *
     * @return CFHIRDataTypeCode|null
     */
    public function mapKind(): ?CFHIRDataTypeCode;

    /**
     * Map property abstract
     *
     * @return CFHIRDataTypeBoolean|null
     */
    public function mapAbstract(): ?CFHIRDataTypeBoolean;

    /**
     * Map property contextInvariant
     *
     * @return CFHIRDataTypeString[]
     */
    public function mapContextInvariant(): array;

    /**
     * Map property type
     *
     * @return CFHIRDataTypeUri|null
     */
    public function mapType(): ?CFHIRDataTypeUri;

    /**
     * Map property baseDefinition
     *
     * @return CFHIRDataTypeCanonical|null
     */
    public function mapBaseDefinition(): ?CFHIRDataTypeCanonical;

    /**
     * Map property derivation
     *
     * @return CFHIRDataTypeCode|null
     */
    public function mapDerivation(): ?CFHIRDataTypeCode;
}
