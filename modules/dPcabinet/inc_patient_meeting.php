<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CPatientReunion;
use Ox\Mediboard\Cabinet\CReunion;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$meeting_id     = CView::get("meeting_id", "ref class|CReunion");
$patient_id     = CView::get("patient_id", "ref class|CPatient");
$selected_model = CView::get("selected_model", "str default|null");
CView::checkin();

// Get the meeting
$meeting = CReunion::findOrFail($meeting_id);
$meeting->loadRefsPatientReunion();

// Get the patient meeting
$patient_meeting             = new CPatientReunion();
$patient_meeting->patient_id = $patient_id;
$patient_meeting->reunion_id = $meeting_id;
$patient_meeting->loadMatchingObjectEsc();

// Get the available models
$models = (new CCompteRendu())->loadList(["object_class" => "= 'CPatientReunion'", "object_id" => "IS NULL"]);

$smarty = new CSmartyDP();

$smarty->assign("current_user", CMediusers::get());
$smarty->assign("patient_meeting", $patient_meeting);
$smarty->assign("models", $models);
$smarty->assign("selected_model", $patient_meeting->model_id);

$smarty->display("inc_patient_meeting");
