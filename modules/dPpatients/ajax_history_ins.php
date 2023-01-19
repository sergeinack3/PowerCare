<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CINSPatient;

$patient_id = CValue::get("patient_id");

$ins_patient             = new CINSPatient();
$ins_patient->patient_id = $patient_id;
$list_ins                = $ins_patient->loadMatchingList("date desc", null, "ins_patient_id");

$smarty = new CSmartyDP();
$smarty->assign("list_ins", $list_ins);
$smarty->display("inc_history_ins.tpl");