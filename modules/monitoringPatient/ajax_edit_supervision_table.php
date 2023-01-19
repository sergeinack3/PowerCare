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
use Ox\Mediboard\MonitoringPatient\CSupervisionTable;

CCanDo::checkAdmin();
$supervision_table_id = CView::get('supervision_table_id', 'ref class|CSupervisionTable', true);
CView::checkin();

$table = new CSupervisionTable();
$table->load($supervision_table_id);
$table->loadRefsNotes();

$smarty = new CSmartyDP();
$smarty->assign("table", $table);
$smarty->display("inc_edit_supervision_table");