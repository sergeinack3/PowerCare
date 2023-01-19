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
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CapabilityStatement\CFHIRDataTypeCapabilityStatementRest;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CapabilityStatement\CFHIRDataTypeCapabilityStatementSoftware;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactDetail;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeUsageContext;

/**
 * Description
 */
interface CapabilityStatementMappingInterface extends ResourceDomainMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "CapabilityStatement";

    /**
     * Map property url
     *
     * @return CFHIRDataTypeUri
     */
    public function mapUrl(): CFHIRDataTypeUri;

    /**
     * Map property version
     *
     * @return CFHIRDataTypeString
     */
    public function mapVersion(): CFHIRDataTypeString;

    /**
     * Map property name
     *
     * @return CFHIRDataTypeString
     */
    public function mapName(): CFHIRDataTypeString;

    /**
     * Map property title
     *
     * @return CFHIRDataTypeString
     */
    public function mapTitle(): CFHIRDataTypeString;

    /**
     * Map property status
     *
     * @return CFHIRDataTypeCode
     */
    public function mapStatus(): ?CFHIRDataTypeCode;

    /**
     * Map property experimental
     *
     * @return CFHIRDataTypeBoolean
     */
    public function mapExperimental(): CFHIRDataTypeBoolean;

    /**
     * Map property date
     *
     * @return CFHIRDataTypeDateTime
     */
    public function mapDate(): ?CFHIRDataTypeDateTime;

    /**
     * Map property publisher
     *
     * @return CFHIRDataTypeString
     */
    public function mapPublisher(): CFHIRDataTypeString;

    /**
     * Map property contact
     *
     * @return CFHIRDataTypeContactDetail[]
     */
    public function mapContact(): array;

    /**
     * Map property description
     *
     * @return CFHIRDataTypeMarkdown
     */
    public function mapDescription(): CFHIRDataTypeMarkdown;

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
     * @return CFHIRDataTypeMarkdown
     */
    public function mapPurpose(): CFHIRDataTypeMarkdown;

    /**
     * Map property copyright
     *
     * @return CFHIRDataTypeMarkdown
     */
    public function mapCopyright(): CFHIRDataTypeMarkdown;

    /**
     * Map property kind
     *
     * @return CFHIRDataTypeCode
     */
    public function mapKind(): ?CFHIRDataTypeCode;

    /**
     * Map property instantiates
     *
     * @return CFHIRDataTypeCanonical[]
     */
    public function mapInstantiates(): array;

    /**
     * Map property imports
     *
     * @return CFHIRDataTypeCanonical[]
     */
    public function mapImports(): array;

    /**
     * Map property software
     *
     * @return CFHIRDataTypeCapabilityStatementSoftware
     */
    public function mapSoftware(): CFHIRDataTypeCapabilityStatementSoftware;

    /**
     * Map property fhirVersion
     *
     * @return CFHIRDataTypeCode
     */
    public function mapFhirVersion(): ?CFHIRDataTypeCode;

    /**
     * Map property format
     *
     * @return CFHIRDataTypeCode[]
     */
    public function mapFormat(): ?array;

    /**
     * Map property patchFormat
     *
     * @return CFHIRDataTypeCode[]
     */
    public function mapPatchFormat(): array;

    /**
     * Map property implementationGuide
     *
     * @return CFHIRDataTypeCanonical[]
     */
    public function mapImplementationGuide(): array;

    /**
     * Map property rest
     *
     * @return CFHIRDataTypeCapabilityStatementRest[]
     */
    public function mapRest(): array;
}
