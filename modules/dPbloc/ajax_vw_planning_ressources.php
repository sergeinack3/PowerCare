<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Bloc\CRessourceMaterielle;
use Ox\Mediboard\Bloc\CTypeRessource;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::read();

$operation_id      = CValue::get("operation_id");
$type_ressource_id = CValue::get("type_ressource_id");
$date              = CValue::get("date");
$besoin_ressource_id = CValue::get("besoin_ressource_id");
$usage_ressource_id = CValue::get("usage_ressource_id");
$usage             = CValue::get("usage");

$operation = new COperation();
$operation->load($operation_id);

CAccessMedicalData::logAccess($operation);

$operation->loadRefPlageOp();
$debut_op = $operation->_datetime_best;

$type_ressource = new CTypeRessource();
$type_ressource->load($type_ressource_id);

$ressources = $type_ressource->loadRefsRessources();

if (!$date) {
  $date = CMbDT::date($debut_op);
}

$date_min   = $date." 00:00:00";
$date_max   = CMbDT::date("+1 day", $date)." 00:00:00";
$min_fin_op = min($date_max, CMbDT::addDateTime($operation->temp_operation, $debut_op));

$date_before = CMbDT::date("-1 day", $date);
$date_after = CMbDT::date("+1 day", $date);

$date_temp = $date_min;
$hours = array();

while ($date_temp < $date_max) {
  $hours[] = $date_temp;
  $date_temp = CMbDT::dateTime("+1 hour", $date_temp);
}

$operation->_debut_offset = CMbDT::position($debut_op, $date_min, "1hour");
$operation->_fin_offset   = CMbDT::position($min_fin_op, $date_min, "1hour");
$operation->_width        = $operation->_fin_offset - $operation->_debut_offset;


$ressource = new CRessourceMaterielle();
$ressource->type_ressource_id = $type_ressource_id;

// Les usages sur la période définie

$usages = $ressource->loadRefsUsages($date_min, $date_max);
CStoredObject::massLoadFwdRef($usages, "ressource_materielle_id");
$usages_by_ressource = array();

$besoins = CStoredObject::massLoadFwdRef($usages, "besoin_ressource_id");
CStoredObject::massLoadFwdRef($besoins, "operation_id");

$display_alert = array();

foreach ($usages as $_usage) {
  if (!isset($usages_by_ressource[$_usage->ressource_materielle_id])) {
    $usages_by_ressource[$_usage->ressource_materielle_id] = array();
  }

  $_ressource = $_usage->loadRefRessource();

  $_operation = $_usage->loadRefBesoin()->loadRefOperation();
  $_operation->loadRefPlageOp();
  $_debut_op = $_operation->_datetime;
  $_fin_op = CMbDT::addDateTime($_operation->temp_operation, $_debut_op);
  $fin_retab = CMbDT::addDateTime($_ressource->retablissement, $_fin_op);

  $_usage->_debut_offset = CMbDT::position(max($date_min, $_debut_op), $date_min, "1hour");
  $_usage->_fin_offset = CMbDT::position(min($date_max, $_fin_op), $date_min, "1hour");
  $_usage->_width = $_usage->_fin_offset - $_usage->_debut_offset;

  $_usage->_debut_offset_retablissement = $_usage->_fin_offset;
  $_usage->_fin_offset_retablissement = CMbDT::position(min($date_max, $fin_retab), $date_min, "1hour");
  $_usage->_width_retablissement = $_usage->_fin_offset_retablissement - $_usage->_debut_offset_retablissement;

  if ($min_fin_op <= $_debut_op && CMbDT::addDateTime($_ressource->retablissement, $min_fin_op) > $_debut_op) {
    $display_alert[$_ressource->_id] = 1;
  }

  if ($_usage->_width <= 0) {
    continue;
  }
  $usages_by_ressource[$_usage->ressource_materielle_id][] = $_usage;
}

// Les indispos sur cette même période
$indispos = $ressource->loadRefsIndispos($date_min, $date_max);
$indispos_by_ressource = array();

foreach ($indispos as $_indispo) {
  $_indispo->_debut_offset = CMbDT::position(max($date_min, $_indispo->deb), $date_min, "1hour");
  $_indispo->_fin_offset   = CMbDT::position(min($date_max, $_indispo->fin), $date_min, "1hour");
  $_indispo->_width        = $_indispo->_fin_offset - $_indispo->_debut_offset;
  if ($_indispo->_width <= 0) {
    continue;
  } 
  $indispos_by_ressource[$_indispo->ressource_materielle_id][] = $_indispo;
}

// Les besoins sur cete même période
$besoins = $ressource->loadRefsBesoins($date_min, $date_max);

unset($besoins[$besoin_ressource_id]);

foreach ($besoins as $key => $_besoin) {
  $_operation = $_besoin->loadRefOperation();
  $_operation->loadRefPlageOp();
  $_debut_op = $_operation->_datetime;
  $_fin_op = CMbDT::addDateTime($_operation->temp_operation, $_debut_op);
  $_besoin->_debut_offset = CMbDT::position($_debut_op, $date_min, "1hour");
  $_besoin->_fin_offset   = CMbDT::position(min($date_max, $_fin_op), $date_min, "1hour");
  $_besoin->_width        = $_besoin->_fin_offset - $_besoin->_debut_offset;
  if ($_besoin->_width <= 0) {
    unset($besoins[$key]);
  }
  foreach ($ressources as $_ressource) {
    if ($min_fin_op <= $_debut_op && CMbDT::addDateTime($_ressource->retablissement, $min_fin_op) > $_debut_op) {
      $display_alert[$_ressource->_id] = 1;
    }
  }
}

$smarty = new CSmartyDP();

$smarty->assign("ressources" , $ressources);
$smarty->assign("hours"      , $hours);
$smarty->assign("operation"  , $operation);
$smarty->assign("date"       , $date);
$smarty->assign("date_before", $date_before);
$smarty->assign("date_after" , $date_after);
$smarty->assign("besoins"    , $besoins);
$smarty->assign("usage"      , $usage);
$smarty->assign("display_alert", $display_alert);
$smarty->assign("usages_by_ressource"  , $usages_by_ressource);
$smarty->assign("indispos_by_ressource", $indispos_by_ressource);
$smarty->assign("type_ressource_id"    , $type_ressource_id);
$smarty->assign("besoin_ressource_id"  , $besoin_ressource_id);
$smarty->assign("usage_ressource_id"   , $usage_ressource_id);

$smarty->display("inc_vw_planning_ressources.tpl");

