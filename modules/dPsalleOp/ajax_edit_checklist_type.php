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
use Ox\Mediboard\SalleOp\CDailyCheckListType;

CCanDo::checkAdmin();

global $g;
$check_list_type_id  = CView::get('check_list_type_id', 'ref class|CDailyCheckListType',true);
$check_list_group_id = CView::get('check_list_group_id', 'ref class|CDailyCheckListGroup');
$callback            = CView::get('callback', 'str');
$modal               = CView::get('modal', 'bool default|0');

CView::checkin();

$list_type = new CDailyCheckListType();
$list_type->load($check_list_type_id);
$list_type->loadRefsNotes();
$list_type->loadRefsCategories();

$list_type->makeLinksArray();

list($targets, $by_type) = CDailyCheckListType::getListTypesTree();

if ($list_type->type != "intervention" || !$list_type->check_list_group_id) {
  unset($list_type->_specs["type_validateur"]->_list[10]);
}

foreach ($targets as $_targets) {
  foreach ($_targets as $_target) {
    $_target->loadRefsFwd();
  }
}

if ($check_list_group_id) {
  $list_type->type = 'intervention';
  $list_type->check_list_group_id = $check_list_group_id;
  $list_type->group_id = $g;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("list_type", $list_type);
$smarty->assign("targets"  , $targets);
$smarty->assign("callback" , $callback);
$smarty->assign("modal"    , $modal);
$smarty->display("inc_edit_check_list_type.tpl");