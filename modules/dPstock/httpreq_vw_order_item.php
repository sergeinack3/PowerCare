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
use Ox\Mediboard\Stock\CProductOrderItem;

CCanDo::checkRead();

$item_id = CValue::get('order_item_id');

// Loads the expected Order Item
$item = new CProductOrderItem();
if ($item->load($item_id)) {
  $item->loadRefs();
  $item->_ref_reference->loadRefsFwd();
}
$item->_quantity_received = $item->quantity_received;

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign('curr_item', $item);
$smarty->assign('order', $item->_ref_order);

$smarty->display('inc_order_item.tpl');

