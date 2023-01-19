<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante;

use Ox\Interop\Hl7\CHDataType;
use Ox\Interop\Hl7\CHL7v2DataType;
use Ox\Interop\Hl7\CHL7v2DataTypeComposite;

/**
 * Class CHPrimSanteDataType
 * data type of HprimSante
 */
class CHPrimSanteDataType extends CHDataType {
  /**
   * Get the spec object of a data type
   *
   * @param CHPrimSanteMessage $message   Message
   * @param string             $type      The 2 or 3 letters type
   * @param string             $version   The version number of the spec
   * @param string             $extension The extension
   *
   * @return CHL7v2DataType The data type spec
   */
  static function load($message, $type, $version, $extension) {
    static $cache = array();

    if ($type == "TS") {
      $type = "DTM";
    }

    $class_type = self::mapToBaseType($type);

    if (isset($cache[$version][$type])) {
      return $cache[$version][$type];
    }

    if (in_array($class_type, self::$typesBase)) {
      $class = "CHL7v2DataType$class_type";
      $instance = new $class($message, $class_type, $version, $extension);
      //$instance->getSpecs();
    }
    else {
      $instance = new CHL7v2DataTypeComposite($message, $type, $version, $extension);
    }

    return $cache[$version][$type] = $instance;
  }
}

