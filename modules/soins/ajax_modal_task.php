<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Soins\CSejourTask;

CCanDo::checkEdit();

$task_id                      = CView::get("task_id", "ref class|CSejourTask");
$sejour_id                    = CView::get("sejour_id", "ref class|CSejour");
$prescription_line_element_id = CView::get("prescription_line_element_id", "ref class|CPrescriptionLineElement");

CView::checkin();

CAccessMedicalData::logAccess("CSejour-$sejour_id");

$task = new CSejourTask();
$task->load($task_id);

$task_element = false;
if ($prescription_line_element_id) {
  $task->prescription_line_element_id = $prescription_line_element_id;
  $task->loadMatchingObject();
  $task_element = true;
}

if (!$task->_id) {
  $task->author_id = CUser::get()->_id;
  $task->date      = CMbDT::dateTime();
}

$task->loadRefConsult()->loadRefsFwd();

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign("sejour_id", $sejour_id);
$smarty->assign("task", $task);
$smarty->assign("task_element", $task_element);

$smarty->display("inc_modal_task");
