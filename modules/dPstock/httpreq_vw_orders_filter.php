<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

CCanDo::checkRead();

$invoiced = CValue::get('invoiced');

$date_min = CMbDT::transform("-1 MONTH", null, "%Y-%m-01");
$date_max = CMbDT::date("+1 MONTH -1 DAY", $date_min);

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign("invoiced", $invoiced);

$smarty->display("inc_orders_filter.tpl");
