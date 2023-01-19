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

$stock_service_id = CView::get("stock_service_id", "ref class|CProductStockService", true);
$service_id       = CView::get("service_id", "ref class|CService", true);
$product_id       = CView::get("product_id", "ref class|CProduct", true);

CView::checkin();

// Loads the stock 
$stock = new CProductStockService();

// If stock_id has been provided, we load the associated product
if ($stock->load($stock_service_id)) {
  $stock->loadRefsFwd();
  $stock->_ref_product->loadRefsFwd();
}
else {
  if ($product_id) {
    $product = new CProduct();
    $product->load($product_id);

    $stock->product_id   = $product_id;
    $stock->_ref_product = $product;
    $stock->updateFormFields();
  }
  else {
    $stock->loadRefsFwd(); // pour le _ref_product
  }
}

$list_services = CProductStockGroup::getServicesList();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("stock", $stock);
$smarty->assign("service_id", $service_id);
$smarty->assign("list_services", $list_services);

$smarty->display("inc_edit_stock_service");

