<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes;

/**
 * FHIR data type
 */
class CFHIRDataTypeDecimal extends CFHIRDataType
{
    /** @var string */
    public const NAME = 'Decimal';

    /**
     * @return float
     */
    public function getValue()
    {
        return (float)$this->_value;
    }
}
