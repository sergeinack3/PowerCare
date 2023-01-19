<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CPatient;

$correspondant_id = CView::get("correspondant_id", "ref class|CCorrespondantPatient");
$patient_id       = CView::get("patient_id", "ref class|CPatient");
$duplicate        = CView::get("duplicate", "bool");

CView::checkin();

$correspondant = new CCorrespondantPatient();
$patient       = new CPatient();

if ($correspondant->load($correspondant_id)) {
    $patient = $correspondant->loadRefPatient();

    if ($duplicate) {
        $correspondant->_id = "";
    }
} else {
    if ($patient->load($patient_id)) {
        $correspondant->patient_id = $patient_id;
        $correspondant->_duplicate = $duplicate;
        $correspondant->updatePlainFields();
    }
}

$patient->loadRefsCorrespondantsPatient();

$smarty = new CSmartyDP();

$smarty->assign("correspondant", $correspondant);
$smarty->assign("patient", $patient);

$smarty->display("inc_form_correspondant.tpl");
