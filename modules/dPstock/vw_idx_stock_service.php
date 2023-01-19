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
use Ox\Mediboard\Stock\CProductStockGroup;

CCanDo::checkEdit();

$stock_service_id = CView::get("stock_service_id", "ref class|CProductStockService", true);
$category_id      = CView::get("category_id", "ref class|CProductCategory", true);
$service_id       = CView::get("service_id", "ref class|CService", true);
$product_id       = CView::get("product_id", "ref class|CProduct");

CView::checkin();

// Categories list
$list_categories = new CProductCategory();
$list_categories = $list_categories->loadList(null, 'name');

$list_services = CProductStockGroup::getServicesList();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("stock_service_id", $stock_service_id);
$smarty->assign("product_id", $product_id);

$smarty->assign("category_id", $category_id);
$smarty->assign("service_id", $service_id);

$smarty->assign("list_categories", $list_categories);
$smarty->assign("list_services", $list_services);

$smarty->display("vw_idx_stock_service");

