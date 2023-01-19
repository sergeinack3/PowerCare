<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Ox\Core\FieldSpecs\CRefSpec;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Description
 */
class CModelObjectFieldDescription {
  /**
   * Patient Import
   *
   * @param CPatient|CSejour $object
   *
   * @return array $object_specs
   */
  static function getSpecList($object) {
    $object_specs["main"] = array_values($object->_specs);
    self::cleanSpecs($object_specs["main"]);

    return $object_specs;
  }

  /**
   * Cleanup the array
   *
   * @param array $array_spec array to clean
   *
   * @return void
   */
  static function cleanSpecs(&$array_spec) {
    foreach ($array_spec as $key => $_spec) {
      if ($_spec instanceof CRefSpec) {
        unset($array_spec[$key]);
      }

      if (substr($_spec->fieldName, 0, 1) == "_") {
        unset($array_spec[$key]);
      }
    }
  }

  /**
   * Remove specs from the list
   *
   * @param array          $specs_to_remove array of fieldname to remove
   * @param CMbFieldSpec[] $specs_array     array of specs
   *
   * @return mixed
   */
  static function removeSpecs($specs_to_remove, &$specs_array) {
    if (!count($specs_to_remove)) {
      return $specs_array;
    }
    foreach ($specs_array["main"] as $key => $_spec) {
      if (in_array($_spec->fieldName, $specs_to_remove)) {
        unset($specs_array["main"][$key]);
      }
    }

    return $specs_array;
  }

  /**
   * Add a spec before another one
   *
   * @param CMbFieldSpec   $spec        spec to add
   * @param CMbFieldSpec[] $specs_array spec array
   * @param string         $key         key of the spec
   * @param bool           $notNull     notNull ?
   *
   * @return void
   */
  static function addBefore(&$spec, &$specs_array, $key = "main", $notNull = false) {
    if (!isset($specs_array[$key])) {
      $specs_array = [$key => []] + $specs_array;
    }
    $spec->notNull = $notNull ? 1 : 0;
    array_unshift($specs_array[$key], $spec);
  }

  /**
   * Add a spec after another one
   *
   * @param CMbFieldSpec   $spec        spec to add
   * @param CMbFieldSpec[] $specs_array spec array
   * @param string         $key         key
   * @param bool           $notNull     is the spec not null
   *
   * @return void
   */
  static function addAfter(&$spec, &$specs_array, $key = "main", $notNull = false) {
    $spec->notNull       = ($notNull != $spec->notNull) ? $notNull : $spec->notNull;
    $specs_array[$key][] = $spec;
  }

  /**
   * Get the array of spec
   *
   * @param array $array the array to return
   *
   * @return array
   */
  static function getArray($array) {
    $return = [];
    foreach ($array as $key => $value) {
      foreach ($value as $_value) {
        $return[] = $_value;
      }
    }

    return $return;
  }
}
