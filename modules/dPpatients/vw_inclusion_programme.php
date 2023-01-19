<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CInclusionProgramme;
use Ox\Mediboard\Patients\CPatient;

$patient_id = CView::getRefCheckRead("patient_id", "ref class|CPatient");
CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);
$patient->canDo();

//list des programmes
$where_inclusion               = [];
$where_inclusion["patient_id"] = " = '$patient->_id'";

$inclusion_programme = new CInclusionProgramme();
$inclusions_patient  = $inclusion_programme->loadList($where_inclusion);

foreach ($inclusions_patient as $_inclusion_patient) {
    $_inclusion_patient->loadRefProgrammeClinique();
}

$smarty = new CSmartyDP();
$smarty->assign("inclusion_programme", $inclusion_programme);
$smarty->assign("inclusions_patient", $inclusions_patient);
$smarty->assign("patient", $patient);
$smarty->display("vw_inclusion_programme.tpl");
