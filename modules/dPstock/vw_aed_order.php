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
use Ox\Mediboard\Stock\CProductCategory;
use Ox\Mediboard\Stock\CProductOrder;
use Ox\Mediboard\Stock\CSociete;

CCanDo::checkEdit();

$category_id = CValue::getOrSession('category_id');
$societe_id  = CValue::getOrSession('societe_id');
$letter      = CValue::getOrSession('letter');
$_autofill   = CValue::get('_autofill');

// Categories list
$category        = new CProductCategory();
$list_categories = $category->loadList(null, 'name');

// Suppliers list
$list_societes = CSociete::getSuppliers(false);

$order       = new CProductOrder;
$list_orders = $order->search("waiting", null, 30);

foreach ($list_orders as $_order) {
  $_order->countBackRefs("order_items");
  $_order->loadRefsOrderItems();
}

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign('_autofill', $_autofill);

$smarty->assign('list_categories', $list_categories);
$smarty->assign('category_id', $category_id);

$smarty->assign('list_societes', $list_societes);
$smarty->assign('societe_id', $societe_id);
$smarty->assign('letter', $letter);

$smarty->assign('list_orders', $list_orders);

$smarty->display('vw_aed_order.tpl');
