<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CPatientState;

if (!CAppUI::pref("allowed_modify_identity_status")) {
  CAppUI::accessDenied();
}

$date_min = CValue::getOrSession("_date_min");
$date_max = CValue::getOrSession("_date_max");

CValue::setSession("patient_state_date_min", $date_min);
CValue::setSession("patient_state_date_max", $date_max);

$patients_count = CPatientState::getAllNumberPatient($date_min, $date_max);

$smarty = new CSmartyDP();
$smarty->assign("patients_count", $patients_count);
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->display("patient_state/inc_manage_patient_state.tpl");