<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkEdit();
$operation_id = CView::get("operation_id", "ref class|COperation");
$just_button  = CView::get("just_button", "bool default|0");
CView::checkin();

$operation = new COperation();
$operation->load($operation_id);

CAccessMedicalData::logAccess($operation);

$operation->canDo();
$operation->countAlertsNotHandled();
$operation->loadLiaisonLibelle();

$smarty = new CSmartyDP();

$smarty->assign("operation"  , $operation);
$smarty->assign("just_button", $just_button);

$smarty->display("inc_reload_infos_interv");
