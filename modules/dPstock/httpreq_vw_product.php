<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Dispensation\CProductDelivery;
use Ox\Mediboard\Stock\CProduct;
use Ox\Mediboard\Stock\CProductCategory;
use Ox\Mediboard\Stock\CProductEndowmentItem;

CCanDo::checkEdit();

$product_id = CValue::getOrSession('product_id');
$only_edit  = CValue::get('only_edit', 0);

// Loads the required Product and its References
$product = new CProduct();
if ($product->load($product_id)) {
  $product->loadRefsBack();

  $endowment_item = new CProductEndowmentItem();
  $ljoin          = array(
    'product_endowment' => "product_endowment.endowment_id = product_endowment_item.endowment_id",
  );
  foreach ($product->_ref_stocks_service as $_stock) {
    $where                        = array(
      "product_endowment.service_id"      => "= '$_stock->object_id'",
      "product_endowment_item.product_id" => "= '$product->_id'",
      "product_endowment.actif"           => "= '1'",
    );
    $_stock->_ref_endowment_items = $endowment_item->loadList($where, null, null, null, $ljoin);
  }

  foreach ($product->_ref_references as $_reference) {
    $_reference->loadRefProduct();
    $_reference->loadRefSociete();
  }

  $product->loadRefStock();

  if (CModule::getActive("dispensation")) {
    $where = array(
      //"date_delivery" => "IS NULL OR date_delivery = ''",
      "stock_class" => " = 'CProductStockGroup'",
      "stock_id"    => " = '{$product->_ref_stock_group->stock_id}'",
    );

    $delivery                 = new CProductDelivery;
    $product->_ref_deliveries = $delivery->loadList($where, "date_dispensation DESC, date_delivery DESC", 50);

    foreach ($product->_ref_deliveries as $_delivery) {
      $_delivery->loadRefsBack();
    }
  }

  $product->loadView();
}

// Loads the required Category the complete list
$category        = new CProductCategory();
$list_categories = $category->loadList(null, 'name');

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign('product', $product);
$smarty->assign('list_categories', $list_categories);
$smarty->assign('only_edit', $only_edit);

if ($only_edit) {
  $smarty->display('inc_form_product.tpl');
}
else {
  $smarty->display('inc_edit_product.tpl');
}
