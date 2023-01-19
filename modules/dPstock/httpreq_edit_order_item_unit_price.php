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

$order_item_id = CValue::get('order_item_id');

$order_item = new CProductOrderItem;
$order_item->load($order_item_id);

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign('order_item', $order_item);

$smarty->display('inc_edit_order_item_unit_price.tpl');
