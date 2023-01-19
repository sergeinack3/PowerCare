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
use Ox\Mediboard\Stock\CProductStockService;

CCanDo::checkEdit();

$service_id = CValue::get('service_id');
$keywords   = CValue::get('keywords');
$limit      = CValue::get('limit');

// Service's stocks
$where = array();
if ($service_id) {
  $where['product_stock_service.object_id']    = " = '$service_id'";
  $where['product_stock_service.object_class'] = " = 'CService'"; // XXX
}
if ($keywords) {
  $where[] = "product.code LIKE '%$keywords%' OR 
              product.name LIKE '%$keywords%' OR 
              product.description LIKE '%$keywords%'";
}
$orderby = 'product.name ASC';

$leftjoin            = array();
$leftjoin['product'] = 'product.product_id = product_stock_service.product_id'; // product to stock
$stock               = new CProductStockService();
$list_stocks_count   = $stock->countList($where, null, $leftjoin);
$list_stocks         = $stock->loadList($where, $orderby, $limit ? $limit : 30, null, $leftjoin);

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign('stock', $stock);
$smarty->assign('list_stocks', $list_stocks);
$smarty->assign('list_stocks_count', $list_stocks_count);

$smarty->display('inc_stocks_list.tpl');

