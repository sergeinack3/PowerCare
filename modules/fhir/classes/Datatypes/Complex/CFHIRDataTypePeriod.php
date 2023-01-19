<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;

/**
 * FHIR data type
 */
class CFHIRDataTypePeriod extends CFHIRDataTypeComplex
{
    /** @var string */
    public const NAME = 'Period';

    /** @var CFHIRDataTypeDateTime */
    public $start;

    /** @var CFHIRDataTypeDateTime */
    public $end;

    /**
     * @param string      $start
     * @param string|null $end
     *
     * @return static
     */
    public static function from(string $start, ?string $end = null): self
    {
        $data = [
            "start" => new CFHIRDataTypeDateTime($start),
        ];

        if ($end) {
            $data['end'] = new CFHIRDataTypeDateTime($end);
        }

        return self::build($data);
    }
}
