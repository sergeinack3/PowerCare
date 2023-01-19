<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Mapping\R4;

use Ox\Interop\Fhir\Contracts\Mapping\ResourceMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBase64Binary;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * Description
 */
interface BinaryMappingInterface extends ResourceMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "Binary";

    /**
     * @return CFHIRDataTypeIdentifier[]
     */
    public function mapIdentifier(): array;

    /**
     * Map property contentType
     *
     * @return CFHIRDataTypeCode|null
     */
    public function mapContentType(): ?CFHIRDataTypeCode;

    /**
     * Map property data
     *
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function mapData(): ?CFHIRDataTypeBase64Binary;

    /**
     * Map property securityContext
     *
     * @return CFHIRDataTypeReference|null
     */
    public function mapSecurityContext(): ?CFHIRDataTypeReference;
}
