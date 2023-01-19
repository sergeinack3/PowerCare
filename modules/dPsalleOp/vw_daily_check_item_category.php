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
use Ox\Mediboard\SalleOp\CDailyCheckItemCategory;
use Ox\Mediboard\SalleOp\CDailyCheckList;
use Ox\Mediboard\SalleOp\CDailyCheckListType;

CCanDo::checkAdmin();

$item_category_id = CView::get('item_category_id', 'ref class|CDailyCheckItemCategory');
$list_type_id     = CView::get('list_type_id', 'ref class|CDailyCheckListType');

CView::checkin();

$list_type = new CDailyCheckListType();
$list_type->load($list_type_id);

$item_category = new CDailyCheckItemCategory();
if ($item_category->load($item_category_id)) {
  $item_category->loadRefsNotes();
  $item_category->loadRefItemTypes();
}
else {
  $item_category->list_type_id = $list_type_id;
  if ($list_type->type == "ouverture_salle"|| $list_type->type == "fermeture_salle") {
    $item_category->target_class = "CSalle";
  }
  elseif ($list_type->type == "ouverture_sspi" || $list_type->type == "ouverture_preop") {
    $item_category->target_class = "CBlocOperatoire";
  }
  else {
    $item_category->target_class = "COperation";
  }
}

foreach (CDailyCheckList::$_HAS_classes as $_class) {
  unset($item_category->_specs["target_class"]->_locales[$_class]);
}

list($targets, $item_categories_by_class) = CDailyCheckItemCategory::getCategoriesTree();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("item_category", $item_category);
$smarty->assign("targets", $targets);
$smarty->display("vw_daily_check_item_category.tpl");
