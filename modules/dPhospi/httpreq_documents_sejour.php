<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::check();
$only_sejour  = CView::get("only_sejour", "bool default|0");
$with_patient = CView::get("with_patient", "bool default|0");
$operation_id = CView::get("operation_id", "ref class|COperation");
$op_with_sejour = CView::get("op_with_sejour", "bool default|0");
$sejour_id    = CView::get("sejour_id", "ref class|CSejour");
CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);
CAccessMedicalData::logAccess("COperation-$operation_id");

$sejour->loadRefPatient();
$sejour->loadRefsOperations();
$accident_travail = $sejour->loadRefAccidentTravail();

if (!$accident_travail->_id) {
  $accident_travail->object_class = $sejour->_class;
  $accident_travail->object_id    = $sejour->_id;
}

$sejour->canDo();

if (!$only_sejour) {
  $sejour->loadRefsConsultations("date DESC, heure DESC", array("consultation.annule" => " = '0'"));
  $sejour->loadRefsConsultAnesth()->loadRefConsultation();

  CStoredObject::massLoadFwdRef($sejour->_ref_consultations, "plageconsult_id");
  foreach ($sejour->_ref_consultations as $_consultation) {
    $_consultation->loadRefPlageConsult();
  }

  CStoredObject::massLoadFwdRef($sejour->_ref_operations, "plageop_id");
  foreach ($sejour->_ref_operations as $key => $_operation) {
    if ($operation_id && $_operation->_id != $operation_id) {
      unset($sejour->_ref_operations[$key]);
      continue;
    }

    $_operation->loadRefsConsultAnesth()->loadRefConsultation()->loadRefPlageConsult();
  }
}

if ($sejour->_ref_patient && CModule::getActive("appFineClient") && CAppUI::gconf("appFineClient Sync allow_appfine_sync")) {
  CAppFineClient::loadIdex($sejour->_ref_patient);
  $sejour->_ref_patient->loadRefStatusPatientUser();

  $count_order_no_treated = CAppFineClient::countOrderNotTreated($sejour, ['CFile', 'CCompteRendu']);
  $count_order_no_treated += CAppFineClient::countObjectReceivedForContext($sejour);
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("sejour"        , $sejour);
$smarty->assign("only_sejour"   , $only_sejour);
$smarty->assign("operation_id"  , $operation_id);
$smarty->assign("with_patient"  , $with_patient);
$smarty->assign("op_with_sejour", $op_with_sejour);
$smarty->assign("count_order", $count_order_no_treated ?? 0);
$smarty->display("inc_documents_sejour");
