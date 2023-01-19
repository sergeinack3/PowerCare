<?php
/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;

/**
 * View stats
 */
CCanDo::checkRead();

$date_min = CValue::getOrSession("date_min", CMbDT::date("-7 DAYS"));
$date_max = CValue::getOrSession("date_max", CMbDT::date());

$service     = CValue::getOrSession("service");
$web_service = CValue::getOrSession("web_service");
$fonction    = CValue::getOrSession("fonction");

$services = array();
$ds = CSQLDataSource::get("std");
$services = $ds->loadColumn("SELECT type FROM echange_soap GROUP BY type");

if (!$date_min) {
  $date_min = CMbDT::date("-7 DAYS");
}
if (!$date_max) {
  $date_max = CMbDT::date();
}

$query = null;
               
$series  = $ticks = array();
$options = array();

if ($service) {
  for ($day = $date_min; $day <= $date_max; $day = CMbDT::date("+1 day", $day)) {
    $ticks[] = array(count($ticks), "$day");
    
    $query = "SELECT COUNT(DISTINCT `echange_soap_id`) as `nb_echanges`, SUM(`response_time`) as `resp`
              FROM `echange_soap`
              WHERE `date_echange` BETWEEN '$day 00:00:00' AND '$day 23:59:59'
              AND `type` = '$service'";
    
    if ($web_service) {
      $query .= "AND `web_service_name` = '$web_service'";
    }
  
    if ($fonction) {
      $query .= "AND `function_name` = '$fonction'";
    }
    
    $ds = CSQLDataSource::get("std");
    $results = $ds->loadList($query);
    $datas[$day] = $results[0];
  }
  
  $series[0]["label"]  = "Hits";
  
  $series[0]["color"]  = "#00A8F0";
  $series[0]["bars"]   = array("show" => true);
  
  $series[1]["label"]  = "Temps de réponse";
  $series[1]["color"]  = "#C0D800";
  $series[1]["yaxis"]  = 2;
  $series[1]["mouse"]  = array("track" => true);
  
  $options = array(
    'xaxis'  => array('ticks' => $ticks, 'labelsAngle' => 45),
    'yaxis'  => array('autoscaleMargin' => 1,
                      'title' => "Hits", 'titleAngle' => 90),
    'y2axis' => array('autoscaleMargin' => 1,
                      'title' => "Temps (ms)", 'titleAngle' => 90),
    'HtmlText' => false
    
  );

  foreach ($datas as $_data) {
    $series[0]["data"][] = array (@count($series[0]["data"]), $_data["nb_echanges"]);
    $tps_reponse = $_data["nb_echanges"] ? (($_data["resp"] * 1000) / $_data["nb_echanges"]) : 0;
    $series[1]["data"][] = array (@count($series[1]["data"]), $tps_reponse);
  }
}

$smarty = new CSmartyDP();

$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);

$smarty->assign("service"    , $service);
$smarty->assign("web_service", $web_service);
$smarty->assign("fonction"   , $fonction);

$smarty->assign("services", $services);

$smarty->assign("series" , $series);
$smarty->assign("options", $options);

$smarty->display("vw_stats.tpl");