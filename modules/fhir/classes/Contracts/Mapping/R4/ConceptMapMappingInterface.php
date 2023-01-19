<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Mapping\R4;

use Ox\Interop\Fhir\Contracts\Mapping\ResourceDomainMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeMarkdown;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\ConceptMap\CFHIRDataTypeConceptMapGroup;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeChoice;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactDetail;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeUsageContext;

/**
 * Description
 */
interface ConceptMapMappingInterface extends ResourceDomainMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "ConceptMap";

    /**
     * @return CFHIRDataTypeIdentifier[]
     */
    public function mapIdentifier(): array;

    /**
     * Map property url
     *
     * @return CFHIRDataTypeUri|null
     */
    public function mapUrl(): ?CFHIRDataTypeUri;

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
     * Map property Publisher
     *
     * @return CFHIRDataTypeString|null
     */
    public function mapPublisher(): ?CFHIRDataTypeString;

    /**
     * Map property Contact
     *
     * @return CFHIRDataTypeContactDetail[]
     */
    public function mapContact(): array;

    /**
     * Map property Description
     *
     * @return CFHIRDataTypeMarkdown|null
     */
    public function mapDescription(): ?CFHIRDataTypeMarkdown;

    /**
     * Map property UseContext
     *
     * @return CFHIRDataTypeUsageContext[]
     */
    public function mapUseContext(): array;

    /**
     * Map property Jurisdiction
     *
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function mapJurisdiction(): array;

    /**
     * Map property Purpose
     *
     * @return CFHIRDataTypeMarkdown|null
     */
    public function mapPurpose(): ?CFHIRDataTypeMarkdown;

    /**
     * Map property CopyRight
     *
     * @return CFHIRDataTypeMarkdown|null
     */
    public function mapCopyright(): ?CFHIRDataTypeMarkdown;

    /**
     * Map property Source
     *
     * @return CFHIRDataTypeChoice|null
     */
    public function mapSource(): ?CFHIRDataTypeChoice;

    /**
     * Map property Target
     *
     * @return CFHIRDataTypeChoice|null
     */
    public function mapTarget(): ?CFHIRDataTypeChoice;

    /**
     * Map property Group
     *
     * @return CFHIRDataTypeConceptMapGroup[]
     */
    public function mapGroup(): array;
}
