<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use Ox\Core\CValue;

/**
 * HL7 Date data type
 */
class CHL7v2DataTypeDate extends CHL7v2DataType
{
    /**
     * @inheritdoc
     */
    public function toMB($value, CHL7v2Field $field)
    {
        $parsed = $this->parseHL7($value, $field);

        // empty value
        if ($parsed === "") {
            return "";
        }

        // invalid value
        if ($parsed === false) {
            return null;
        }

        return CValue::read($parsed, "year") . "-" .
            CValue::read($parsed, "month", "00") . "-" .
            CValue::read($parsed, "day", "00");
    }

    /**
     * @inheritdoc
     */
    public function toHL7($value, CHL7v2Field $field)
    {
        $parsed = $this->parseMB($value, $field);

        // empty value
        if (empty($parsed)) {
            return "";
        }

        // invalid value
        if ($parsed === false) {
            return null;
        }

        return $parsed["year"] . ($parsed["month"] === "00" ? "" : $parsed["month"]) .
            ($parsed["day"] === "00" ? "" : $parsed["day"]);
    }
}
