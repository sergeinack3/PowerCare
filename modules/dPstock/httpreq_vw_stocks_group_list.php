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

CCanDo::checkEdit();

$category_id         = CView::get("category_id", "ref class|CProductCategory");
$location_id         = CView::get("location_id", "ref class|CProductStockLocation");
$stock_id            = CView::get("stock_id", "ref class|CProductStock");
$keywords            = CView::get("keywords", "str");
$start               = CView::get("start", "num default|0");
$letter              = CView::get("letter", "str default|%");
$only_ordered_stocks = CView::get("only_ordered_stocks", "bool");

CView::setSession("category_id", $category_id);

CView::checkin();

$where = array(
  "product_stock_group.group_id" => "= '" . CProductStockGroup::getHostGroup() . "'",
  "product.name"                 => ($letter === "#" ? "RLIKE '^[^A-Z]'" : "LIKE '$letter%'"),
);

if ($category_id) {
  $where['product.category_id'] = " = '$category_id'";
}

if ($location_id) {
  $where["product_stock_group.location_id"] = "= '$location_id'";
}

if ($keywords) {
  $where[] = "product.code LIKE '%$keywords%' OR 
              product.name LIKE '%$keywords%' OR 
              product.description LIKE '%$keywords%'";
}

$leftjoin = array(
  'product' => 'product.product_id = product_stock_group.product_id', // product to stock
);

if ($only_ordered_stocks) {
  $where['product_order.cancelled']    = '= 0'; // order not cancelled
  $where['product_order.deleted']      = '= 0'; // order not deleted
  $where['product_order.date_ordered'] = 'IS NOT NULL'; // order not deleted
  $where['product_order_item.renewal'] = '= 1'; // renewal line

  $leftjoin['product_reference']  = 'product_reference.product_id = product_stock_group.product_id'; // stock to reference
  $leftjoin['product_order_item'] = 'product_order_item.reference_id = product_reference.reference_id'; // reference to order item
  $leftjoin['product_order']      = 'product_order.order_id = product_order_item.order_id'; // order item to order

  $where[] = 'product_order_item.order_item_id NOT IN (
    SELECT product_order_item.order_item_id FROM product_order_item
    LEFT JOIN product_order_item_reception ON product_order_item_reception.order_item_id = product_order_item.order_item_id
    LEFT JOIN product_order ON product_order.order_id = product_order_item.order_id
    WHERE product_order.deleted = 0 AND product_order.cancelled = 0
    HAVING SUM(product_order_item_reception.quantity) < product_order_item.quantity
  )';
}

$pagination_size = CAppUI::gconf("dPstock CProductStockGroup pagination_size");

$stock       = new CProductStockGroup();
$list_stocks = $stock->loadList($where, 'product.name ASC', intval($start) . ",$pagination_size", "product_stock_group.stock_id", $leftjoin);

/** @var CProductStockGroup $_stock */
foreach ($list_stocks as $_stock) {
  $_stock->_ref_product->getPendingOrderItems(false);
}

if (!$only_ordered_stocks) {
  $list_stocks_count = $stock->countList($where, null, $leftjoin);
}
else {
  $list_stocks_count = count($stock->loadList($where, null, null, "product_stock_group.stock_id", $leftjoin));
}

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign("stock", $stock);
$smarty->assign("stock_id", $stock_id);
$smarty->assign("list_stocks", $list_stocks);
$smarty->assign("list_stocks_count", $list_stocks_count);
$smarty->assign("start", $start);

$smarty->display("inc_stocks_list");
