<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Mapping\R4;

use Ox\Interop\Fhir\Contracts\Mapping\ResourceMappingInterface;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Parameters\CFHIRDataTypeParametersParameter;

/**
 * Description
 */
interface ParametersMappingInterface extends ResourceMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "Parameters";

    /**
     * @return CFHIRDataTypeParametersParameter[]
     */
    public function mapParameter(): array;
}
