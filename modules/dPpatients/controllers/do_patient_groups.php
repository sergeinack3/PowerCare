<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientGroup;

$group_ids  = CValue::post('patient_groups');
$patient_id = CValue::post('patient_id');

$group  = CGroups::get();
$groups = $group->loadList();

$patient = new CPatient();
$patient->load($patient_id);

$patient->loadSharingGroups();

// Enable for that groups
if ($group_ids) {
  $group_ids        = explode('|', $group_ids);
  $_all_groups_ids  = CMbArray::pluck($groups, "_id");
  $groups_to_remove = array_diff($_all_groups_ids, $group_ids);

  // Checked groups
  foreach ($group_ids as $_group_id) {
    if (!$patient->checkSharingGroup($_group_id)) {
      $_patient_group             = new CPatientGroup();
      $_patient_group->patient_id = $patient_id;
      $_patient_group->group_id   = $_group_id;

      $_patient_group->loadMatchingObject();

      $_patient_group->share = true;

      if ($msg = $_patient_group->store()) {
        CAppUI::setMsg($msg, UI_MSG_WARNING);
      }
      else {
        CAppUI::setMsg("{$_patient_group->_class}-msg-modify", UI_MSG_OK);
      }
    }
  }

  // Non-checked groups
  foreach ($groups_to_remove as $_group_id) {
    $_patient_group             = new CPatientGroup();
    $_patient_group->patient_id = $patient_id;
    $_patient_group->group_id   = $_group_id;

    $_patient_group->loadMatchingObject();

    $_patient_group->share = false;

    if ($msg = $_patient_group->store()) {
      CAppUI::setMsg($msg, UI_MSG_WARNING);
    }
    else {
      CAppUI::setMsg("{$_patient_group->_class}-msg-modify", UI_MSG_OK);
    }
  }
}
// Disable for all groups
else {
  foreach ($groups as $_group) {
    $_patient_group             = new CPatientGroup();
    $_patient_group->patient_id = $patient_id;
    $_patient_group->group_id   = $_group->_id;

    $_patient_group->loadMatchingObject();

    $_patient_group->share = false;

    if ($msg = $_patient_group->store()) {
      CAppUI::setMsg($msg, UI_MSG_WARNING);
    }
    else {
      CAppUI::setMsg("{$_patient_group->_class}-msg-modify", UI_MSG_OK);
    }
  }
}

echo CAppUI::getMsg();
CApp::rip();