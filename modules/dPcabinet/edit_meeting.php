<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CReunion;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$meeting_id = CView::get("meeting_id", "ref class|CReunion notNull");
CView::checkin();

// Get the meeting
$meeting = CReunion::findOrFail($meeting_id);

$appointments      = $meeting->loadRefsAppointment();
$slots             = CStoredObject::massLoadFwdRef($appointments, "plageconsult_id");
$practitioners_ids = CStoredObject::massLoadFwdRef($slots, "chir_id");
CStoredObject::massLoadFwdRef($practitioners_ids, "function_id");
$practitioners = array_map(
    function (CConsultation $appointment) {
        $practitioner = $appointment->loadRefPlageConsult()->loadRefChir();
        $practitioner->loadRefFunction();

        return $practitioner;
    },
    $appointments
);

$patient_meeting_list = $meeting->loadRefsPatientReunion();
CStoredObject::massLoadFwdRef($patient_meeting_list, "patient_id");
foreach ($patient_meeting_list as $patient_meeting) {
    $patient_meeting->loadRefPatient();
}

// Get a "consultation" of one of the practitioner to get infos like dates, hours ...
$an_appointment = reset($meeting->_refs_consult);
$an_appointment->loadRefPlageConsult();
$an_appointment->_ref_plageconsult->date = CMbDT::format(
    $an_appointment->_ref_plageconsult->date,
    CAppUI::conf("date")
);

// Get the files
$models = (new CCompteRendu())->loadList(
    [
        'object_class' => "= 'CPatientReunion'",
        'object_id'    => "IS NULL",
        'type'         => "= 'body'",
    ]
);

$smarty = new CSmartyDP();

$smarty->assign("meeting", $meeting);
$smarty->assign("current_user", CMediusers::get());
$smarty->assign("patient_meeting_list", $patient_meeting_list);
$smarty->assign("models", $models);
$smarty->assign("an_appointment", $an_appointment);
$smarty->assign("practitioners", $practitioners);

$smarty->display("edit_meeting");
