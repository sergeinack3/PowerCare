<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\MonitoringPatient\CSupervisionTableRow;

CCanDo::checkAdmin();
$supervision_table_row_id = CView::get('supervision_table_row_id', 'ref class|CSupervisionTableRow');
$supervision_table_id     = CView::get('supervision_table_id', 'ref class|CSupervisionTable');
CView::checkin();

$row = new CSupervisionTableRow();
if (!$row->load($supervision_table_row_id)) {
  $row->supervision_table_id = $supervision_table_id;
}

$row->loadRefValueType();
$row->loadRefValueUnit();

$smarty = new CSmartyDP();
$smarty->assign('row', $row);
$smarty->display('inc_edit_supervision_table_row');