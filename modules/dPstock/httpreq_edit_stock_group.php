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
use Ox\Mediboard\Stock\CProduct;
use Ox\Mediboard\Stock\CProductStockGroup;
use Ox\Mediboard\Stock\CProductStockService;

CCanDo::checkEdit();

$stock_id   = CView::get("stock_group_id", "ref class|CProductStockGroup", true);
$product_id = CView::get("product_id", "ref class|CProduct", true);

CView::checkin();

// Loads the stock in function of the stock ID or the product ID
$stock = new CProductStockGroup();

// If stock_id has been provided, we load the associated product
if ($stock_id) {
  $stock->stock_id = $stock_id;
  $stock->loadMatchingObject();
  $stock->loadRefsFwd();
  $stock->_ref_product->loadRefsFwd();
}
// else, if a product_id has been provided, we load the associated stock
else {
  if ($product_id) {
    $product = new CProduct();
    $product->load($product_id);

    $stock->product_id   = $product_id;
    $stock->_ref_product = $product;
  }
  else {
    $stock->loadRefsFwd();
  }
}
$stock->updateFormFields();

$list_services = CProductStockGroup::getServicesList();

foreach ($list_services as $_service) {
  $stock_service               = new CProductStockService;
  $stock_service->object_id    = $_service->_id;
  $stock_service->object_class = $_service->_class;
  $stock_service->product_id   = $stock->product_id;
  if (!$stock_service->loadMatchingObject()) {
    $stock_service->quantity                = $stock->_ref_product->quantity;
    $stock_service->order_threshold_min     = $stock->_ref_product->quantity;
    $stock_service->order_threshold_optimum = max($stock->getOptimumQuantity(), $stock_service->quantity);
  }
  $_service->_ref_stock = $stock_service;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("stock", $stock);
$smarty->assign("list_services", $list_services);

$smarty->display("inc_edit_stock_group");

