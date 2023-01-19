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
class CFHIRDataTypeTime extends CFHIRDataType
{
    /** @var string */
    public const NAME = 'Time';

    /**
     * @return string
     */
    public function getValue()
    {
        return CMbDT::format($this->_value, CMbDT::ISO_TIME);
    }
}
