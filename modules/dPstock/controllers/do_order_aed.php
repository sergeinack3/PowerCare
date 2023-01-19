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
use Ox\Mediboard\Stock\CProductOrder;
use Ox\Mediboard\Stock\CProductStockGroup;

$do = new CDoObjectAddEdit('CProductOrder');

// New order
if (CValue::post('order_id') == 0) {
  $order               = new CProductOrder();
  $order->group_id     = CProductStockGroup::getHostGroup();
  $order->societe_id   = CValue::post('societe_id');
  $order->order_number = CValue::post('order_number');
  $order->locked       = 0;
  $order->cancelled    = 0;
  if ($msg = $order->store()) {
    CAppUI::setMsg($msg);
  }
  else {
    if (CValue::post('_autofill') == 1) {
      $order->autofill();
    }
    CAppUI::setMsg($do->createMsg);
    CAppUI::redirect('m=dPstock&a=vw_aed_order&dialog=1&order_id=' . $order->order_id);
  }
}

$do->doIt();

