<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\System\CUserAgent;
use Ox\Mediboard\System\CUserAuthentication;

CCanDo::checkRead();

$start         = CValue::get("start", 0);
$date_min      = CValue::getOrSession("date_min", CMbDT::dateTime("-2 MONTH"));
$date_max      = CValue::getOrSession("date_max");
$user_id       = CValue::getOrSession("user_id");
$user_agent_id = CValue::get("user_agent_id");

$auth = new CUserAuthentication();
$ua   = new CUserAgent();

$where = array(
  "datetime_login" => ">= '$date_min'"
);
if ($date_max) {
  $where[] = "datetime_login <= '$date_min'";
}
if ($user_id) {
  $where["user_id"] = "<= '$user_id'";
}
if ($user_agent_id) {
  $where["user_agent_id"] = "= '$user_agent_id'";
  $ua->load($user_agent_id);
}

$limit = ((int)$start) . ",100";

$total = $auth->countList($where);

/** @var CUserAuthentication[] $auth_list */
$auth_list = $auth->loadList($where, "datetime_login DESC", $limit);

foreach ($auth_list as $_auth) {
  $_auth->loadRefUser()->loadRefMediuser()->loadRefFunction();
}

$smarty = new CSmartyDP();
$smarty->assign("start", $start);
$smarty->assign("total", $total);
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign("user_id", $user_id);
$smarty->assign("auth_list", $auth_list);
$smarty->assign("ua", $ua);
$smarty->display("inc_vw_list_user_authentications.tpl");
