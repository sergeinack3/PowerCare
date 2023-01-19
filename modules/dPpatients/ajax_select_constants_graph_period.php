<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

$selection    = json_decode(stripslashes(CValue::get('constants', '[]')));
$patient_id   = CValue::get('patient_id');
$context_guid = CValue::get('context_guid');
$period       = CValue::get('period', 'month');

$patient = CMbObject::loadFromGuid("CPatient-$patient_id");

$smarty = new CSmartyDP();
$smarty->assign('patient', $patient);
$smarty->assign('context_guid', $context_guid);
$smarty->assign('constants', json_encode($selection));
$smarty->assign('period', $period);
$smarty->display('inc_select_constants_graph_period.tpl');