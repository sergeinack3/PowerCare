<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\MonitoringPatient;

use Exception;
use Ox\Core\CMbObject;
use Ox\Mediboard\ObservationResult\CObservationValueUnit;

/**
 * A supervision instant data representation
 */
class CSupervisionInstantData extends CSupervisionTimedEntity {
  public $supervision_instant_data_id;

  public $value_type_id;
  public $value_unit_id;
  public $size;
  public $color;

  public $_ref_value_type;
  public $_ref_value_unit;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "supervision_instant_data";
    $spec->key   = "supervision_instant_data_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                  = parent::getProps();
    $props["owner_id"]     .= " back|supervision_instant_data";
    $props["value_type_id"] = "ref notNull class|CObservationValueType autocomplete|_view dependsOn|coding_system back|supervision_instant_data";
    $props["value_unit_id"] = "ref notNull class|CObservationValueUnit autocomplete|_view dependsOn|coding_system back|supervision_instant_data";
    $props["size"]          = "num notNull min|10 max|60";
    $props["color"]         = "color";

    return $props;
  }

  /**
   * Load value type
   *
   * @param bool $cache Use object cache
   *
   * @return CObservationValueType
   * @throws Exception
   */
  function loadRefValueType($cache = true) {
    return $this->_ref_value_type = $this->loadFwdRef("value_type_id", $cache);
  }

  /**
   * Load value unit
   *
   * @return CObservationValueUnit
   */
  function loadRefValueUnit() {
    return $this->_ref_value_unit = CObservationValueUnit::get($this->value_unit_id);
  }

  /**
   * Get all the instant data for an object
   *
   * @param CMbObject $object The object to get timed data of
   *
   * @return self[]
   */
  static function getAllFor(CMbObject $object) {
    $graph = new self;

    $where = array(
      "owner_class" => "= '$object->_class'",
      "owner_id"    => "= '$object->_id'",
    );

    return $graph->loadList($where, "title");
  }
}
