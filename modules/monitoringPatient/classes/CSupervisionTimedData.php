<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\MonitoringPatient;

use Ox\Core\CMbObject;

/**
 * A supervision timed data representation
 */
class CSupervisionTimedData extends CSupervisionTimedEntity {
  public $supervision_timed_data_id;

  public $period;
  public $value_type_id;
  public $in_doc_template;
  public $type;
  public $items;
  public $column;

  public $_items;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "supervision_timed_data";
    $spec->key   = "supervision_timed_data_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                    = parent::getProps();
    $props["owner_id"]       .= " back|supervision_timed_data";
    $props["period"]          = "enum list|1|5|10|15|20|30|60";
    $props["value_type_id"]   = "ref notNull class|CObservationValueType autocomplete|_view dependsOn|datatype back|supervision_timed_data";
    $props["in_doc_template"] = "bool notNull default|0";
    $props["type"]            = "enum list|str|bool|enum|set notNull default|str";
    $props["items"]           = "text";
    $props["column"]          = "num min|1 max|4 default|1";

    return $props;
  }

  /**
   * Get timed data, set it to empty array if not present
   *
   * @param array[][] $results Patient results
   *
   * @return array
   */
  function loadTimedData($results) {
    $type_id = $this->value_type_id;

    if (!isset($results[$type_id]["none"])) {
      return $this->_graph_data = array();
    }

    return $this->_graph_data = $results[$type_id]["none"];
  }

  /**
   * Make items
   *
   * @return string[]
   */
  function makeItems() {
    $items = trim($this->items);

    if ($items === "") {
      return $this->_items = null;
    }

    return $this->_items = preg_split('/[\r\n]+/', $this->items);
  }

  /**
   * Get all the timed data for an object
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
