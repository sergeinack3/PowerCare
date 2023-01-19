<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\System\CLongRequestLog;
use Ox\Mediboard\System\CModuleAction;

$_datetime_start_min = CValue::post('_datetime_start_min');
$_datetime_start_max = CValue::post('_datetime_start_max');
$_datetime_end_min   = CValue::post('_datetime_end_min');
$_datetime_end_max   = CValue::post('_datetime_end_max');
$user_id             = CValue::post('user_id');
$duration            = CValue::post('duration');
$duration_operand    = CValue::post('duration_operand');
$module              = CValue::post('filter_module');
$module_action_id    = CValue::post('module_action_id');
$purge_limit         = CValue::post('purge_limit', '100');
$just_count          = CValue::post('just_count');

$purge_limit = ($purge_limit) ? $purge_limit : 100;

$ds  = CSQLDataSource::get('std');
$log = new CLongRequestLog();

$where = array();

if ($_datetime_start_min) {
  $where[] = $ds->prepare('`datetime_start` >= ?', $_datetime_start_min);
}

if ($_datetime_start_max) {
  $where[] = $ds->prepare('`datetime_start` <= ?', $_datetime_start_max);
}

if ($_datetime_end_min) {
  $where[] = $ds->prepare('`datetime_end` >= ?', $_datetime_end_min);
}

if ($_datetime_end_max) {
  $where[] = $ds->prepare('`datetime_end` <= ?', $_datetime_end_max);
}

if ($user_id) {
  $where['user_id'] = $ds->prepare('= ?', $user_id);
}

if ($duration && in_array($duration_operand, array('<', '<=', '=', '>', '>='))) {
  $where['duration'] = $ds->prepare("$duration_operand ?", $duration);
}

if ($module_action_id) {
  $where['module_action_id'] = $ds->prepare("= ?", $module_action_id);
}
elseif ($module) {
  $module_action         = new CModuleAction();
  $module_action->module = $module;

  $module_actions = $module_action->loadMatchingListEsc();
  if ($module_actions) {
    $where['module_action_id'] = $ds->prepareIn(CMbArray::pluck($module_actions, '_id'));
  }
}

$count = $log->countList($where);

$msg = '%d CLongRequestLog to be removed.';
if ($count == 1) {
  $msg = 'One CLongRequestLog to be removed.';
}
elseif (!$count) {
  $msg = 'No CLongRequestLog to be removed.';
}

CAppUI::stepAjax("CLongRequestLog-msg-$msg", UI_MSG_OK, $count);

if ($just_count || !$count) {
  CAppUI::js("\$('clean_auto').checked = false");
  CApp::rip();
}

$logs = $log->loadList($where, null, $purge_limit);

if (!$logs) {
  CAppUI::js("\$('clean_auto').checked = false");
  CAppUI::stepAjax("CLongRequestLog-msg-No CLongRequestLog to be removed.", UI_MSG_OK);
  CApp::rip();
}

$deleted_logs = 0;
foreach ($logs as $_log) {
  if ($msg = $_log->delete()) {
    CAppUI::setMsg($msg, UI_MSG_WARNING);
  }
  else {
    CAppUI::setMsg('CLongRequestLog-msg-delete', UI_MSG_OK);
    $deleted_logs++;
  }
}
CAppUI::setMsg('CLongRequestLog-msg-%d CLongRequestLog to be removed.', UI_MSG_OK, $count - $deleted_logs);

echo CAppUI::getMsg();
CApp::rip();