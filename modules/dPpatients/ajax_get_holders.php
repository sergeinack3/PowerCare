<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();
$patient_id = CView::get("patient_id", "ref class|CPatient");
CView::checkin();

$patient = CPatient::findOrFail($patient_id);

$patient->loadRefsCorrespondantsPatient();
$holder_data = [];
foreach ($patient->_ref_correspondants_patient as $_ref){
  $_ref->updateFormFields();
  $holder_data[] = ["guid" => $_ref->_guid, "view" => $_ref->_longview];
}

$medical_holders_data = [];
$holders              = $patient->loadRefsCorrespondants();
CStoredObject::massLoadFwdRef($holders, "medecin");
foreach ($holders as $_holder) {
  $medical_holders_data[] = $_holder->loadRefMedecin();
}

if ($patient->_ref_medecin_traitant) {
  $medical_holders_data[] = ["guid" => $patient->_ref_medecin_traitant->_guid, "view" => $patient->_ref_medecin_traitant->_view];
}
if ($patient->_ref_pharmacie) {
  $medical_holders_data[] = ["guid" => $patient->_ref_pharmacie->_guid, "view" => $patient->_ref_pharmacie->_view];
}


CApp::json(["holders" => $holder_data, "medical_holders" => $medical_holders_data]);