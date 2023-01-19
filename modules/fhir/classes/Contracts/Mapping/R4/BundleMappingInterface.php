<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Mapping\R4;

use Ox\Interop\Fhir\Contracts\Mapping\ResourceMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUnsignedInt;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Bundle\CFHIRDataTypeBundleEntry;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Bundle\CFHIRDataTypeBundleLink;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeSignature;

/**
 * Description
 */
interface BundleMappingInterface extends ResourceMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "Bundle";

    /**
     * Map property type
     *
     * @return CFHIRDataTypeCode
     */
    public function mapType(): ?CFHIRDataTypeCode;

    /**
     * Map porperty Identifier
     *
     * @return CFHIRDataTypeIdentifier|null
     */
    public function mapIdentifier(): ?CFHIRDataTypeIdentifier;

    /**
     * Map property timestamp
     *
     * @return CFHIRDataTypeInstant|null
     */
    public function mapTimestamp(): ?CFHIRDataTypeInstant;

    /**
     * Map property total
     *
     * @return CFHIRDataTypeUnsignedInt|null
     */
    public function mapTotal(): ?CFHIRDataTypeUnsignedInt;

    /**
     * Map property link
     *
     * @return CFHIRDataTypeBundleLink[]
     */
    public function mapLink(): array;

    /**
     * Map property entry
     *
     * @return CFHIRDataTypeBundleEntry[]
     */
    public function mapEntry(): array;

    /**
     * Map property signature
     *
     * @return CFHIRDataTypeSignature|null
     */
    public function mapSignature(): ?CFHIRDataTypeSignature;
}
