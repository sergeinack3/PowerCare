<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypePositiveInt;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;

/**
 * FHIR data type
 */
class CFHIRDataTypeUsageContext extends CFHIRDataTypeComplex
{
    /** @var string */
    public const NAME = 'UsageContext';

    /** @var CFHIRDataTypeString */
    public $code;

    /**
     * @var CFHIRDataTypeChoice
     * CodeableConcept
     * Quantity
     * Range
     * Reference
     */
    public $value;
}
