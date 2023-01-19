<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\MonitoringPatient;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Mediboard\ObservationResult\CObservationValueType;
use Ox\Mediboard\ObservationResult\CObservationValueUnit;

/**
 * A supervision graph Y axis
 */
class CSupervisionGraphSeries extends CMbObject {
  public $supervision_graph_series_id;

  public $supervision_graph_axis_id;
  public $title;
  public $value_type_id;
  public $value_unit_id;
  public $color;
  public $integer_values;
  public $display_ratio_time;
  public $display_ratio_value;
  public $import_sampling_frequency;

  /** @var CObservationValueType */
  public $_ref_value_type;

  /** @var CObservationValueUnit */
  public $_ref_value_unit;

  /** @var CSupervisionGraphAxis */
  public $_ref_axis;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "supervision_graph_series";
    $spec->key   = "supervision_graph_series_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                              = parent::getProps();
    $props["supervision_graph_axis_id"] = "ref notNull class|CSupervisionGraphAxis cascade back|series";
    $props["title"]                     = "str";
    $props["value_type_id"]             = "ref notNull class|CObservationValueType autocomplete|_view dependsOn|datatype back|supervision_graph_series";
    $props["value_unit_id"]             = "ref class|CObservationValueUnit autocomplete|_view back|supervision_graph_series";
    $props["color"]                     = "color notNull";
    $props["integer_values"]            = "bool notNull default|0";
    $props["display_ratio_time"]        = "float";
    $props["display_ratio_value"]       = "float";
    $props['import_sampling_frequency'] = 'enum list|1|2|3|5|10|15|20|30 default|5';

    return $props;
  }

    /**
     * Initializes series data structure
     *
     * @param int $yaxes_count Number of y-axes
     *
     * @return array
     */
    public function initSeriesData($yaxes_count): array
    {
        $axis = $this->loadRefAxis();
        $unit = $this->loadRefValueUnit()->label;

        if (strpos($unit, "MDC_") !== false) {
            $unit = CAppUI::tr("CMonitoringConcentrator-unit-$unit");
        }

        if ($this->_ref_value_unit->display_text) {
            $unit = $this->_ref_value_unit->display_text;
        }

        $series_data = [
            "data"       => [[0, null]],
            "yaxis"      => $yaxes_count,
            "label"      => $this->_view . ($unit ? " ($unit)" : ""),
            "unit"       => $unit,
            "color"      => "#$this->color",
            "shadowSize" => 0,
        ];

        $series_data["points"]       = ["show" => false];
        $series_data[$axis->display] = ["show" => true];

        if ($axis->display === "stack") {
            $series_data["bars"]  = [
                "show"      => true,
                "barWidth"  => 60 * 1000 * 30, // FIXME
                "lineWidth" => 0.5,
            ];
            $series_data["stack"] = true; // It replaces the "stack" array with a boolean !!
        }

        if ($axis->display === "bandwidth") {
            $series_data["bandwidth"]["lineWidth"] = 10;
        }

        if ($axis->show_points || $axis->display === "points") {
            $series_data["points"] = [
                "show"      => true,
                "symbol"    => $axis->symbol,
                "lineWidth" => 1,
            ];
        }

        return $series_data;
    }

    /**
     * Load axis
     *
     * @param bool $cache Use object cache
   *
   * @return CSupervisionGraphAxis
   */
  function loadRefAxis($cache = true) {
    return $this->_ref_axis = $this->loadFwdRef("supervision_graph_axis_id", $cache);
  }

  /**
   * Load value type
   *
   * @param bool $cache Use object cache
   *
   * @return CObservationValueType
   */
  function loadRefValueType($cache = true) {
    return $this->_ref_value_type = $this->loadFwdRef("value_type_id", $cache);
  }

  /**
   * Load value unit
   *
   * @param bool $cache Use object cache
   *
   * @return CObservationValueUnit
   */
  function loadRefValueUnit($cache = true) {
    return $this->_ref_value_unit = $this->loadFwdRef("value_unit_id", $cache);
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $title = $this->title;

    if (!$title) {
      $title = $this->loadRefValueType()->label;
    }

    $this->_view = $title;
  }

  /**
   * Builds a set of sample value data
   *
   * @param array $times A list of time values to get sample data for
   *
   * @return array
   */
  function getSampleData($times) {
    $axis = $this->loadRefAxis();

    $low  = $axis->limit_low != null ? $axis->limit_low : 0;
    $high = $axis->limit_high != null ? $axis->limit_high : 100;

    if ($axis->display === "stack") {
      $low  /= 2;
      $high /= 2;
    }

    $diff  = $high - $low;
    $value = mt_rand($low + $diff / 4, $high - $diff / 4);

    $data = array();
    foreach ($times as $_time) {
      $v      = round($value, $this->integer_values ? 0 : 2);
      $data[] = array($_time, $v);
      $value  += mt_rand(-$diff, +$diff) / 10;
    }

    return $data;
  }
}
