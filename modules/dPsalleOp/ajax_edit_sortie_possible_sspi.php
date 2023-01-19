<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkEdit();

$operation_id = CView::get("operation_id", "ref class|COperation");
$ambu         = CView::get("ambu", "bool default|0");
$date         = CView::get("date", "date default|" . CMbDT::date(), true);

CView::checkin();

$modif_operation = CCanDo::edit() || $date >= CMbDT::date();

$operation = new COperation();
$operation->load($operation_id);

CAccessMedicalData::logAccess($operation);

$smarty = new CSmartyDP();

$smarty->assign("operation"      , $operation);
$smarty->assign("modif_operation", $modif_operation);
$smarty->assign("ambu"           , $ambu);

$smarty->display("inc_edit_sortie_possible_sspi.tpl");