<?php
/**
 * @package Mediboard\api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Api;

use Ox\Core\Autoload\IShortNameAutoloadable;

/**
 * Description
 */
class CApexchartsConf implements IShortNameAutoloadable {
  /** Constants */
  const GOAL_TYPE_VERTICALLY = "sinceDate";
  const GOAL_TYPE_HORIZONTALLY = "achieved";

  const DEFAULT_COLOR = "";
  const DEFAULT_GOAL_COLOR = "#61a23d";

  public $series_labels = array();
  public $specCode = [];
  public $goals = [];
  public $aggregate = [];
  public $chartOptions = [];

  /**
   * Add label for specific serie
   *
   * @param string $label label for serie
   * @param int    $index index series
   *
   * @return void
   */
  public function addSeriesLabel($label, $index = -1) {
    if ($index >= 0) {
      $this->series_labels[$index] = $label;
    }
    $this->series_labels[] = $label;
  }

  /**
   * Add labels for series
   *
   * @param array $labels labels for series
   *
   * @return void
   */
  public function setSeriesLabels(array $labels) {
    $this->series_labels = $labels;
  }

  /**
   * Set spec code for graph with medical constants
   *
   * @param string $specCode spec code
   *
   * @return void
   */
  public function setSpecCode($specCode) {
    $this->specCode[] = $specCode;
  }

  /**
   * Add options for goal
   *
   * @param int    $value goal_value (time or value)
   * @param string $type  type of goal (vertical or horizontal)
   * @param string $color color for goal achieved
   *
   * @return void
   */
  public function addGoal($value, $type = self::GOAL_TYPE_HORIZONTALLY, $color = self::DEFAULT_GOAL_COLOR) {
    if ($type == self::GOAL_TYPE_VERTICALLY) {
      $this->aggregate[] = true;
    }

    $this->goals [] = array(
      "typeGoal" => $type,
      "colorGoal" => $color,
      "valueGoal" => $value
    );
  }

  /**
   * Add configuration options
   *
   * @param array $options configuration options
   *
   * @return void
   */
  public function setChartOptions(array $options) {
    $this->chartOptions = $options;
  }

  /**
   * Add specific option
   *
   * @param string $name  key option
   * @param mixed  $value value option
   *
   * @return void
   */
  public function addChartOption($name, $value) {
    $this->chartOptions[$name] = $value;
  }
}
