<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Stock\CProductStockGroup;
use Ox\Mediboard\Stock\CProductStockService;

CCanDo::checkEdit();

$stock_id    = CView::get("stock_service_id", "ref class|CProductStockService", true);
$category_id = CView::get("category_id", "ref class|CProductCategory");
$object_id   = CView::get("object_id", "ref class|CService");
$location_id = CView::get("location_id", "ref class|CProductStockLocation");
$keywords    = CView::get("keywords", "str");
$start       = CView::get("start", "num default|0");

CView::setSession("category_id", $category_id);

CView::checkin();

$where = array(
  "service.group_id" => "= '" . CProductStockGroup::getHostGroup() . "'",
);

if ($object_id) {
  $where['product_stock_service.object_id']    = " = '$object_id'";
  $where['product_stock_service.object_class'] = " = 'CService'"; // XXX
}
if ($category_id) {
  $where['product.category_id'] = " = '$category_id'";
}
if ($location_id) {
  $where["product_stock_service.location_id"] = "= '$location_id'";
}
if ($keywords) {
  $where[] = "product.code LIKE '%$keywords%' OR 
              product.name LIKE '%$keywords%' OR 
              product.description LIKE '%$keywords%'";
}

$leftjoin = array(
  "product" => "product.product_id = product_stock_service.product_id", // product to stock
  "service" => "service.service_id = product_stock_service.object_id",
);

$stock             = new CProductStockService();
$list_stocks_count = $stock->countList($where, null, $leftjoin);

$pagination_size = CAppUI::gconf("dPstock CProductStockService pagination_size");
$list_stocks     = $stock->loadList($where, 'product.name ASC', intval($start) . ",$pagination_size", null, $leftjoin);

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign("stock", $stock);
$smarty->assign("stock_id", $stock_id);
$smarty->assign("list_stocks", $list_stocks);
$smarty->assign("list_stocks_count", $list_stocks_count);
$smarty->assign("start", $start);

$smarty->display("inc_stocks_list");
