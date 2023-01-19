<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes;

use Exception;
use Ox\Core\CMbDT;
use Ox\Interop\Fhir\Profiles\CFHIR;

/**
 * FHIR data type
 */
class CFHIRDataTypeDateTime extends CFHIRDataType
{
    /** @var string */
    public const NAME = 'DateTime';

    /**
     * @return string
     * @throws Exception
     */
    public function getValue()
    {
        return CFHIR::getTimeUtc($this->_value, false);
    }

    /**
     * Get datetime in format asked
     *
     * @param string $format
     *
     * @return string|null
     */
    public function getDatetime(string $format = CMbDT::ISO_DATETIME): ?string
    {
        return $this->_value ? CMbDT::format($this->_value, $format) : null;
    }
}
