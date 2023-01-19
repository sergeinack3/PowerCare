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
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\ValueSet\CFHIRDataTypeValueSetCompose;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\ValueSet\CFHIRDataTypeValueSetExpansion;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactDetail;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeUsageContext;

/**
 * Description
 */
interface ValueSetMappingInterface extends ResourceDomainMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "ValueSet";

    /**
     * Map property url
     *
     * @return CFHIRDataTypeUri|null
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
     * @return CFHIRDataTypeCode
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
     * Map property immutable
     *
     * @return CFHIRDataTypeBoolean|null
     */
    public function mapImmutable(): ?CFHIRDataTypeBoolean;

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
     * Map property compose
     *
     * @return CFHIRDataTypeValueSetCompose|null
     */
    public function mapCompose(): ?CFHIRDataTypeValueSetCompose;

    /**
     * Map property expansion
     *
     * @return CFHIRDataTypeValueSetExpansion|null
     */
    public function mapExpansion(): ?CFHIRDataTypeValueSetExpansion;
}
