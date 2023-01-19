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

CCanDo::checkEdit();

$category_id = CValue::getOrSession('category_id');

// Loads the expected Category
$category = new CProductCategory();
$category->load($category_id);

// Categories list
$list_categories = $category->loadList();
foreach ($list_categories as $_cat) {
  $_cat->countBackRefs("products");
}

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign('category', $category);
$smarty->assign('list_categories', $list_categories);

$smarty->display('vw_idx_category.tpl');

