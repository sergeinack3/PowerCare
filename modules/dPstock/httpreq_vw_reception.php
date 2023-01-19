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
use Ox\Mediboard\Stock\CProductReception;

CCanDo::checkRead();

$reception_id = CValue::get('reception_id');

// Loads the expected Order
$reception = new CProductReception();
$reception->load($reception_id);
$reception->loadRefsBack();

foreach ($reception->_ref_reception_items as $_reception) {
  $_reception->loadRefOrderItem()->loadReference();
}

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign('reception', $reception);
$smarty->display('inc_reception.tpl');
