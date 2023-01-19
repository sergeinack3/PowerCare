<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Stock\CProductOrder;

CCanDo::checkRead();
$type        = CView::get("type", "str");
$keywords    = CView::get("keywords", "str");
$category_id = CView::get("category_id", "ref class|CProductCategory");
$invoiced    = CView::get("invoiced", "bool");
$start       = CView::get("start", "str");
CView::checkin();

$page = CValue::read($start, $type, 0);

$where = array();

if ($category_id) {
  $where["product.category_id"] = "= '$category_id'";
}

if (($type == "received") && !$invoiced) {
  $where["bill_number"] = "IS NULL";
}

// @todo faire de la pagination
$order  = new CProductOrder();
$orders = $order->search($type, $keywords, "$page, 25", $where);

$count = $order->_search_count;

$order_items = CStoredObject::massLoadBackRefs($orders, "order_items");
$receptions  = CStoredObject::massLoadBackRefs($order_items, "receptions");
CStoredObject::massLoadFwdRef($receptions, "reception_id");

foreach ($orders as $_order) {
  //$_order->updateCounts();
  $_order->countRenewedItems();
  if ($_order->object_id) {
    $_order->loadTargetObject();
    $_order->_ref_object->loadRefsFwd();
  }

  foreach ($_order->_ref_order_items as $_item) {
    $_item->loadRefsReceptions();
    foreach ($_item->_ref_receptions as $_reception) {
      $_reception->loadRefReception();
    }
  }
}

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign('orders'  , $orders);
$smarty->assign('count'   , $count);
$smarty->assign('type'    , $type);
$smarty->assign('page'    , $page);
$smarty->assign('invoiced', $invoiced);
$smarty->display('inc_orders_list');
