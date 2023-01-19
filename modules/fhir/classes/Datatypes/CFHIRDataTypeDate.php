<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes;

use Ox\Core\CMbDT;

/**
 * FHIR data type
 */
class CFHIRDataTypeDate extends CFHIRDataType
{
    /** @var string */
    public const NAME = 'Date';

    /**
     * @return string
     */
    public function getValue()
    {
        return CMbDT::date($this->_value);
    }

    /**
     * Get date in format asked
     *
     * @param string $format
     *
     * @return string|null
     */
    public function getDate(string $format = CMbDT::ISO_DATE): ?string
    {
        return $this->_value ? CMbDT::format($this->_value, $format) : null;
    }
}
