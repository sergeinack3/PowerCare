<?php

/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Dmi\CDM;
use Ox\Mediboard\Stock\CProductOrder;
use Ox\Mediboard\Stock\CProductOrderItem;

CCanDo::checkRead();

$order_id  = CView::get("order_id", "ref class|CProductOrder", true);
$order_ids = CView::get("order_ids", "str");

CView::checkin();

if (!$order_ids) {
    $order_ids = [$order_id];
}

// Loads the expected Orders
$order = new CProductOrder();

if (count($order_ids)) {
    $orders = $order->loadList(["order_id" => CSQLDataSource::prepareIn($order_ids)]);

    CStoredObject::massLoadFwdRef($orders, "societe_id");
    CStoredObject::massLoadFwdRef($orders, "address_id");

    foreach ($orders as $_order) {
        $_order->loadRefsFwd();
        $_order->loadRefAddress();
        $_order->updateCounts();

        if ($_order->object_class) {
            $_order->_ref_object->loadRefsFwd();
        }

        foreach ($_order->_ref_order_items as $_item) {
            if ($_item->septic) {
                $_order->_septic = true;
            }

            if ($_item->lot_id) {
                $_item->loadRefLot();
                $_order->_has_lot_numbers = true;
            }

            if ($_order->object_id) {
                $_item->_ref_dmi = CDM::getFromProduct($_item->loadReference()->loadRefProduct());
            }
        }

        if ($_order->_ref_order_items) {
            usort($_order->_ref_order_items, function (CProductOrderItem $a, CProductOrderItem $b) {
                return strcmp($a->_ref_reference->_ref_product->name, $b->_ref_reference->_ref_product->name);
            });
        }
    }
}

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign("orders", $orders);

$smarty->display("vw_order_form");
