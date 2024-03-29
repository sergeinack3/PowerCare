<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

/**
 * Integer HL7 datatype
 */
class CHL7v2DataTypeInteger extends CHL7v2DataType
{
    /**
     * @inheritdoc
     */
    function toMB($value, CHL7v2Field $field)
    {
        $parsed = parent::toMB($value, $field);

        // empty value
        if ($parsed === "") {
            return "";
        }

        // invalid value
        if ($parsed === false) {
            return null;
        }

        return (int)$parsed;
    }

    /**
     * @inheritdoc
     */
    function toHL7($value, CHL7v2Field $field)
    {
        $parsed = parent::toHL7($value, $field);

        // empty value
        if ($parsed === "" || $parsed === null) {
            return "";
        }

        // invalid value
        if ($parsed === false) {
            return null;
        }

        return (int)$parsed;
    }
}
