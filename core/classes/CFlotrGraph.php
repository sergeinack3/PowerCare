<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

/**
 * Flotr utility class
 */
class CFlotrGraph {
  private static $profiles = array(
    // Base profile
    "base"  => array(
      "legend"      => array(
        "show"     => true,
        "position" => "nw",
      ),
      "grid"        => array(
        "verticalLines"   => false,
        "backgroundColor" => "#FFFFFF",
      ),
      "mouse"       => array(
        "relative" => true,
        "position" => "ne",
      ),
      "yaxis"       => array(
        "min"             => 0,
        "autoscaleMargin" => 1,
      ),
      "y2axis"      => array(
        "min"             => 0,
        "autoscaleMargin" => 1,
      ),
      "xaxis"       => array(
        "labelsAngle" => 45,
      ),
      "HtmlText"    => false,
      "spreadsheet" => array(
        "show"             => true,
        "tabGraphLabel"    => "Graphique",
        "tabDataLabel"     => "Donn&eacute;es",
        "toolbarDownload"  => "T&eacute;l&eacute;charger le fichier CSV",
        "toolbarSelectAll" => "S&eacute;lectionner le tableau",
        "csvFileSeparator" => ";",
        "decimalSeparator" => ",",
      ),
    ),

    // Lines graph
    "lines" => array(
      "lines"   => array("show" => true),
      "points"  => array("show" => true),
      "markers" => array("show" => true),
      "mouse"   => array("track" => true),
    ),

    // Bars graph
    "bars"  => array(
      "bars" => array(
        "show"        => true,
        "barWidth"    => 0.8,
        "fillOpacity" => 0.6,
      ),
    ),

    // Pie chart
    "pie"   => array(
      "pie" => array(
        "show"    => true,
        "explode" => 0,
      ),
    ),
  );

  /**
   * Initialize profiles strings
   *
   * @return void
   */
  static function initProfiles() {
    $ss = &self::$profiles["base"]["spreadsheet"];
    $ss["tabGraphLabel"]    = CAppUI::tr("CFlotrGraph-spreadsheet-Graph");
    $ss["tabDataLabel"]     = CAppUI::tr("CFlotrGraph-spreadsheet-Data");
    $ss["toolbarDownload"]  = CAppUI::tr("CFlotrGraph-spreadsheet-Download CSV");
    $ss["toolbarSelectAll"] = CAppUI::tr("CFlotrGraph-spreadsheet-Select table");
  }

  /**
   * Merge options with default options
   *
   * @param string|array $from            The profile name or an array of options
   * @param array        $options         Options
   * @param bool         $merge_with_base Merge with base
   *
   * @return array|bool
   */
  static function merge($from, $options = array(), $merge_with_base = true) {
    if (is_string($from) && isset(self::$profiles[$from])) {
      $from = self::$profiles[$from];
    }
    else {
      if (!is_array($from)) {
        return false;
      }
    }

    $base = $merge_with_base ? self::$profiles["base"] : array();

    return array_replace_recursive($base, $from, $options);
  }

  /**
   * Compute totales from series
   *
   * @param array $series  Series to compute the total of
   * @param array $options Options to take into account and adapt min and max
   *
   * @return void
   */
  static function computeTotals(&$series, &$options) {
    $serie = array();

    if (count($series) <= 1) {
      $series[0]["markers"]["show"] = true;

      return;
    }

    $options["xaxis"]["min"] = -0.5;
    $options["xaxis"]["max"] = count($series[0]["data"]) - 0.5;

    $options["yaxis"]["min"] = 0;
    $options["yaxis"]["max"] = null;

    // X totals
    foreach ($series as $_index => &$_serie) {
      $new_serie = array(count($series[$_index]["data"]), 0);

      foreach ($_serie["data"] as $_key => $_data) {
        $new_serie[1] += $_data[1];
      }

      $series[$_index]["data"][] = $new_serie;
    }
    unset($_serie);

    // Y totals
    foreach ($series as $_index => &$_serie) {
      foreach ($_serie["data"] as $_key => $_data) {
        if (!isset($serie[$_key])) {
          $serie[$_key] = array($_data[0], 0);
        }
        $serie[$_key][1] += $_data[1];
      }
    }
    unset($_serie);

    foreach ($serie as $_key => $_value) {
      if ($_key === count($serie) - 1) {
        break;
      }
      $options["yaxis"]["max"] = max($_value[1], $options["yaxis"]["max"]);
    }

    $options["yaxis"]["max"] *= 1.1;

    $series[] = array(
      "data"    => $serie,
      "label"   => "total",
      //"hide" => true, 
      "markers" => array("show" => true),
      "bars"    => array("show" => false),
      "lines"   => array("show" => false),
    );
  }
}

if(PHP_SAPI !== 'cli'){
    CFlotrGraph::initProfiles();
}
