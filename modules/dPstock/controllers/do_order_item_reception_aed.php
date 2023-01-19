<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CValue;
use Ox\Mediboard\Stock\CProduct;
use Ox\Mediboard\Stock\CProductOrderItem;
use Ox\Mediboard\Stock\CProductReference;
use Ox\Mediboard\Stock\CSociete;

$do = new CDoObjectAddEdit('CProductOrderItemReception');

$reference_id = CValue::post("_reference_id");
$quantity     = CValue::post("quantity");

if ($reference_id) {
  // If it is a societe id
  if (!is_numeric($reference_id)) {
    list($societe_id, $product_id) = explode("-", $reference_id);

    $societe = new CSociete;
    $societe->load($societe_id);

    $product = new CProduct;
    $product->load($product_id);

    $reference             = new CProductReference;
    $reference->product_id = $product->_id;
    $reference->societe_id = $societe->_id;
    $reference->quantity   = 1;
    $reference->price      = 0;
    $reference->store();
  }
  else {
    // If it is a reference id
    $reference = new CProductReference;
    $reference->load($reference_id);
  }

  if (!$reference->_id) {
    CAppUI::setMsg("Impossible de créer l'article, la réference n'existe pas", UI_MSG_ERROR);
  }

  $order_item               = new CProductOrderItem;
  $order_item->reference_id = $reference->_id;
  $order_item->quantity     = $quantity;
  $order_item->unit_price   = $reference->price;
  if ($msg = $order_item->store()) {
    CAppUI::setMsg($msg);
  }

  $_POST["order_item_id"] = $order_item->_id;
}

$do->doIt();

