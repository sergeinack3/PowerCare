<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$prat_id       = CView::getRefCheckRead("prat_id", "ref class|CMediusers");
$function_id   = CView::getRefCheckRead("function_id", "ref class|CFunctions");
$date          = CView::get("date", "date default|now");
$only_func     = CView::get("only_func", "bool default|0");
$rdv           = CView::get("rdv", "bool default|0");
$prats_ids     = CView::get("prats_ids", "str", true);
$days          = CView::get("days", "str", true);
$times         = CView::get("times", "str", true);
$libelle_plage = CView::get("libelle_plage", "str", true);

// Récupération des différents tableaux
if ($prats_ids) {
  $prats_ids = explode(",", $prats_ids);
}
else {
  $prats_ids = array($prat_id);
}

CView::checkin();
CView::enableSlave();

// Commencer la recherche à x semaine(s)
$offset_week = CAppUI::pref("search_free_slot");
$_date       = CMbDT::date("+$offset_week week", $date);

$monday      = CMbDT::date("this week", $_date);
$week_number = date('W', strtotime($monday));

// days
$days_name = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");

// connaître le jour de la semaine
$current_day_number = CMbDT::format(CMbDT::date(), "%w");
$current_day        = $days_name[$current_day_number - 1];

if ($days) {
  $days = explode(",", $days);
}
else {
  $days[] = $current_day;
}

//hours
$times_hour = array();
for ($i = 7; $i < 21; $i++) {
  $prefix       = ($i < 10) ? "0" : "";
  $times_hour[] = $prefix . "$i:00:00";
}

$count_hours = 0;

if ($times) {
  $times       = explode(",", $times);
  $count_hours = count($times);
}
else {
  $times = array();
}

$praticien = new CMediusers();
$listPrat  = $praticien->loadPraticiens("PERM_EDIT", $function_id);

$list_functions = new CFunctions();
if ($function_id) {
  $list_functions = new CFunctions();
  $list_functions->load($function_id);

  $listPrat              = CConsultation::loadPraticiens(PERM_EDIT, $function_id, null, true);
  $secondaries_functions = $list_functions->loadBackRefs("secondary_functions");
}
$prat = new CMediusers();
if ($prat_id) {
  $prat->load($prat_id);
}

// Génération du content
$smarty = new CSmartyDP();
$smarty->assign("list_functions", $list_functions);
$smarty->assign("listPrat"      , $listPrat);
$smarty->assign("praticien"     , $prat);
$smarty->assign("function"      , $function_id);
$smarty->assign("prat_id"       , $prat_id);
$smarty->assign("days_name"     , $days_name);
$smarty->assign("date"          , $date);
$smarty->assign("current_day"   , $current_day);
$smarty->assign("times_hour"    , $times_hour);
$smarty->assign("only_func"     , $only_func);
$smarty->assign("rdv"           , $rdv);
$smarty->assign("debut"         , $monday);
$smarty->assign("week_number"   , $week_number);
$smarty->assign("prats_ids"     , $prats_ids);
$smarty->assign("days"          , $days);
$smarty->assign("times"         , $times);
$smarty->assign("count_hours"   , $count_hours);
$smarty->assign("libelle_plage" , $libelle_plage);
$smarty->display("inc_next_slots");

