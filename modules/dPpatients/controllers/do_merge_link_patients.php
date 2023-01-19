<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientLink;
use Ox\Mediboard\System\CMergeLog;

CCanDo::checkEdit();

$patient_id_ref = CView::post("patient_id_referent", "ref class|CPatient");
$patients_ids   = CView::post("patients_ids", "str");
$status         = CView::post("status", "enum list|VALI|PROV default|PROV");
$link           = CView::post("link", "bool");

CView::checkin();

$patient_ref = new CPatient();
$patient_ref->load($patient_id_ref);

$patients_ids = explode("-", $patients_ids);
CMbArray::removeValue($patient_ref->_id, $patients_ids);

foreach ($patient_ref->loadList(array("patient_id" => CSQLDataSource::prepareIn($patients_ids))) as $_patient) {
  if ($link) {
    $patient_link              = new CPatientLink();
    $patient_link->patient_id1 = $patient_ref->_id;
    $patient_link->patient_id2 = $_patient->_id;

    if ($patient_link->loadMatchingObject()) {
      CAppUI::setMsg("CPatientLink-Already linked", UI_MSG_WARNING);
      continue;
    }

    $msg = $patient_link->store();
    CAppUI::setMsg($msg ?: "CPatientLink-msg-create", $msg ? UI_MSG_ERROR : UI_MSG_OK);

    continue;
  }

  $patient_ref->_merging = [
    $patient_ref->_id => $patient_ref->_id,
    $_patient->_id    => $_patient->_id
  ];

  $merge_log = CMergeLog::logStart(CUser::get()->_id, $patient_ref, [$_patient], true);

  try {
      $patient_ref->merge(array($_patient), true, $merge_log);
      $merge_log->logEnd();
      CAppUI::setMsg("CPatientState-_merge_patient", UI_MSG_OK);
  } catch (Throwable $t) {
      $merge_log->logFromThrowable($t);
      CAppUI::setMsg($t->getMessage(), UI_MSG_ERROR);
  }
}

$patient_ref->status = $status;

$msg = $patient_ref->store();

CAppUI::setMsg($msg ?: "CPatient-msg-modify", $msg ? UI_MSG_ERROR : UI_MSG_OK);

echo CAppUI::getMsg();
