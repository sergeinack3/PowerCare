<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CFlotrGraph;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

CCanDo::checkAdmin();

ini_set("memory_limit", "2048M");
set_time_limit(3600);

$graphs = array();

$format = $format_regexp = null;
$formats = array(
  /* 05/Dec/2012:00:00:00 */
  "d/M/Y:H:i:s" => "/[0-9][0-9]\/[A-Z][a-z][a-z]\/[0-9][0-9][0-9][0-9]:[0-9][0-9]:[0-9][0-9]:[0-9][0-9]/",

  /* Sat Dec 01 04:36:44 2012 */
  "D M d H:i:s Y" => "/[A-Z][a-z][a-z]\s*[A-Z][a-z][a-z]\s*[0-9]*\s*[0-9][0-9]:[0-9][0-9]:[0-9][0-9]\s*[0-9][0-9][0-9][0-9]/",
);

$mode = CValue::get("mode", "error_log");

$count_by_day = array();
$count_by_hour = array();
$count_by_ip = array();
$count_by_hour_ip = array();
$filename = "";

if (isset($_FILES["formfile"])) {
  $upload = $_FILES["formfile"];
  $filename = $_FILES["formfile"]["name"][0];

  $f = fopen($upload["tmp_name"][0], "r");

  while (!feof($f)) {
    $line = fgets($f);

    if (!$line || $line == "\n") {
      break;
    }

    if (!$format) {
      foreach ($formats as $_format => $_format_regexp) {
        if (preg_match($_format_regexp, $line)) {
          $format_regexp = $_format_regexp;
          $format = $_format;
        }
      }
    }

    preg_match($format_regexp, $line, $date_reg);

    $date_reg = $date_reg[0];
    $date = DateTime::createFromFormat($format, $date_reg);
    $date = $date->format("Y-m-d H:i:s");

    preg_match("/[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+/", $line, $ip);

    // If a domain name, next line
    if (!isset($ip[0])) {
      continue;
    }

    $ip = $ip[0];

    $day  = CMbDT::transform(CMbDT::dateTime($date), null, "%Y-%m-%d");
    $hour = CMbDT::transform(CMbDT::dateTime($date), null, "%H");

    if (!isset($count_by_day[$day])) {
      $count_by_day[$day] = 0;
    }
    if (!isset($count_by_hour[$hour])) {
      $count_by_hour[$hour] = 0;
    }
    if (!isset($count_by_ip[$ip])) {
      $count_by_ip[$ip] = 0;
    }
    if (!isset($count_by_hour_ip[$hour])) {
      $count_by_hour_ip[$hour] = array();
    }
    if (!isset($count_by_hour_ip[$hour][$ip])) {
      $count_by_hour_ip[$hour][$ip] = 0;
    }

    $count_by_day[$day]++;
    $count_by_hour[$hour]++;
    $count_by_ip[$ip]++;
    $count_by_hour_ip[$hour][$ip]++;
  }

  ksort($count_by_hour);
  ksort($count_by_hour_ip);
  ksort($count_by_day);
  arsort($count_by_ip);

  foreach ($count_by_hour_ip as &$_count) {
    arsort($_count);
  }

  // Logs par heure
  $serie_a = array(
    'data' => array(),
    'label' => "Logs par heure"
  );
  $labels = array();

  foreach ($count_by_hour as $_hour => $count) {
    $labels[] = array(count($labels), $_hour);
    $serie_a['data'][] = array(count($serie_a['data']), $count);
  }

  $options = CFlotrGraph::merge("lines", array(
    'title'    => "Logs par heure",
    'xaxis'    => array('ticks' => $labels),
    'yaxis'    => array('tickDecimals' => 0,
                        'min' => 0,
                        'autoscaleMargin' => 1),
    'y2axis'    => array('tickDecimals' => 0,
                         'min' => 0,
                         'autoscaleMargin' => 1),
    'grid'     => array('verticalLines' => true),
  ));

  // Ips distinctes par hour (nb d'ips)
  $serie_b = array(
    'data' => array(),
    'yaxis' => 2,
    'label' => "Nombre d'ips par heure"
  );
  $labels = array();

  foreach ($count_by_hour_ip as $_count) {
    $serie_b['data'][] = array(count($serie_b['data']), count($_count));
  }


  $graphs["hour"] = array('series' => array($serie_a, $serie_b), 'options' => $options);

  // Logs par jour
  $serie = array(
    'data' => array(),
    'label' => "Logs par jour"
  );
  $labels = array();

  foreach ($count_by_day as $_day => $count) {
    $labels[] = array(count($labels), CMbDT::dateToLocale($_day));
    $serie['data'][] = array(count($serie['data']), $count);
  }

  $options = CFlotrGraph::merge("bars", array(
    'title'    => "Logs par jour",
    'xaxis'    => array('ticks' => $labels),
    'yaxis'    => array('tickDecimals' => 0),
    'grid'     => array('verticalLines' => true)
  ));

  $graphs["day"] = array('series' => array($serie), 'options' => $options);

  // Logs par IP
  $serie = array(
    'data' => array(),
    'label' => "Logs par ip"
  );
  $labels = array();

  foreach ($count_by_ip as $_ip => $count) {
    $labels[] = array(count($labels), $_ip);
    $serie['data'][] = array(count($serie['data']), $count);
  }

  $options = CFlotrGraph::merge("bars", array(
    'title'    => "Logs par ip",
    'xaxis'    => array('ticks' => $labels),
    'yaxis'    => array('tickDecimals' => 0),
    'grid'     => array('verticalLines' => true)
  ));

  $graphs["ip"] = array('series' => array($serie), 'options' => $options);
}

$smarty = new CSmartyDP();

$smarty->assign("graphs", $graphs);
$smarty->assign("filename", $filename);

$smarty->display("inc_inspect_error_log.tpl");
