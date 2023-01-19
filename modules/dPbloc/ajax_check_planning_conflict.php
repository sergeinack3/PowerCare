<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();

$salle_id     = CView::get('salle_id', 'ref class|CSalle');
$operation_id = CView::get('operation_id', 'ref class|COperation');
$time         = CView::get('time', 'time');

CView::checkin();

/** @var COperation $operation */
$operation = COperation::loadFromGuid("COperation-{$operation_id}");

CAccessMedicalData::logAccess($operation);

$end = CMbDT::addTime($operation->temp_operation, $time);

$where = array(
  'salle_id'      => " = '{$salle_id}'",
  'date'          => " = '{$operation->date}'",
  'operation_id'  => " != '{$operation_id}'",
  "time_operation BETWEEN '{$time}' AND '{$end}' OR ADDTIME(time_operation, temp_operation) BETWEEN '{$time}' AND '{$end}'"
);
$operations = $operation->loadList($where, 'time_operation', null, 'operation_id');

$data = array(
  'conflicts' => count($operations)
);

if (count($operations)) {
  $data['operations'] = array();
  foreach ($operations as $operation) {
    $operation->loadRefPatient();
    $operation->loadRefChir();
    $operation->updateView();
    $data['operations'][] = $operation->_view . ' à ' . CMbDT::format($operation->time_operation, '%H:%M');
  }
}

CApp::json($data);