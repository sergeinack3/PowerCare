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

$patient_id_1 = CView::post("patient_1", "ref class|CPatient");
$patient_id_2 = CView::post("patient_2", "ref class|CPatient");

CView::checkin();

if (!$patient_id_1 || !$patient_id_2) {
  CAppUI::stepAjax('CPatientSignature-two-must-check', UI_MSG_ERROR);
}
$patient_1 = new CPatient();
$patient_1->load($patient_id_1);
$patient_2 = new CPatient();
$patient_2->load($patient_id_2);

if (!$patient_1->_id || !$patient_2->_id) {
  CAppUI::stepAjax('CPatientSignature-no-exist', UI_MSG_ERROR);
}

$patient_link              = new CPatientLink();
$patient_link->patient_id1 = $patient_1->_id;
$patient_link->patient_id2 = $patient_2->_id;
$patient_link->type        = "HOMA";

$patient_link->loadMatchingObject();
if ($patient_link->_id) {
  CAppUI::stepAjax('CPatientLink-msg-homonymes-exist', UI_MSG_ERROR);
}
$patient_link->patient_id1 = $patient_2->_id;
$patient_link->patient_id2 = $patient_1->_id;

$patient_link->loadMatchingObject();
if ($patient_link->_id) {
  CAppUI::stepAjax('CPatientLink-msg-homonymes-exist', UI_MSG_ERROR);
}

if ($msg = $patient_link->store()) {
  CAppUI::stepAjax($msg, UI_MSG_ERROR);
}

//CPatientLink::deleteDoubloon();

CAppUI::stepAjax('CPatientLink-msg-create');
CApp::rip();