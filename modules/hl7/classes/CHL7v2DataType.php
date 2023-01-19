<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

/**
 * HL7 data type
 */
class CHL7v2DataType extends CHDataType
{
    /**
     * Get the spec object of a data type
     *
     * @param CHMessage $message   H Message
     * @param string    $type      The 2 or 3 letters type
     * @param string    $version   The version number of the spec
     * @param string    $extension The extension
     *
     * @return CHL7v2DataType The data type spec
     */
    static function load($message, $type, $version, $extension)
    {
        static $cache = [];

        if ($type === "TS") {
            $type = "DTM";
        }

        $class_type = self::mapToBaseType($type);

        if (isset($cache[$version][$type])) {
            return $cache[$version][$type];
        }

        if (in_array($class_type, self::$typesBase)) {
            $class    = "CHL7v2DataType$class_type";
            $instance = new $class($message, $class_type, $version, $extension);
            //$instance->getSpecs();
        } else {
            $instance = new CHL7v2DataTypeComposite($message, $type, $version, $extension);
        }

        return $cache[$version][$type] = $instance;
    }
}

