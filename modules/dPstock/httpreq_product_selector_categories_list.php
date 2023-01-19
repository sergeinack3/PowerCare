<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Stock\CProductCategory;

CCanDo::checkRead();

$keywords          = CValue::get('keywords');
$category_id       = CValue::get('category_id');
$selected_category = CValue::get('selected_category');

// Loads the required Category and the complete list
$category = new CProductCategory();
$total    = null;
$count    = null;

if ($keywords) {
  $where           = array();
  $where['name']   = "LIKE '%$keywords%'";
  $list_categories = $category->loadList($where, 'name', 20);
  $total           = $category->countList($where);
}
else {
  $list_categories = $category->loadList(null, 'name');
  $total           = count($list_categories);
}
$count = count($list_categories);
if ($total == $count) {
  $total = null;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign('list_categories', $list_categories);
$smarty->assign('category_id', $category_id);
$smarty->assign('selected_category', $selected_category);
$smarty->assign('count', $count);
$smarty->assign('total', $total);

$smarty->display('inc_product_selector_categories_list.tpl');

