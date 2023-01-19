<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\MonitoringPatient;

use Ox\Core\CMbObject;
use Ox\Mediboard\Files\CFile;

/**
 * A supervision timed data representation
 */
class CSupervisionTimedPicture extends CSupervisionTimedEntity {
  const PICTURES_ROOT = "modules/monitoringPatient/images/supervision";

  public $supervision_timed_picture_id;
  public $in_doc_template;

  public $value_type_id;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "supervision_timed_picture";
    $spec->key   = "supervision_timed_picture_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                    = parent::getProps();
    $props["owner_id"]       .= " back|supervision_timed_picture";
    $props["value_type_id"]   = "ref notNull class|CObservationValueType autocomplete|_view dependsOn|datatype back|supervision_timed_pictures";
    $props["in_doc_template"] = "bool notNull default|0";

    return $props;
  }

  /**
   * Load pictures from results
   *
   * @param array[][] $results Patient results
   *
   * @return array
   */
  function loadTimedPictures($results) {
    $type_id = $this->value_type_id;
    if (!isset($results[$type_id]["none"])) {
      return $this->_graph_data = array();
    }

    $data = $results[$type_id]["none"];

    foreach ($data as $_i => $_d) {
      if ($_d["file_id"]) {
        $file = new CFile();
        $file->load($_d["file_id"]);
        $data[$_i]["file"] = $file;
      }
    }

    return $this->_graph_data = $data;
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
