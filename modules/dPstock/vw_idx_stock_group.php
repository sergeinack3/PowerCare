<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Stock\CProductCategory;

CCanDo::checkEdit();

$stock_id    = CView::get("stock_group_id", "ref class|CProductStockGroup", true);
$category_id = CView::get("category_id", "ref class|CProductCategory");
$product_id  = CView::get("product_id", "ref class|CProduct");
$letter      = CView::get("letter", "str default|%");

CView::checkin();

// Loads the required Category and the complete list
$category        = new CProductCategory();
$list_categories = $category->loadList(null, 'name');

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("stock_id", $stock_id);
$smarty->assign("category_id", $category_id);
$smarty->assign("product_id", $product_id);
$smarty->assign("list_categories", $list_categories);
$smarty->assign("letter", $letter);

$smarty->display("vw_idx_stock_group");

