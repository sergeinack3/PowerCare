<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex;

use Ox\Interop\Fhir\Datatypes\CFHIRDataType;
use Ox\Interop\Fhir\Exception\CFHIRException;

/**
 * FHIR data type
 */
class CFHIRDataTypeChoice extends CFHIRDataTypeComplex
{
    /** @var string */
    public const NAME = 'Choice';

    /** @var CFHIRDataType */
    private $type;

    /** @var mixed */
    private $value;

    /**
     * CFHIRDataTypeChoice constructor.
     *
     * @param string $type
     * @param mixed  $value
     */
    public function __construct(string $type = null, $value = null)
    {
        parent::__construct(null);

        if ($type && !is_subclass_of($type, CFHIRDataType::class)) {
            throw new CFHIRException('Type should be an instanceof CFHIRDataType');
        }

        $this->type  = $type;
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        /** @var CFHIRDataType $datatype */
        $datatype = $this->type;

        if (is_subclass_of($datatype, CFHIRDataTypeComplex::class)) {
            return $datatype::build($this->value);
        } else {
            return new $datatype($this->value);
        }
    }

    /**
     * @return bool
     */
    public function isNull(): bool
    {
        if (($datatype = $this->getValue()) === null) {
            return false;
        }

        return $datatype->isNull();
    }
}
