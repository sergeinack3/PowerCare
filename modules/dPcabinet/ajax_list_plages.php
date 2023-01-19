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
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::check();

global $listPraticiens;

$period           = CView::get("period", "str default|" . CAppUI::pref("DefaultPeriod"));
$periods          = array("day", "week", "month","weekly");
$chir_id          = CView::getRefCheckRead("chir_id", "ref class|CMediusers");
$function_id      = $chir_id ? null : CView::getRefCheckRead("function_id", "ref class|CFunctions");
$multipleMode     = CView::get("multipleMode", "bool default|0");
$date             = CView::get("date", "date default|now");
$plageconsult_id  = CView::get("plageconsult_id", "ref class|CPlageconsult");
$hour             = CView::get("hour", "str");
$hide_finished    = CView::get("hide_finished", "bool default|1");
$_line_element_id = CView::get("_line_element_id", "ref class|CPrescriptionLineElement");
$as_place         = CView::get("as_place", "bool default|0");
CView::checkin();

$ds = CSQLDataSource::get("std");

// Vérification des droits sur les praticiens
$listPraticiens = CConsultation::loadPraticiens(PERM_EDIT);
$listPrat = CConsultation::loadPraticiens(PERM_EDIT, $function_id, null, true);

$list_prat = array();

$chir = new CMediusers();
$chir->load($chir_id);
if ($chir->_id) {
  $list_prat = $chir->loadPraticiens(PERM_EDIT, $chir->function_id);
}

// Récupération des plages de consultation disponibles
$listPlage = new CPlageconsult;
$plage = new CPlageconsult;
$where = array();

if ($_line_element_id) {
  $where["pour_tiers"] = "= '1'";
}

$chir_sel = CSQLDataSource::prepareIn(array_keys($listPrat), $chir_id);
$where[] = "chir_id $chir_sel OR remplacant_id $chir_sel";

// Filtres
if ($hour) {
  $where["debut"] = "<= '$hour:00'";
  $where["fin"] = "> '$hour:00'";
}

if ($hide_finished) {
  $where[] = $ds->prepare("`date` >= %", CMbDT::date());
}

$minDate = $maxDate = $refDate = CMbDT::date(null, $date);



if ($period == "weekly") {
  CAppUI::requireModuleFile("dPcabinet", "inc_plage_selector_weekly");
  return;
}


switch ($period) {
  case "day":
    $minDate = $maxDate = $refDate = CMbDT::date(null, $date);
    $ndate = CMbDT::date("+1 day", $date);
    $pdate = CMbDT::date("-1 day", $date);
    break;

  case "week":
    $minDate = CMbDT::date("last sunday", $date);
    $maxDate = CMbDT::date("next saturday", $date);
    $refDate = CMbDT::date("+1 day", $minDate);
    $ndate = CMbDT::date("+1 week", $date);
    $pdate = CMbDT::date("-1 week", $date);
    break;

  case "4weeks":
    $minDate = CMbDT::date("last sunday", $date);
    $maxDate = CMbDT::date("+ 3 weeks", CMbDT::date("next saturday", $date));
    $refDate = CMbDT::date("+1 day", $minDate);
    $ndate = CMbDT::date("+4 week", $date);
    $pdate = CMbDT::date("-4 week", $date);
    break;

  case "month":
    $minDate = CMbDT::format($date, "%Y-%m-01");
    $maxDate = CMbDT::transform("+1 month", $minDate, "%Y-%m-01");
    $maxDate = CMbDT::date("-1 day", $maxDate);
    $refDate = $minDate;
    $ndate = CMbDT::date("first day of next month"   , $date);
    $pdate = CMbDT::date("last day of previous month", $date);
    break;

  default:
    $minDate = $maxDate = $refDate = CMbDT::date(null, $date);
    $ndate = CMbDT::date("+1 day", $date);
    $pdate = CMbDT::date("-1 day", $date);
    trigger_error("Période '$period' inconnue");
    break;
}

$bank_holidays = array_merge(CMbDT::getHolidays($minDate), CMbDT::getHolidays($maxDate));

$where["date"] = $ds->prepare("BETWEEN %1 AND %2", $minDate, $maxDate);
$where[] = "libelle != 'automatique' OR libelle IS NULL";

$ljoin["users"] = "users.user_id = plageconsult.chir_id OR users.user_id = plageconsult.remplacant_id ";

$order = "date, user_last_name, user_first_name, debut";

// Chargement des plages disponibles
/** @var CPlageconsult[] $listPlage */
$listPlage = $plage->loadList($where, $order, null, "plageconsult_id", $ljoin);

if (!array_key_exists($plageconsult_id, $listPlage)) {
  $plage->_id = $plageconsult_id = null;
}
$currPlage = new CPlageconsult();
foreach ($listPlage as $currPlage) {
  if (!$plageconsult_id && $date == $currPlage->date) {
    $plageconsult_id = $currPlage->_id;
  }

  if (array_key_exists($currPlage->date, $bank_holidays) && !CAppUI::pref('show_plage_holiday')) {
    unset($listPlage[$currPlage->_id]);
    continue;
  }

  $currPlage->_ref_chir = $listPrat[$currPlage->chir_id];
  $currPlage->loadCategorieFill();
  $currPlage->loadRefsNotes();
  $currPlage->countPatients();
  $currPlage->loadRefsConsultations(false);
  $currPlage->loadDisponibilities();

  // Chargement de l'agenda associé à la plage
  $currPlage->loadRefAgendaPraticien();
}
// Création du template
$smarty = new CSmartyDP();

$smarty->assign("period"         , $period);
$smarty->assign("periods"        , $periods);
$smarty->assign("hour"           , $hour);
$smarty->assign("hours"          , CPlageconsult::$hours);
$smarty->assign("hide_finished"  , $hide_finished);
$smarty->assign("date"           , $date);
$smarty->assign("today"          , CMbDT::date());
$smarty->assign("refDate"        , $refDate);
$smarty->assign("ndate"          , $ndate);
$smarty->assign("pdate"          , $pdate);
$smarty->assign("bank_holidays"  , $bank_holidays);
$smarty->assign("chir_id"        , $chir_id);
$smarty->assign("function_id"    , $function_id);
$smarty->assign("plageconsult_id", $plageconsult_id);
$smarty->assign("plage"          , $plage);
$smarty->assign("listPlage"      , $listPlage);
$smarty->assign("online"         , true);
$smarty->assign("_line_element_id", $_line_element_id);
$smarty->assign("multipleMode"    , (int)$multipleMode);

$smarty->assign("as_place"      , $as_place);
$smarty->assign("list_prat"      ,$list_prat);

$smarty->display("inc_list_plages");
