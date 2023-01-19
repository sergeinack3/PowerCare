<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::read();

$modal            = CView::get("modal", "bool default|0");
$onclose_modal    = CView::get("onclose_modal", "str");
$NDA              = CView::get("NDA", "str");
$refreshPatient   = CView::get("refreshPatient", "bool default|0");
$auto_entree_bloc = CView::get("auto_entree_bloc", "bool default|0");
$sejour_id        = CView::get("sejour_id", "ref class|CSejour");
$operation_id     = CView::get("operation_id", "ref class|COperation");

CView::checkin();

$sejour  = new CSejour();
$patient = new CPatient();
$selOp   = new COperation();

// Si on scan le numéro du séjour d'un patient,
// on charge le patient et le séjour correspondant
if ($NDA) {
  $sejour->loadFromNDA($NDA);
  if ($sejour->_id) {
    $patient = $sejour->loadRefPatient();
    $patient->loadRefPhotoIdentite();

    foreach ($sejour->loadRefsOperations() as $_op) {
      $selOp = $_op;
    }

    if ($operation_id) {
      $selOp = $sejour->_ref_operations[$operation_id];
    }
  }

  if ($sejour_id) {
    $sejour->load($sejour_id);
    $sejour->loadRefPatient();
  }
}

CAccessMedicalData::logAccess($sejour);
CAccessMedicalData::logAccess($selOp);

$smarty = new CSmartyDP();
$smarty->assign("patient"         , $patient);
$smarty->assign("sejour"          , $sejour);
$smarty->assign("selOp"           , $selOp);
$smarty->assign("modal"           , $modal);
$smarty->assign("onclose_modal"   , $onclose_modal);
$smarty->assign("auto_entree_bloc", $auto_entree_bloc);
$smarty->assign("sejour_id"       , $sejour_id);
$smarty->assign("operation_id"    , $operation_id);

if ($refreshPatient) {
  $smarty->display("inc_code_barre_nda.tpl");
}
else {
  $smarty->display("vw_code_barre_nda.tpl");
}
