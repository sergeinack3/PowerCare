<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CFunctions;

$ds = CSQLDataSource::get("std");

// Initialisation des variables
global $period, $periods, $chir_id, $function_id, $date_range, $ndate, $pdate, $heure, $plageconsult_id, $consultation_id;

$hour             = CValue::get("hour");
$hide_finished    = CValue::get("hide_finished", true);
$_line_element_id = CValue::get("_line_element_id");
$multipleMode     = CValue::get("multipleMode", 0);
$multiple_edit    = CValue::get("multipleEdit", 0);

if ($multiple_edit || $consultation_id) {
  $hide_finished = false;
}

//if multiple, no weekly planner
if ($multipleMode) {
  $periods         = array("day", "week", "month");
  if ($period == "weekly") {
    $period = "month";
  }
}

$consultation_ids = array();
// next consultations in editMultiple
if ($consultation_id) {
  $consultation_ids = array();
  $consultation_temp = new CConsultation();
  $consultation_temp->load($consultation_id);
  $consultation_temp->loadRefPlageConsult()->loadRefChir();
  $consultation_temp->loadRefElementPrescription();

  // we add the first consult to the future json list (first element)
  if (!$consultation_temp->annule && $consultation_temp->chrono = 16) {
    $consultation_ids[] = array(
      $consultation_temp->plageconsult_id,
      $consultation_temp->_id,
      $consultation_temp->_ref_plageconsult->date,
      $consultation_temp->heure,
      $consultation_temp->_ref_plageconsult->chir_id,
      $consultation_temp->_ref_plageconsult->_ref_chir->_view,
      $consultation_temp->annule,
      $consultation_temp->rques,
      $consultation_temp->element_prescription_id,
      $consultation_temp->element_prescription_id ? $consultation_temp->_ref_element_prescription->libelle : "",
    );
  }

  //edit mod
  if ($multiple_edit) {
    $plage_temp = $consultation_temp->_ref_plageconsult;
    $where_next = array();
    $ljoin_next = array();
    $limit = CAppUI::pref("NbConsultMultiple");
    $date_ref = CAppUI::pref("today_ref_consult_multiple") ? CMbDT::date() : $plage_temp->date ;
    $ljoin_next["plageconsult"] = "plageconsult.plageconsult_id = consultation.plageconsult_id";
    $where_next["consultation.patient_id"] = "= '$consultation_temp->patient_id'";
    $where_next["consultation.annule"] = "= '0'";     //only not cancelled
    $where_next["consultation.chrono"] = "< '48'";     //only not finished and not arrived
    $where_next["plageconsult.date"] = ">= '$date_ref'";
    $where_next[$consultation_temp->_spec->key] = "!= '$consultation_id'";
    /** @var $_consult CConsultation */
    foreach ($consultation_temp->loadListWithPerms(PERM_READ, $where_next, "date", $limit, null, $ljoin_next) as $_consult) {
      $consultation_temp->loadRefPlageConsult()->loadRefChir();
      $_consult->loadRefElementPrescription();
      $consultation_ids[]= array(
        $_consult->plageconsult_id,
        $_consult->_id,
        $_consult->_ref_plageconsult->date,
        $_consult->heure,
        $_consult->_ref_chir->_id,
        $_consult->_ref_chir->_view,
        $_consult->annule,
        $_consult->rques,
        $_consult->element_prescription_id,
        $_consult->_ref_element_prescription->libelle,
      );
    }
  }
}

if ($heure && !$consultation_id) {
  $consultation_ids[] = array(
    $plageconsult_id,
    null,
    $date_range,
    $heure,
    $chir_id,
    "chir",
    0,
    null,
    null,
    null
  );
}

//functions
$function       = new CFunctions();
$listFunctions  = $function->loadSpecialites(PERM_EDIT);

// Récupération des plages de consultation disponibles
$plage = new CPlageconsult();
$where = array();

// Praticiens sélectionnés
$listPrat = CConsultation::loadPraticiens(PERM_EDIT, $function_id, null, true);

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

$minDate = $maxDate = $refDate = CMbDT::date(null, $date_range);
// Filtre de la période
switch ($period) {
  case "day":
    $minDate = $maxDate = $refDate = CMbDT::date(null, $date_range);
    break;

  case "week":
    $minDate = CMbDT::date("last sunday", $date_range);
    $maxDate = CMbDT::date("next saturday", $date_range);
    $refDate = CMbDT::date("+1 day", $minDate);
    break;

  case "4weeks":
    $minDate = CMbDT::date("last sunday", $date_range);
    $maxDate = CMbDT::date("+ 3 weeks", CMbDT::date("next saturday", $date_range));
    $refDate = CMbDT::date("+1 day", $minDate);
    break;

  case "month":
    $minDate = CMbDT::format($date_range, "%Y-%m-01");
    $maxDate = CMbDT::transform("+1 month", $minDate, "%Y-%m-01");
    $maxDate = CMbDT::date("-1 day", $maxDate);
    $refDate = $minDate;
    break;

  default:
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

foreach ($listPlage as $currPlage) {
  if (!$plageconsult_id && $date_range == $currPlage->date) {
    $plageconsult_id = $currPlage->_id;
  }

  $currPlage->_ref_chir = $listPrat[$currPlage->chir_id];
  $currPlage->loadFillRate();
  $currPlage->loadCategorieFill();
  $currPlage->loadRefsNotes();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("period"         , $period);
$smarty->assign("periods"        , $periods);
$smarty->assign("hour"           , $hour);
$smarty->assign("hours"          , CPlageconsult::$hours);
$smarty->assign("hide_finished"  , $hide_finished);
$smarty->assign("date"           , $date_range);
$smarty->assign("refDate"        , $refDate);
$smarty->assign("ndate"          , $ndate);
$smarty->assign("pdate"          , $pdate);
$smarty->assign("bank_holidays"  , $bank_holidays);
$smarty->assign("chir_id"        , $chir_id);
$smarty->assign("function_id"    , $function_id);
$smarty->assign("plageconsult_id", $plageconsult_id);
$smarty->assign("plage"          , $plage);
$smarty->assign("listPlage"      , $listPlage);
$smarty->assign("listFunctions"  , $listFunctions);
$smarty->assign("consultation_id", $consultation_id);
$smarty->assign("consultation_ids", $consultation_ids);
$smarty->assign("online"         , true);
$smarty->assign("_line_element_id", $_line_element_id);
$smarty->assign("multipleMode"   , $multipleMode);
$smarty->assign("multiple_edit"  , $multiple_edit);

$smarty->display("plage_selector.tpl");
