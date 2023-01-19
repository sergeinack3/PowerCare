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
use Ox\Mediboard\Stock\CProduct;

CCanDo::checkEdit();

$product_id = CValue::getOrSession('product_id');

// Loads the required Product and its References
$product = new CProduct();
if ($product->load($product_id)) {
  $product->loadRefs();

  foreach ($product->_ref_references as $_item) {
    $_item->loadRefs();
  }

  foreach ($product->_ref_stocks_group as $_item) {
    $_item->loadRefs();
  }

  foreach ($product->_ref_stocks_service as $_item) {
    $_item->loadRefs();
  }
}

$product->loadRefStock();

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign('product', $product);
$smarty->display('vw_product_overview.tpl');
