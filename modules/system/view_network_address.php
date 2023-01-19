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
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CUserLog;

CCanDo::checkRead();
$start  = CValue::get("start", 0);
CView::enforceSlave();

$filter = new CUserLog();
$filter->date = CValue::get("date", CMbDT::dateTime());
$filter->_date_max = CMbDT::dateTime($filter->date);
$filter->_date_min = CMbDT::dateTime("-1 week", $filter->date);
$filter->user_id      = CValue::get("user_id");
$filter->ip_address   = CValue::get("ip_address", "255.255.255.255");

$order_col = CValue::getOrSession("order_col", "date_max");
$order_way = CValue::getOrSession("order_way", "DESC");
$order_way_alt = $order_way == "ASC" ? "DESC" : "ASC";

$user = new CMediusers();
$listUsers = $user->loadUsers();

$where = array(
  "ip_address IS NOT NULL AND ip_address != ''"
);

$where[] = "date >= '$filter->_date_min'";
$where[] = "date <= '$filter->_date_max'";
$where[] = "user_id ".CSQLDataSource::prepareIn(array_keys($listUsers), $filter->user_id);

$order = "$order_col $order_way";
$group = "ip_address";

$total_list_count = $filter->countMultipleList(
  $where,
  $order,
  $group,
  null,
  array("ip_address", "MAX(date) AS date_max, GROUP_CONCAT(DISTINCT user_id SEPARATOR '|') AS user_list")
);

foreach ($total_list_count as $key => $_log) {
  if (inet_ntop($_log["ip_address"]) != (inet_ntop($_log["ip_address"] & inet_pton($filter->ip_address)))) {
    unset($total_list_count[$key]);
  }
}

$total_list = array_slice($total_list_count, $start, 30);
foreach ($total_list as &$_log) {
  $_log["ip"]         = inet_ntop($_log["ip_address"]);
  $_log["ip_explode"] = explode(".", $_log["ip"]);
  $list_users = explode("|", $_log["user_list"]);
  $_log["users"] = array();
  foreach ($list_users as $_user_id) {
    if (isset($listUsers[$_user_id])) {
      $_log["users"][$_user_id] = $listUsers[$_user_id];
    }
  }
}

$list_count = count($total_list_count);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("start"        , $start        );
$smarty->assign("filter"       , $filter       );
$smarty->assign("listUsers"    , $listUsers    );
$smarty->assign("list_count"   , $list_count   );
$smarty->assign("total_list"   , $total_list   );
$smarty->assign("order_col"    , $order_col    );
$smarty->assign("order_way"    , $order_way    );
$smarty->assign("order_way_alt", $order_way_alt);

$smarty->display("view_network_address.tpl");
