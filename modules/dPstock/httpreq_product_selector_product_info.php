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

CCanDo::checkRead();

$product_id = CValue::get('product_id');

$product = new CProduct();
if ($product_id) {
  $product->load($product_id);
  $product->loadRefs();
  $product->loadRefStock();
}

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign('product', $product);
$smarty->display('inc_product_selector_product_info.tpl');
