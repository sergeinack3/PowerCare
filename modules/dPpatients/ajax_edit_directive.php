<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CDirectiveAnticipee;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkEdit();
$directive_anticipee_id = CView::get("directive_anticipee_id", "ref class|CDirectiveAnticipee");
$patient_id             = CView::get("patient_id", "ref class|CPatient");
CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);

$directive_anticipee = new CDirectiveAnticipee();
$directive_anticipee->load($directive_anticipee_id);

$patient->loadRefsCorrespondantsPatient();

$medical_holders = [];

$holders = $patient->loadRefsCorrespondants();
CStoredObject::massLoadFwdRef($holders, "medecin_id");

foreach ($holders as $_holder) {
  if ($_holder->loadRefMedecin()->_id) {
    $medical_holders[] = $_holder->_ref_medecin;
  }
}
if ($patient->_ref_medecin_traitant->_id) {
  $medical_holders[] = $patient->_ref_medecin_traitant;
}
if ($patient->_ref_pharmacie->_id) {
  $medical_holders[] = $patient->_ref_pharmacie;
}

$smarty = new CSmartyDP();
$smarty->assign("patient"            , $patient);
$smarty->assign("correspondants"     , $medical_holders);
$smarty->assign("directive_anticipee", $directive_anticipee);
$smarty->display("vw_edit_directive");
