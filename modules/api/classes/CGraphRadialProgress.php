<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Api;

use Ox\Core\CMbArray;


/**
 * Description
 */
class CGraphRadialProgress extends CGraphApexcharts {

  protected $_unique_value = false;

  /**
   * Get default configuration
   *
   * @return CApexchartsConf
   */
  public static function getConf() {
    $options                                   = array();
    $options["chart_sparkline_enabled"]        = true;
    $options["chart_type"]                     = "radialBar";
    $options["chart_height"]                   = "100%";
    $options["pltop_radial_hollow_size"]       = "65%";
    $options["pltop_radial_hollow_background"] = "#eeeeee";
    $options["pltop_radial_value_color"]       = "#000";
    $options["pltop_radial_hollow_margin"]     = "0";
    $options["pltop_radial_value_show"]        = true;
    $options["pltop_radial_fontSize"]          = "1.25rem";
    $options["stroke_lineCap"]                 = "round";
    $options["colors"]                         = ["#20EE47"];
    $options["radial_shadow_hollow"]           = true;
    $options["chart_animations_speed"]         = 2500;
    $options["legend_show"]                    = false;
    $options["tooltip_enabled"]                = false;
    $options["fill_gradient"]                  = true;
    $conf                                      = new CApexchartsConf();
    $conf->setChartOptions($options);

    return $conf;
  }

  /**
   * Set unique value for radial chart
   *
   * @param int $value_int value
   *
   * @return void
   */
  public function setValue($value_int) {
    $this->_unique_value = true;
    $this->series        = array($this->parseData($value_int));
  }

  /**
   * @inheritDoc
   */
  protected function parseData($data) {
    return array($data);
  }

  /**
   * @inheritDoc
   */
  public function getChart() {
    //todo fonction pour la retro-compatibilité
    $chart            = parent::getChart();
    $combined         = array_merge(CMbArray::get($chart, "adaptOptions"), CMbArray::get($chart, "chartOptions"));
    $chart["options"] = $combined;

    return $chart;
  }

  /**
   * @inheritDoc
   */
  protected function setSeries(array $parsed_data) {
    $parsed_data  = reset($parsed_data);
    $this->series = reset($parsed_data);
  }

  /**
   * @inheritDoc
   */
  protected function buildSeries() {
    if ($this->_unique_value) {
      return;
    }

    parent::buildSeries();
  }
}
