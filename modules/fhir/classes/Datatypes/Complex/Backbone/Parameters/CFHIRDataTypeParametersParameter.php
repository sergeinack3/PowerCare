<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex\Backbone\Parameters;

use Ox\Interop\Fhir\Datatypes\CFHIRDataType;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeChoice;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeResource;

/**
 * FHIR data type
 */
class CFHIRDataTypeParametersParameter extends CFHIRDataTypeBackboneElement
{
    /** @var string */
    public const NAME = 'Parameters.parameter';

    /** @var CFHIRDataTypeString */
    public $name;

    /** @var CFHIRDataTypeChoice */
    public $value;

    /** @var CFHIRDataTypeResource */
    public $resource;

    /** @var CFHIRDataTypeReference[] */
    public $part;

    /**
     * @param CFHIRDataTypeString $name
     *
     * @return CFHIRDataTypeParametersParameter
     */
    public function setName(string $name): CFHIRDataTypeParametersParameter
    {
        $this->name = new CFHIRDataTypeString($name);

        return $this;
    }

    /**
     * @param CFHIRDataTypeString $name
     *
     * @return CFHIRDataTypeParametersParameter
     */
    public function setNameElement(?CFHIRDataTypeString $name): CFHIRDataTypeParametersParameter
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param CFHIRDataTypeChoice|CFHIRDataType $value
     *
     * @return CFHIRDataTypeParametersParameter
     */
    public function setValueElement(?CFHIRDataType $value): CFHIRDataTypeParametersParameter
    {
        $this->value = $value;

        return $this;
    }
}
