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
use Ox\Core\CView;
use Ox\Mediboard\Stock\CProductOrder;

CCanDo::checkRead();

$type     = CValue::post("type", "pending");
$keywords = CValue::post("keywords");

CView::enableSlave();

$order       = new CProductOrder();
$orders_list = $order->search($type, $keywords, 30);

foreach ($orders_list as $_order) {
  $_order->countBackRefs("order_items");
  $_order->updateCounts();
}

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign("orders", $orders_list);
$smarty->display("inc_orders_autocomplete.tpl");
