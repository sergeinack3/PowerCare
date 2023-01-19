<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\MonitoringPatient\CSupervisionTable;

CCanDo::checkAdmin();
$supervision_table_id = CView::get('supervision_table_id', 'ref class|CSupervisionTable', true);
CView::checkin();

$table = new CSupervisionTable();
$table->load($supervision_table_id);
$rows = $table->loadRefsRows();

CMbObject::massLoadFwdRef($rows, 'value_type_id');
CMbObject::massLoadFwdRef($rows, 'value_unit_id');
foreach ($rows as $row) {
  $row->loadRefValueType();
  $row->loadRefValueUnit();
}

$smarty = new CSmartyDP();
$smarty->assign("table", $table);
$smarty->assign("rows" , $rows);
$smarty->display("inc_list_supervision_table_rows");