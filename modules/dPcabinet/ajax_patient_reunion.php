<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CPatientReunion;
use Ox\Mediboard\Cabinet\CReunion;

CCanDo::checkEdit();

$reunion_id = CView::get("reunion_id", "ref class|CReunion notNull");
CView::checkin();

$meeting          = CReunion::findOrFail($reunion_id);
$patients_meeting = $meeting->loadRefsPatientReunion();

CStoredObject::massLoadFwdRef($patients_meeting, "patient_id");
foreach ($patients_meeting as $_patient_meeting) {
    $_patient_meeting->loadRefPatient();
}

$nom_order    = CMbArray::pluck($patients_meeting, "_ref_patient", "nom");
$prenom_order = CMbArray::pluck($patients_meeting, "_ref_patient", "prenom");
array_multisort(
    $nom_order,
    SORT_ASC,
    $prenom_order,
    SORT_ASC,
    $patients_meeting,
    SORT_ASC
);

$smarty = new CSmartyDP();

$smarty->assign("meeting", $meeting);
$smarty->assign("patient_meeting", new CPatientReunion());
$smarty->assign("patients_meeting", $patients_meeting);

$smarty->display("inc_patient_reunion");
