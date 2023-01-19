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
use Ox\Mediboard\Stock\CProductOrder;

CCanDo::checkRead();

$order_id = CValue::get('order_id');

// Loads the expected Order
$order = new CProductOrder();
$order->load($order_id);
$order->loadRefsBack();
$order->loadRefsFwd();
$order->updateCounts();
$order->loadView();

if ($order->date_ordered) {
  foreach ($order->_ref_order_items as $_id => $_item) {
    if (!$_item->renewal) {
      unset($order->_ref_order_items[$_id]);
    }
  }
}

foreach ($order->_ref_order_items as $_item) {
  $_item->loadRefsReceptions();
  foreach ($_item->_ref_receptions as $_reception) {
    $_reception->loadRefReception();
  }
}

if ($order->_ref_object) {
  $order->_ref_object->loadRefsFwd();
}

$order->loadView();

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign('order', $order);

if (!$order->date_ordered) {
  $smarty->display('inc_order.tpl');
}
else {
  $smarty->display('inc_order_to_receive.tpl');
}
