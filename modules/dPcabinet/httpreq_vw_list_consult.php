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
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();
global $m;
$current_m = CView::get("current_m", "str default|$m");

$ds               = CSQLDataSource::get("std");
// get
$user             = CUser::get();
$boardItem        = CView::get("boardItem", "bool default|0");
$plageconsult_id  = CView::getRefCheckRead("plageconsult_id", "ref class|CPlageconsult");
$board            = CView::get("board", "bool default|0");
// get or session
$date             = CView::get("date", "date default|now", true);
$prat_id          = CView::get("chirSel", "num default|".$user->_id, true);
$function_id      = CView::getRefCheckRead("functionSel", "ref class|CFunctions", true);
$selConsult       = CView::get("selConsult", "num", true);
$vue              = CView::get("vue2", "bool default|0", true);
$withClosed       = CView::get("withClosed", "bool default|0", true);

$nb_consult = 0;

$today            = CMbDT::date();
if(!$board && !$boardItem) {
  $withClosed = 1;
}
else {
  $vue = 0;
}

$consult = new CConsultation();
// Test compliqué afin de savoir quelle consultation charger
if (isset($_GET["selConsult"])) {
  if ($consult->load($selConsult)) {
    $consult->loadRefPlageConsult(1);
    $prat_id = $consult->_ref_plageconsult->chir_id;
    CView::setSession("chirSel", $prat_id);
  }
  else {
    CView::setSession("selConsult");
  }
}
else {
  if ($consult->load($selConsult)) {
    $consult->loadRefPlageConsult(1);
    if ($prat_id !== $consult->_ref_plageconsult->chir_id) {
      $consult = new CConsultation();
      CView::setSession("selConsult");
    }
  }
}

CAccessMedicalData::logAccess($consult);

// On charge le praticien
$userSel = CMediusers::get($prat_id);

// Si un cabinet est sélectionné, vérification si au moins l'un des praticiens du cabinet est Medical
$is_medical_by_function = false;
if ($function_id) {
  $function = new CFunctions();
  $function->load($function_id);
  foreach ($function->loadRefsUsers() as $_user) {
    if ($_user->isMedical()) {
      $is_medical_by_function = true;
      break;
    }
  }
}
$canUserSel = $userSel->canDo();
if (!$userSel->isMedical() && !$is_medical_by_function) {
  CAppUI::setMsg("Vous devez selectionner un professionnel de santé", UI_MSG_ALERT);
  if ($current_m != "dPurgences") {
    CAppUI::redirect("m=dPcabinet&tab=0");
  }
}

$canUserSel->needsEdit(array("chirSel"=>0));

if ($consult->_id) {
  $date = $consult->_ref_plageconsult->date;
  CView::setSession("date", $date);
}
//Fermeture de session (après les set sessions)
CView::checkin();

$userSel->loadRefsSecondaryUsers();
$whereChir = $userSel->getUserSQLClause();

// Récupération des plages de consultation du jour et chargement des références
$plage = new CPlageconsult();
$ljoin = array();
$where = array();

if ($function_id) {
  $ljoin["users_mediboard"] = "users_mediboard.user_id = plageconsult.chir_id";
  $where["users_mediboard.function_id"] = " = '$function_id'";
}

if($prat_id) {
  $where['plageconsult.chir_id'] = $whereChir;
  $plage->chir_id = $userSel->_id;
}

$where['plageconsult.date'] = " = '$date'";
if ($plageconsult_id && $boardItem) {
  $where['plageconsult.plageconsult_id'] = " = $plageconsult_id";
}
$order = "plageconsult.debut";

/** @var CPlageconsult[] $listPlage */
$listPlage = $plage->loadList($where, $order, null, null, $ljoin);

CStoredObject::massCountBackRefs($listPlage, "notes");

foreach ($listPlage as $_plage) {
  $_plage->_ref_chir =& $userSel;
  $consultations = $_plage->loadRefsConsultations(false, !$vue && $withClosed);
  $_plage->loadRefsNotes();
  
  // Mass preloading
  $patients = CStoredObject::massLoadFwdRef($consultations, "patient_id");
  CStoredObject::massLoadFwdRef($consultations, "sejour_id");
  CStoredObject::massLoadFwdRef($consultations, "categorie_id");
  CStoredObject::massLoadBackRefs($patients, "bmr_bhre");
  CMbObject::massCountDocItems($consultations);
  CMbObject::countAlertDocs($consultations);

  /** @var CConsultAnesth[] $dossiers */
  CStoredObject::massLoadBackRefs($consultations, "consult_anesth");

  $nb_consult += count($consultations);

  foreach ($consultations as $_consultation) {
    $_consultation->loadRefPatient();
    $_consultation->loadRefSejour();
    $_consultation->loadRefCategorie();
    $_consultation->loadRefBrancardage();
    $_consultation->countDocItems();
    $_consultation->loadRefPraticien();
    $_consultation->_ref_patient->updateBMRBHReStatus($_consultation);
    $_consultation->_ref_categorie->getSessionOrder($_consultation->patient_id);
  }
}

// Si un praticien est sélectionné, on imprimera ses interventions
if ($prat_id) {
  $print_content_class = "CMediusers";
  $print_content_id    = $prat_id;
}
// Sinon, si un cabinet est sélectionnée, on imprimera les interventions du cabinet
elseif ($function_id) {
  $print_content_class = "CFunctions";
  $print_content_id    = $function_id;
}


// Récupération de la date du jour si $date
$current_date = ($date != $today) ? $today : null;

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("boardItem"          , $boardItem);
$smarty->assign("tab"                , "edit_consultation");
$smarty->assign("board"              , $board);
$smarty->assign("date"               , $date);
$smarty->assign("hour"               , CMbDT::time());
$smarty->assign("vue"                , $vue);
$smarty->assign("userSel"            , $userSel);
$smarty->assign("listPlage"          , $listPlage);
$smarty->assign("consult"            , $consult);
$smarty->assign("canCabinet"         , CModule::getCanDo("dPcabinet"));
$smarty->assign("current_m"          , $current_m);
$smarty->assign("mode_urgence"       , false);
$smarty->assign("current_date"       , $current_date);
$smarty->assign("withClosed"         , $withClosed);
$smarty->assign("nb_consult"         , $nb_consult);
$smarty->assign("print_content_class", $print_content_class);
$smarty->assign("print_content_id"   , $print_content_id);

$smarty->display("inc_list_consult");
