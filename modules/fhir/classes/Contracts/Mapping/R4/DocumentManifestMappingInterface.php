<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Mapping\R4;

use Ox\Interop\Fhir\Contracts\Mapping\ResourceDomainMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\DocumentManifest\CFHIRDataTypeDocumentManifestRelated;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * Description
 */
interface DocumentManifestMappingInterface extends ResourceDomainMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "DocumentManifest";

    /**
     * Map property masterIdentifier
     *
     * @return CFHIRDataTypeIdentifier|null
     */
    public function mapMasterIdentifier(): ?CFHIRDataTypeIdentifier;

    /**
     * @return CFHIRDataTypeIdentifier[]
     */
    public function mapIdentifier(): array;

    /**
     * Map property status
     *
     * @return CFHIRDataTypeCode|null
     */
    public function mapStatus(): ?CFHIRDataTypeCode;

    /**
     * Map property type
     *
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function mapType(): ?CFHIRDataTypeCodeableConcept;

    /**
     * Map property subject
     *
     * @return CFHIRDataTypeReference|null
     */
    public function mapSubject(): ?CFHIRDataTypeReference;

    /**
     * Map property created
     *
     * @return CFHIRDataTypeDateTime|null
     */
    public function mapCreated(): ?CFHIRDataTypeDateTime;

    /**
     * Map property author
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapAuthor(): array;

    /**
     * Map property recipient
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapRecipient(): array;

    /**
     * Map property source
     *
     * @return CFHIRDataTypeUri|null
     */
    public function mapSource(): ?CFHIRDataTypeUri;

    /**
     * Map property description
     *
     * @return CFHIRDataTypeString|null
     */
    public function mapDescription(): ?CFHIRDataTypeString;

    /**
     * Map property content
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapContent(): array;

    /**
     * Map property related
     *
     * @return CFHIRDataTypeDocumentManifestRelated[]
     */
    public function mapRelated(): array;
}
