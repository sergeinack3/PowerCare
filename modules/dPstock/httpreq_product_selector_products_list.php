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
use Ox\Mediboard\Stock\CProductCategory;

CCanDo::checkRead();

$category_id      = CValue::get('category_id');
$keywords         = CValue::get('keywords');
$selected_product = CValue::get('selected_product');

$product  = new CProduct();
$category = new CProductCategory();
$total    = null;
$count    = null;
$where_or = array();
$order    = 'name, code';

//FIXME: changer en seek
if ($keywords) {
  foreach ($product->getSeekables() as $field => $spec) {
    $where_or[] = "`$field` LIKE '%$keywords%'";
  }
  $where   = array();
  $where[] = implode(' OR ', $where_or);
  $where[] = "cancelled IS NULL OR cancelled = '0'";

  $list_products = $product->loadList($where, $order, 20);
  $total         = $product->countList($where);
}
else {
  if ($category_id == 0) {
    $list_products = $product->loadList(null, $order);
  }
  else {
    if ($category_id == -1) {
      $list_products = array();
    }
    else {
      $category->load($category_id);
      $list_products = $category->loadBackRefs("products", $order);
      $total         = count($list_products);
    }
  }
}

$count = count($list_products);
if ($total == $count) {
  $total = null;
}

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign('list_products', $list_products);
$smarty->assign('selected_product', $selected_product);
$smarty->assign('count', $count);
$smarty->assign('total', $total);

$smarty->display('inc_product_selector_products_list.tpl');
