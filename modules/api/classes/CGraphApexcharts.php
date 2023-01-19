<?php
/**
 * @package Mediboard\api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Api;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbArray;
use Ox\Mediboard\Patients\Constants\CAbstractConstant;

/**
 * Description
 */
abstract class CGraphApexcharts implements IShortNameAutoloadable {

  const SIZE_LARGE = "large";
  const SIZE_NORMAL = "normal";
  const SIZE_SMALL = "small";

  // data
  protected $data;
  /** @var CApexchartsConf $conf */
  protected $conf;
  protected $series = [];
  protected $generatedData = [];
  protected $chartOptions = [];
  protected $adaptOptions = [];

  // attributs
  protected $_series_length = 0;
  protected $_series_size = array();
  protected $_curr_idx_serie = 0;
  protected $_curr_idx_value = 0;

  protected $_goal_dt_achieved = null;
  protected $_goal_index_achieved = null;

  /**
   * Generate option and data for build chart. Need to call getChart() to parse it.
   *
   * @param array|null $data data for chart : 'config' => CApexChartsConf, 'data' => array_series(array_values)
   *
   * @return $this
   */
  public function buildChart($data = null) {
    $this->initialize($data);
    $this->buildSeries();
    $this->buildOptions();

    return $this;
  }

  /**
   * Get chart parse for library ApexCharts
   *
   * @return array
   */
  public function getChart() {
    return array(
      "series" => $this->series,
      "chartOptions" => $this->chartOptions,
      "adaptOptions" => $this->adaptOptions,
      "data" => $this->generatedData,
      "context" => "", //todo init le context
      "title" => "" // todo init le titre
    );
  }

  /**
   * Allow to add calculated data in field generatedData
   *
   * @param string $name  key of generated data
   * @param mixed  $value value for generated data
   *
   * @return $this
   */
  public function addgeneratedData($name, $value) {
    $this->generatedData[$name] = $value;
    return $this;
  }

  /**
   * Add option in conf
   *
   * @param string $name  key option
   * @param mixed  $value value option
   *
   * @return void
   */
  protected function addChartOption($name, $value) {
    $this->chartOptions[$name] = $value;
  }

  /**
   * Initialize class variables to build chart
   *
   * @param array $data data
   *
   * @return void
   */
  protected function initialize($data) {
    $this->data = CMbArray::get($data, "data", $this->data);
    $this->conf = CMbArray::get($data, "config", $this->conf);
    //$this->_identification_source = CMbArray::get($this->conf, "identification_source", false); //todo a revoir
  }

  /**
   * Build data to generate series
   *
   * @return void
   */
  protected function buildSeries() {
    $parsed_data          = array();
    $this->_series_length = count($this->data);
    // parcours series
    foreach ($this->data as $_serie) {
      $this->_series_size[$this->_curr_idx_serie] = count($_serie);
      $this->_curr_idx_value                      = 0;
      // parcours des values
      foreach ($_serie as $_data) {
        $_parsed_data = $this->parseData($_data);
        $this->calculateData($_parsed_data);
        $this->checkGoal($_parsed_data);
        $this->_curr_idx_value                 += 1;
        $parsed_data[$this->_curr_idx_serie][] = $_parsed_data;
      }
      $this->_curr_idx_serie += 1;
    }
    // set labels
    $this->setSeries($parsed_data);
  }

  /**
   * Parse in function of data
   *
   * @param CAbstractConstant | mixed $data
   *
   * @return mixed
   */
  protected abstract function parseData($data);

  /**
   * Generate calculated constants with data in chart
   *
   * @param array $data data parsed
   *
   * @return void
   */
  protected function calculateData($data) {
    $val = CMbArray::get($data, "y");
    if (CMbArray::get($this->conf->aggregate, $this->_curr_idx_serie)) {
      $this->generatedData["aggregate"][$this->_curr_idx_serie] =
        CMbArray::getRecursive($this->generatedData, "aggregate $this->_curr_idx_serie", 0) + $val;
    }
  }

  /**
   * Generate options and data to add goal
   *
   * @param array $data data parsed
   *
   * @return void
   */
  protected function checkGoal($data) {
    if (!($goal = CMbArray::getRecursive($this->conf->goals, "$this->_curr_idx_serie"))) {
      return;
    }
    $goalValue = CMbArray::get($goal, "valueGoal");
    $aggregate = CMbArray::getRecursive($this->generatedData, "aggregate $this->_curr_idx_serie");
    // if goal is not achieved
    if ($aggregate < $goalValue) {
      return;
    }

    $this->adaptOptions["transform_color"] = true;
    if (CMbArray::get($goal, "typeGoal") == CApexchartsConf::GOAL_TYPE_VERTICALLY) {
      // index when goal is achieved
      $this->adaptOptions["goal_index_achieved"][$this->_curr_idx_serie] = $this->_curr_idx_value;
      $this->adaptOptions["goal_dt_achieved"][$this->_curr_idx_serie]    = CMbArray::get($data, "x");
    }
  }

  /**
   * Set data parsed in series
   *
   * @param array $parsed_data data parsed
   *
   * @return void
   */
  protected function setSeries(array $parsed_data) {
    $series = array();
    $labels = $this->conf->series_labels;
    for ($i = 0; $i < $this->_series_length; $i++) {
      $label    = CMbArray::get($labels, $i, "");
      $series[] = array(
        "name" => $label,
        "data" => CMbArray::get($parsed_data, $i)
      );
    }
    $this->series = array_values($series);
  }

  /**
   * Analyse and build options for chart in function of CApexchartsConf given
   *
   * @return void
   */
  protected function buildOptions() {
    $this->chartOptions = $this->conf->chartOptions;

    if (count($this->conf->goals) > 0) {
      foreach ($this->conf->goals as $_idx_serie => $_goal) {
        $this->adaptOptions["goal_achievement"][$_idx_serie] = CMbArray::get($_goal, "valueGoal");
        $this->adaptOptions["goal_color"][$_idx_serie]       = CMbArray::get($_goal, "colorGoal");
      }
    }
  }

  /**
   * Set configuration for chart
   *
   * @param CApexchartsConf $conf configuration for chart
   *
   * @return void
   */
  public function setConf(CApexchartsConf $conf) {
    $this->conf = $conf;
  }
}
