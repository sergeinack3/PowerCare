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
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\ObservationResult\CObservationValueType;
use Ox\Mediboard\ObservationResult\CObservationValueUnit;

/**
 * Represents a row in a supervision table
 */
class CSupervisionTableRow extends CMbObject {
  /** @var integer Primary key */
  public $supervision_table_row_id;

  /** @var int The supervision table */
  public $supervision_table_id;

  /** @var string The title of the row */
  public $title;

  /** @var bool Indicate if the row is displayed or not */
  public $active;

  /** @var string An optional color used for the displayed data */
  public $color;

  /** @var int The observation value type */
  public $value_type_id;

  /** @var int The observation value unit */
  public $value_unit_id;

  /** @var int The sampling frequency used when importing the data into the constants */
  public $import_sampling_frequency;

  /** @var CSupervisionTable The table */
  public $_ref_table;

  /** @var CObservationValueType The value type */
  public $_ref_value_type;

  /** @var CObservationValueUnit The value unit */
  public $_ref_value_unit;

  /** @var array The observation result value */
  public $_data = array();

  /**
   * Initialize the class specifications
   *
   * @return CMbObjectSpec
   */
  public function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "supervision_table_rows";
    $spec->key   = "supervision_table_row_id";

    return $spec;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  public function getProps() {
    $props = parent::getProps();

    $props['supervision_table_id']      = 'ref notNull class|CSupervisionTable cascade back|rows';
    $props['title']                     = 'str notNull';
    $props['active']                    = 'bool default|1';
    $props['color']                     = 'color';
    $props['value_type_id']             = 'ref notNull class|CObservationValueType autocomplete|_view dependsOn|datatype back|supervision_table_rows';
    $props['value_unit_id']             = 'ref notNull class|CObservationValueUnit autocomplete|_view back|supervision_table_rows';
    $props['import_sampling_frequency'] = 'enum list|1|2|3|5|10|15|20|30 default|5';

    return $props;
  }

  /**
   * @inheritdoc
   */
  public function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->title;

    if ($this->value_unit_id) {
      $this->loadRefValueUnit();

      if ($this->_ref_value_unit->display_text) {
        $this->_view .= " ({$this->_ref_value_unit->display_text})";
      }
      else {
        $this->_view .= " ({$this->_ref_value_unit->label})";
      }
    }
  }

  /**
   * Load the table
   *
   * @param bool $cache Use object cache
   *
   * @return CSupervisionTable
   * @throws Exception
   */
  public function loadRefTable($cache = true) {
    return $this->_ref_table = $this->loadFwdRef('supervision_table_id', $cache);
  }

  /**
   * Load the value type
   *
   * @param bool $cache Use object cache
   *
   * @return CObservationValueType
   * @throws Exception
   */
  public function loadRefValueType($cache = true) {
    return $this->_ref_value_type = $this->loadFwdRef('value_type_id', $cache);
  }

  /**
   * Load the value unit
   *
   * @param bool $cache Use object cache
   *
   * @return CObservationValueUnit
   * @throws Exception
   */
  public function loadRefValueUnit($cache = true) {
    return $this->_ref_value_unit = $this->loadFwdRef('value_unit_id', $cache);
  }

  /**
   * Get the data from the sampled data from the given observation results
   *
   * @param array  $results The observation results, by value type and unit
   * @param string $start   Start datetime
   * @param string $end     End datetime
   *
   * @return void
   * @throws Exception
   */
  public function build($results, $start, $end) {
    $this->loadRefValueType();
    $this->loadRefValueUnit();
    $this->loadRefTable();

    if (array_key_exists($this->value_type_id, $results) && array_key_exists($this->value_unit_id, $results[$this->value_type_id])) {
      $start                 = reset($results[$this->value_type_id][$this->value_unit_id]);
      $start_sampling_period = $start['ts'];
      $end_sampling_period   = $start_sampling_period + $this->_ref_table->sampling_frequency * 60000;

      foreach ($results[$this->value_type_id][$this->value_unit_id] as $result) {
        if ($result['ts'] > $end) {
          break;
        }
        elseif ($result['value'] !== null && ($result['ts'] >= $end_sampling_period
            || ($result['ts'] >= $start_sampling_period && $result['ts'] <= $end_sampling_period))
        ) {
          $this->_data[$result['ts']] = $result;
          $start_sampling_period      = $result['ts'] + $this->_ref_table->sampling_frequency * 60000;
          $end_sampling_period        += $this->_ref_table->sampling_frequency * 60000;
        }
      }
    }
  }
}
