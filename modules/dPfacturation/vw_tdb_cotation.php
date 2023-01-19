<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkEdit();

$patient_id             = CView::get("patient_id", "ref class|CPatient", true);
$praticien_id           = CView::get("praticien_id", "str default|-1", true);
$date_max               = CView::get("_date_max", "date default|now", true);
$date_min               = CView::get("_date_min", "date default|" . CMbDT::date("-1 month"), true);
$use_disabled_praticien = CView::get("use_disabled_praticien", "bool default|", true);
$get_consults           = CView::get("get_consults", "bool default|0");
$page                   = CView::get("page", "num default|0");

CView::checkin();
$consultations_par_page = 30;

$patient = new CPatient();
if ($patient_id) {
  $patient->load($patient_id);
}

$praticien      = new CMediusers();
$praticiens     = $praticien->loadPraticiens(PERM_EDIT);
$all_praticiens = $praticien->loadPraticiens(PERM_EDIT, null, null, null, false);
if ($praticien_id) {
  $praticien->load($praticien_id);
}

$consultation            = new CConsultation();
$consultation->_date_min = $date_min;
$consultation->_date_max = $date_max;

// Création du template
$smarty = new CSmartyDP();
if ($get_consults) {
  $where = array(
    "plageconsult.date"   => "BETWEEN '$date_min' AND '$date_max'",
    "consultation.valide" => "= '0'",
    "patient_id"          => "IS NOT NULL",
    "consultation.chrono" => "= '" . CConsultation::TERMINE . "'",
    "functions_mediboard.group_id" => "= '" . CGroups::loadCurrent()->_id .  "'",
  );
  if ($praticien_id > 0) {
    $where["plageconsult.chir_id"] = "= '$praticien_id'";
  }
  if ($patient_id) {
    $where["consultation.patient_id"] = "= '$patient_id'";
  }
  $ljoin               = array(
    "plageconsult" => "consultation.plageconsult_id = plageconsult.plageconsult_id",
    "users_mediboard" => "users_mediboard.user_id = plageconsult.chir_id",
    "functions_mediboard" => "users_mediboard.function_id = functions_mediboard.function_id"
  );
  $order               = "plageconsult.date DESC";
  $group               = "consultation_id";
  $consultations_count = $consultation->countList($where, null, $ljoin);
  $consultations       = $consultation->loadList($where, $order, "$page, $consultations_par_page", $group, $ljoin);

  CMbObject::massLoadFwdRef($consultations, "patient_id");
  CMbObject::massLoadFwdRef($consultations, "plageconsult_id");
  foreach ($consultations as $_consultation) {
    $_consultation->loadRefPatient();
    $_consultation->loadRefPraticien();
  }

  $smarty->assign("page", $page);
  $smarty->assign("consultations", $consultations);
  $smarty->assign("consultations_count", $consultations_count);
  $smarty->assign("consultations_par_page", $consultations_par_page);
}
else {
  $smarty->assign("patient", $patient);
  $smarty->assign("praticien", $praticien);
  $smarty->assign("praticiens", $praticiens);
  $smarty->assign("all_praticiens", $all_praticiens);
  $smarty->assign("use_disabled_praticien", $use_disabled_praticien);
  $smarty->assign("praticien_id", $praticien_id);
  $smarty->assign("consultation", $consultation);
}

$smarty->display("tdb_cotation/" . ($get_consults ? "tdb_cotation_consultations" : "tdb_cotation"));

