<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes;

use Ox\Core\CMbDT;
use Ox\Interop\Fhir\Profiles\CFHIR;

/**
 * FHIR data type
 */
class CFHIRDataTypeInstant extends CFHIRDataType
{
    /** @var string */
    public const NAME = 'Instant';

    /**
     * @return string
     */
    public function getValue()
    {
        return CFHIR::getTimeUtc($this->_value, false);
    }

    /**
     * Get date in format asked
     *
     * @param string $format
     *
     * @return string|null
     */
    public function getDatetime(string $format = CMbDT::ISO_DATETIME): ?string
    {
        return $this->_value ? CMbDT::format($this->_value, $format) : null;
    }

    /**
     * Format period fhir type
     *
     * @param string $start start
     * @param string $end   end
     *
     * @return array
     */
    public static function formatPeriod(string $start, string $end = null): array
    {
        $data = [
            "start" => new CFHIRDataTypeInstant($start),
        ];

        if ($end) {
            $data['end'] = new CFHIRDataTypeInstant($end);
        }

        return $data;
    }
}
