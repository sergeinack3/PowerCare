<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CLongRequestLog;

CCanDo::checkAdmin();

$log = new CLongRequestLog();

$log->_date_min   = CValue::get("_date_min", CMbDT::date("-1 MONTH") . ' 00:00:00');
$log->_date_max   = CValue::get("_date_max");
$log->user_id     = CValue::get("user_id");
$log->duration    = CValue::get("duration");
$duration_operand = CValue::get("duration_operand");

$user           = new CUser();
$user->template = "0";
$order          = "user_last_name, user_first_name";
$user_list      = $user->loadMatchingList($order);

$modules = CModule::getInstalled();
uasort($modules, function ($a, $b) {
  return strcmp(CMbString::removeAccents($a->_view), CMbString::removeAccents($b->_view));
});

$smarty = new CSmartyDP();
$smarty->assign("user_list", $user_list);
$smarty->assign("modules", $modules);
$smarty->assign("log", $log);
$smarty->assign("duration_operand", $duration_operand);
$smarty->display("vw_purge_long_request_logs.tpl");
