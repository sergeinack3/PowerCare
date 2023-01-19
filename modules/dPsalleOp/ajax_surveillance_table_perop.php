<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\MonitoringPatient\SupervisionGraph;
use Ox\Mediboard\MonitoringPatient\CSupervisionTable;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();
$operation_id = CView::get('operation_id', 'ref class|COperation');
$type         = CView::get('type', 'str default|perop');
$table_id     = CView::get('table_id', 'ref class|CSupervisionTable');
CView::checkin();

$operation = new COperation();
$operation->load($operation_id);

CAccessMedicalData::logAccess($operation);

switch ($type) {
  case 'sspi':
    $pack = $operation->loadRefGraphPackSSPI();
    break;
  case 'preop':
    $pack = $operation->loadRefGraphPackPreop();
    break;
  case 'perop':
  default:
    $pack = $operation->loadRefGraphPack();
}

[$results, $times] = SupervisionGraph::getResultsFor($operation);

[$time_min, $time_max, $time_debut_op_iso, $time_fin_op_iso] = SupervisionGraph::getLimitTimes($operation, $type);

$table = new CSupervisionTable();
$table->load($table_id);
$table->build($times, $results, $time_min, $time_max);

foreach ($table->_ref_rows as $key_row => $_row) {
  if (!$_row->active) {
    unset($table->_ref_rows[$key_row]);
  }
}

$smarty = new CSmartyDP('modules/dPsalleOp');
$smarty->assign('table', $table);
$smarty->display('inc_surveillance_perop_table');
