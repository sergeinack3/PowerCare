<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Mediboard\Stock\CProductOrder;
use Ox\Mediboard\Stock\CProductReference;
use Ox\Mediboard\Stock\CProductStockGroup;

$do = new CDoObjectAddEdit('CProductOrderItem');

if (CValue::post("_create_order")) {
  $reference_id            = CValue::post("reference_id");
  $reference               = new CProductReference;
  $reference->reference_id = $reference_id;

  if (!$reference_id || !$reference->loadMatchingObject()) {
    CAppUI::setMsg("Impossible de créer l'article, la réference n'existe pas", UI_MSG_ERROR);
  }

  $where = array(
    "product_order.societe_id"   => "= '$reference->societe_id'",
    "product_order.object_class" => "IS NULL",
  );

  $septic   = null;
  $comments = null;

  // If a context is provided
  if ($context_guid = CValue::post("_context_guid")) {
    list($object_class, $object_id) = explode("-", $context_guid);
    $where["product_order.object_class"] = "= '$object_class'";
    $where["product_order.object_id"]    = "= '$object_id'";
    if ($septic = CValue::post("septic")) {
      unset($_POST["context_guid"]);
      $where["product_order_item.septic"] = "= '$septic'";
    }
    else {
      $where["product_order_item.septic"] = "= '0'";
    }
  }
  elseif ($comments = CValue::read($_POST, "_comments")) {
    $where["product_order.comments"] = "LIKE '$comments%'";
  }

  $where["product_order.group_id"] = "= '" . CProductStockGroup::getHostGroup() . "'";

  $order  = new CProductOrder;
  $orders = $order->search("waiting", null, 1, $where);

  if (($context_guid || $comments == CProductOrder::$_return_form_label) && count($orders) == 0) {
    $orders = $order->search("locked", null, 1, $where);
  }

  // If no order found
  if (count($orders) == 0) {
    if ($context_guid) {
      $context = CMbObject::loadFromGuid($context_guid);
      $order->setObject($context);
      $order->locked = 1;
    }

    $comments = CValue::read($_POST, "_comments");

    $order->societe_id = $reference->societe_id;
    $order->group_id   = CProductStockGroup::getHostGroup();
    $order->comments   = $comments;

    if (strpos(CProductOrder::$_return_form_label, $comments) === 0) {
      $order->locked = 1;
    }

    $product            = $reference->loadRefProduct();
    $count_dmi          = $product->countBackRefs("dmis");
    $order->_context_bl = $count_dmi > 0;
    $order->_septic     = $septic;

    if ($msg = $order->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
    }

    $order->order_number = $order->getUniqueNumber();

    if ($msg = $order->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
    }
  }
  else {
    $order = reset($orders);
  }

  if ($order->_id && !$order->bill_number) {
    $order->bill_number = CValue::post("_bill_number");
    $order->store();
  }

  $_POST["order_id"] = $order->_id;
}

$do->doIt();
