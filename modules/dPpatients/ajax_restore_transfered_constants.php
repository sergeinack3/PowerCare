<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\System\CUserLog;

$start_time = microtime(true);

CCanDo::checkAdmin();

$date = CValue::get('date');
$step = CValue::get('step', 100);

$start = CValue::getOrSession('start_restore_constants', 0);

$converted_constants = array(
  'glycemie',
  'cetonemie',
  'ta',
  'ta_gauche',
  'ta_droit',
  'ta_couche',
  'ta_assis',
  'ta_debout',
  'hemoglobine_rapide',
);

$user_log              = new CUserLog();
$where                 = array();
$where['object_class'] = " = 'CConstantesMedicales'";
$where['type']         = " = 'store'";
$whereOr               = array();
$whereOr[]             = "`fields` LIKE '%context_id%'";
$whereOr[]             = "`fields` LIKE '%patient_id%'";
$where[]               = implode(' OR ', $whereOr);
$whereOr               = array();
foreach ($converted_constants as $_constant) {
  $whereOr[] = "`fields` LIKE '%$_constant%'";
}

$where[] = implode(' OR ', $whereOr);

$total = $user_log->countList($where);
/** @var CUserLog[] $user_logs */
$user_logs = $user_log->loadList($where, 'date ASC', "$start, $step");

foreach ($user_logs as $_log) {
  $_log->loadTargetObject();
  if ($_log->_ref_object->_id) {
    $old_values = $_log->getOldValues();
    foreach ($old_values as $_field => $_value) {
      if (in_array($_field, $converted_constants)) {
        $_log->_ref_object->$_field = $_value;
      }
    }
  }
}

CValue::setSession('start_restore_constants', $start + $step);

$smarty = new CSmartyDP();
$smarty->assign('total', $total);
$smarty->assign('current', $start + $step);
$smarty->display('inc_status_restore_constants.tpl');