<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientGroup;

$patient_id = CValue::get('patient_id');

$patient = new CPatient();
$patient->load($patient_id);

$group  = CGroups::get();
$groups = $group->loadList();

$patient_groups = $patient->getSharingList();

$active_patient_groups = array();
foreach ($patient_groups as $_group_id => $_group_data) {
  if ($_group_data['share'] instanceof CPatientGroup) {
    $_group_data['share']->loadRefUser();

    if ($_group_data['share']->share) {
      $active_patient_groups[] = $_group_id;
    }
  }
}

// Current group checked by default if none set
if (empty($active_patient_groups) && !$patient->loadSharingGroups()) {
  $active_patient_groups[] = $group->_id;
}

$smarty = new CSmartyDP();
$smarty->assign('active_patient_groups', implode('|', $active_patient_groups));
$smarty->assign('patient_groups', $patient_groups);
$smarty->assign('groups', $groups);
$smarty->assign('patient', $patient);
$smarty->display('vw_patient_groups.tpl');