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

$product_id = CValue::getOrSession('product_id', null);

$product     = new CProduct();
$category_id = 0;
if ($product->load($product_id)) {
  $product->loadRefsFwd();
  $category_id = $product->_ref_category->_id;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign('selected_product', $product->_id);
$smarty->assign('selected_category', $category_id);

$smarty->display('product_selector.tpl');

