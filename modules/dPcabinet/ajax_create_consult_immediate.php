<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\CPreferences;

$patient_id    = CView::get("patient_id", "ref class|CPatient");
$sejour_id     = CView::get("sejour_id", "ref class|CSejour");
$operation_id  = CView::get("operation_id", "ref class|COperation");
$grossesse_id  = CView::get("grossesse_id", "ref class|CGrossesse");
$callback      = CView::get("callback", "str");
CView::checkin();

$sejour = CSejour::findOrNew($sejour_id);

CAccessMedicalData::logAccess($sejour);
CAccessMedicalData::logAccess("COperation-$operation_id");

$patient = new CPatient();
$patient->load($patient_id);
$where = array();
$where["plageconsult.date"] = " = '" . CMbDT::date() . "'";
$where['consultation.type_consultation'] = "= 'consultation'";
$patient->loadRefsConsultations($where);
CStoredObject::massLoadFwdRef($patient->_ref_consultations, "plageconsult_id");

foreach ($patient->_ref_consultations as $_consult) {
  $plage = $_consult->loadRefPlageConsult();
  $plage->_ref_chir->loadRefFunction();
}

$consult = new CConsultation();
$consult->_datetime = CMbDT::dateTime();
$consult->patient_id = $patient->_id;
$consult->sejour_id = $sejour_id;
$consult->_operation_id = $operation_id;
$consult->grossesse_id = $grossesse_id;

$praticiens = CConsultation::loadPraticiens(PERM_EDIT);
$prefs = CPreferences::getAllPrefsUsers($praticiens);
foreach ($praticiens as $_prat) {
  if ($prefs[$_prat->user_id]["allowed_new_consultation"] == 0) {
    unset($praticiens[$_prat->_id]);
  }
}

CConsultation::guessUfMedicaleMandatory($praticiens);

$smarty = new CSmartyDP();
$smarty->assign("patient"   , $patient);
$smarty->assign("consult"   , $consult);
$smarty->assign("praticiens", $praticiens);
$smarty->assign("callback"  , $callback);
$smarty->display("inc_consult_immediate");
