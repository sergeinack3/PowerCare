<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\System\CUserAgent;

$start         = CValue::get("start", 0);
$date_min      = CValue::getOrSession("date_min", CMbDT::dateTime("-2 MONTH"));
$date_max      = CValue::getOrSession("date_max");
$user_id       = CValue::getOrSession("user_id");
$user_agent_id = CValue::get("user_agent_id");

$ua = new CUserAgent();

if ($user_agent_id) {
  $ua->load($user_agent_id);
}

$smarty = new CSmartyDP();
$smarty->assign("start", $start);
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign("user_id", $user_id);
$smarty->assign("user_agent_id", $user_agent_id);
$smarty->assign("ua", $ua);
$smarty->display("vw_user_authentications.tpl");
