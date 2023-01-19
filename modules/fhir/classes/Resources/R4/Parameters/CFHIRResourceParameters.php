<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Parameters;

use Ox\Interop\Fhir\Contracts\Mapping\R4\ParametersMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourceParametersInterface;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Parameters\CFHIRDataTypeParametersParameter;
use Ox\Interop\Fhir\Resources\CFHIRResource;

/**
 * FIHR patient resource
 */
class CFHIRResourceParameters extends CFHIRResource implements ResourceParametersInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "Parameters";

    /** @var CFHIRDataTypeParametersParameter[] */
    protected array $parameter = [];

    /** @var ParametersMappingInterface */
    protected $object_mapping;

    /**
     * @param CFHIRDataTypeParametersParameter ...$parameter
     *
     * @return CFHIRResourceParameters
     */
    public function setParameter(CFHIRDataTypeParametersParameter ...$parameter): CFHIRResourceParameters
    {
        $this->parameter = $parameter;

        return $this;
    }

    /**
     * @param CFHIRDataTypeParametersParameter ...$parameter
     *
     * @return CFHIRResourceParameters
     */
    public function addParameter(CFHIRDataTypeParametersParameter ...$parameter): CFHIRResourceParameters
    {
        $this->parameter = array_merge($this->parameter, $parameter);

        return $this;
    }

    /**
     * @return CFHIRDataTypeParametersParameter[]
     */
    public function getParameter(): array
    {
        return $this->parameter;
    }

    /**
     * @return void
     */
    public function mapParameter(): void
    {
        $this->parameter = $this->object_mapping->mapParameter();
    }
}
