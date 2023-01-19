<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkEdit();

$operation_id = CView::post('operation_id', 'ref class|COperation notNull');
$delay        = CView::post('delay', 'num notNull');

CView::checkin();

/** @var COperation $operation */
$operation = COperation::loadFromGuid("COperation-$operation_id");

if ($delay > 0) {
  $delay = "+{$delay}";
}

$errors = 0;
$success = 0;

$where = array(
  'operation_id'    => " != {$operation->operation_id}",
  'plageop_id'      => 'IS NULL',
  'annulee'         => " = '0'",
  'salle_id'        => " = {$operation->salle_id}",
  'date'            => " = '{$operation->date}'",
  'time_operation'  => ">= '{$operation->time_operation}'"
);

/** @var COperation[] $operations */
$operations = $operation->loadList($where, 'time_operation ASC', null, 'operation_id');
foreach ($operations as $_operation) {
  $_operation->_time_urgence = CMbDT::time("{$delay} MINUTES", $_operation->time_operation);
  if (!is_null($_operation->store())) {
    $errors++;
  }
  else {
    $success++;
  }
}

$where['time_operation'] = "< '{$operation->time_operation}'";
$where[] = "ADDTIME(time_operation, temp_operation) >= '{$operation->time_operation}'";
/** @var COperation[] $operations */
$operations = $operation->loadList($where, 'time_operation ASC', null, 'operation_id');
foreach ($operations as $_operation) {
  $_operation->_time_urgence = CMbDT::subTime($_operation->temp_operation, $operation->time_operation);
  if (!is_null($_operation->store())) {
    $errors++;
  }
  else {
    $success++;
  }
}

if ($success) {
  CAppUI::setMsg('COperation-msg-operations_delayed', UI_MSG_OK, $success);
}
if ($errors) {
  CAppUI::setMsg('COperation-error-delayed_operations', UI_MSG_ERROR, $errors);
}

echo CAppUI::getMsg();
