<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$patient_id = CView::get("patient_id", "ref class|CPatient");

CView::checkin();

// Création du patient
$patient = new CPatient();
$patient->load($patient_id);

$patient->updateNomPaysInsee();
$patient->loadRefsCorrespondants();
$patient->loadRefsCorrespondantsPatient(null, true);
$patient->loadIPP();
$patient_insnir = $patient->loadRefPatientINSNIR();
$patient_insnir->createDatamatrix($patient_insnir->createDataForDatamatrix());

$sejours = $patient->loadRefsSejours();
CSejour::massLoadNDA($sejours);
CStoredObject::massLoadFwdRef($sejours, "praticien_id");
CStoredObject::massLoadBackRefs($sejours, "operations", "date ASC");

foreach ($patient->_ref_sejours as $sejour) {
  $sejour->loadRefPraticien();
  $operations = $sejour->loadRefsOperations();

  CStoredObject::massLoadFwdRef($operations, "plageop_id");
  foreach ($operations as $operation) {
    $operation->loadRefPlageOp();
    $operation->loadRefChir();
  }
}

$consultations = $patient->loadRefsConsultations();
CStoredObject::massLoadFwdRef($consultations, "plageconsult_id");
foreach ($consultations as $consultation) {
  $consultation->loadRefPlageConsult();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("patient", $patient);

if (CAppUI::gconf("dPpatients CPatient extended_print")) {
  $smarty->display("print_patient_extended.tpl");
}
else {
  $smarty->display("print_patient.tpl");
}
