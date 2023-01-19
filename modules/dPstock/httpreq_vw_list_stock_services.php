<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

global $g;

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Stock\CProduct;
use Ox\Mediboard\Stock\CProductStockGroup;
use Ox\Mediboard\Stock\CProductStockService;

CCanDo::checkEdit();

$product_id = CValue::get('product_id');

$product = new CProduct;
$product->load($product_id);

$list_services = CProductStockGroup::getServicesList();

foreach ($list_services as $_service) {
  $stock_service = CProductStockService::getFromProduct($product, $_service);
  if (!$stock_service->_id) {
    $stock_service->quantity            = $product->quantity;
    $stock_service->order_threshold_min = $product->quantity;
  }
  $_service->_ref_stock = $stock_service;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign('list_services', $list_services);

$smarty->display('inc_list_stock_services.tpl');
