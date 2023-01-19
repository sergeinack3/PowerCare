<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CPatientReunion;

CCanDo::checkEdit();

$from_patient = CView::post("from_patient_meeting", "ref class|CPatientReunion");
$to_patients = CView::post("to_patients", "str");

CView::checkin();

// Fetch the active patient meeting object (displayed)
$active_patient_meeting = new CPatientReunion();
$active_patient_meeting->load($from_patient);
$apm = &$active_patient_meeting; // need a short var name

$patients = explode(",", $to_patients);

$patient_meeting = new CPatientReunion();
$patients_meeting = $patient_meeting->loadList(array("reunion_id" => "= $apm->reunion_id",
                                                     "patient_id" => CSQLDataSource::prepareIn($patients)));

foreach ($patients_meeting as $_p_meeting) {
  // Edit the object then store
  $_p_meeting->motif = $apm->motif;
  $_p_meeting->remarques = $apm->remarques;
  $_p_meeting->action = $apm->action;
  $_p_meeting->au_total = $apm->au_total;

  if ($msg = $_p_meeting->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
    CApp::rip();
  }
}

CAppUI::setMsg(CAppUI::tr("form-copied"));
echo CAppUI::getMsg();
