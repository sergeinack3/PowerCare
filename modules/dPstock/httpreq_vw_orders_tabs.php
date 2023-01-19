<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Stock\CProductOrder;

CCanDo::checkEdit();

$order       = new CProductOrder;
$list_orders = $order->search("waiting", null, 30);

foreach ($list_orders as $_order) {
  $_order->countBackRefs("order_items");
  $_order->loadRefsOrderItems();
  $_order->updateCounts();
}

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign('list_orders', $list_orders);
$smarty->display('inc_orders_tabs.tpl');
