<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\SalleOp\CDailyCheckList;

CCanDo::checkRead();

$operation_id = CView::get("operation_id", 'ref class|COperation');

CView::checkin();

$operation = new COperation;
$operation->load($operation_id);

CAccessMedicalData::logAccess($operation);

/** @var CDailyCheckList[] $check_lists */
$check_lists = $operation->loadBackRefs("check_lists", "date");
foreach ($check_lists as $_check_list_id => $_check_list) {
  // Remove check lists not signed
  if (!$_check_list->validator_id) {
    unset($operation->_back["check_lists"][$_check_list_id]);
    unset($check_lists[$_check_list_id]);
    continue;
  }

  $_check_list->loadItemTypes();
  $_check_list->loadRefListType();
  $_check_list->loadBackRefs('items', "daily_check_item_id");
  foreach ($_check_list->_back['items'] as $_item) {
    $_item->loadRefsFwd();
  }
}

$order_date = CMbArray::pluck($operation->_back["check_lists"], "date_validate");
array_multisort($order_date, SORT_ASC, $operation->_back["check_lists"]);

$operation->loadRefsFwd();
$operation->loadRefSejour();
$operation->_ref_sejour->loadRefCurrAffectation()->updateView();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("operation" , $operation);
$smarty->assign("patient"   , $operation->_ref_patient);
$smarty->assign("sejour"    , $operation->_ref_sejour);
$smarty->display("print_check_list_operation.tpl");
