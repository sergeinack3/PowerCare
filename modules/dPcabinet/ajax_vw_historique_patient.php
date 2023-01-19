<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$patient_id = CView::getRefCheckRead("patient_id", "ref class|CPatient");
$type       = CView::get("type", "enum list|sejour|consultation default|sejour");

CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);

switch ($type) {
  default:
  case "sejour":
    // Chargement de ses séjours
    $sejours = $patient->loadRefsSejours();
    CStoredObject::massLoadBackRefs($sejours, "operations");
    foreach ($sejours as $_sejour) {
      foreach ($_sejour->loadRefsOperations() as $_key_op => $_operation) {
        $_operation->loadRefChir()->loadRefFunction()->loadRefGroup();
      }
      $_sejour->loadRefPraticien();
    }
    break;
  case "consultation":
    foreach ($patient->loadRefsConsultations() as $_consultation) {
      $_consultation->loadRefPraticien()->loadRefFunction()->loadRefGroup();
    }
}

$smarty = new CSmartyDP();

$smarty->assign("patient", $patient);
$smarty->assign("type"   , $type);

$smarty->display("inc_vw_historique_patient.tpl");
