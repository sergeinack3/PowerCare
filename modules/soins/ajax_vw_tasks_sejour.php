<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Soins\CSejourTask;

$sejour_id        = CValue::getOrSession("sejour_id");
$mode_realisation = CValue::get("mode_realisation");
$readonly         = CValue::get("readonly", 0);
$source           = CValue::get("source", 'soins');
$non_realise      = CView::get("unfinished_only", "bool default|0");

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->countTasks();
$sejour->loadRefsTasks();

foreach ($sejour->_ref_tasks as $key => $_task) {
  // Remove unfinished tasks from the list if required
  if ($non_realise && $_task->realise) {
    unset($sejour->_ref_tasks[$key]);
  }

  $_task->setDateAndAuthor();
  $_task->loadRefAuthor();
  $_task->loadRefPrescriptionLineElement();
  $_task->loadRefAuthorRealise();
}

CSejourTask::sortByDate($sejour->_ref_tasks);

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign("sejour", $sejour);
$smarty->assign("task", new CSejourTask());
$smarty->assign("readonly", $readonly);
$smarty->assign("header", "0");
$smarty->assign("mode_realisation", $mode_realisation);
$smarty->assign("source", $source);

$smarty->display("inc_vw_tasks_sejour");

