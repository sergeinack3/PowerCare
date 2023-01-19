<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CPatient;

$patient_id = CValue::get("patient_id");

$patient = new CPatient();
$patient->load($patient_id);
$patient->loadRefsCorrespondantsPatient();

foreach ($patient->_ref_correspondants_patient as $_correspondant) {
  $_correspondant->loadRefsNotes();
}

$smarty = new CSmartyDP;

$smarty->assign("correspondants_by_relation", $patient->_ref_cp_by_relation);
$smarty->assign("nb_correspondants", count($patient->_ref_correspondants_patient));
$smarty->assign("patient_id", $patient_id);
$smarty->assign("patient", $patient);

$smarty->display("inc_list_correspondants.tpl");