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
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientLink;

CCanDo::checkAdmin();

$patient_id_1 = CView::post("patient_1", "num notNull");
$patient_id_2 = CView::post("patient_2", "num notNull");

CView::checkin();

$patient_1 = new CPatient();
$patient_1->load($patient_id_1);
$patient_2 = new CPatient();
$patient_2->load($patient_id_2);

if (!$patient_1->_id || !$patient_2->_id) {
  CAppUI::stepAjax('CPatientSignature-patient-no-exist', UI_MSG_ERROR);
}

$patient_link = new CPatientLink();

$where = array(
  "patient_id1" => "= $patient_1->_id",
  "patient_id2" => "= $patient_2->_id",
  "type"        => "= 'HOMA'"
);
$patient_link->loadObject($where);

if (!$patient_link->_id) {
  $where = array(
    "patient_id1" => "= $patient_2->_id",
    "patient_id2" => "= $patient_1->_id",
    "type"        => "= 'HOMA'"
  );
  $patient_link->loadObject($where);
  if (!$patient_link->_id) {
    CAppUI::stepAjax('CPatientSignature-patient-no-homonyme', UI_MSG_ERROR);
  }
}

if ($msg = $patient_link->delete()) {
  CAppUI::stepAjax($msg, UI_MSG_ERROR);
}

CAppUI::stepAjax('CPatientLink-msg-delete', UI_MSG_OK);

CApp::rip();