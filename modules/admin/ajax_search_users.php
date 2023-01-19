<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;

CCanDo::checkEdit();
// init
$step = 30;

//filters
$page          = intval(CView::get('page', 'num default|0', true));
$filter        = CView::get("filter", "str", true);
$user_type     = CView::get("user_type", "str", true);
$template      = CView::get("template", "str", true);
$order_way     = CView::get("order_way", "enum list|ASC|DESC default|ASC", true);
$order_col     = CView::get(
    "order_col",
    "enum list|user_username|user_last_name|user_type|_user_last_login default|user_username",
    true
);
$inactif       = CView::get("inactif", "str");
$locked        = CView::get("locked", "str");
$user_loggable = CView::get("user_loggable", "str");

// Récuperation de l'utilisateur sélectionné
$user_id = CView::get("user_id", "str", true);
$user    = $user_id == "0" ? new CUser() : CUser::get($user_id);
CView::checkin();

CView::enforceSlave(false);

//ljoin
$ljoin                    = [];
$ljoin["users_mediboard"] = "users.user_id = users_mediboard.user_id";

// Where clause
$where = null;
if ($user_type) {
    $where["users.user_type"] = "= '$user_type'";
}
if ($template !== "") {
    $where["users.template"] = "= '$template'";
}

$ds = CSQLDataSource::get('std');
if ($filter) {
    $where[] = "(" .
        "user_username " . $ds->prepareLike("%$filter%") . " OR " .
        "user_first_name " . $ds->prepareLike("%$filter%") . "OR " .
        "user_last_name " . $ds->prepareLike("%$filter%") . ")";
}

// Order
$order = null;
if ($order_col == "user_username") {
    $order = "users.user_username $order_way, users.user_last_name $order_way, users.user_first_name $order_way";
}
if ($order_col == "user_last_name") {
    $order = "users.user_last_name $order_way, users.user_first_name $order_way, users.user_username $order_way";
}
if ($order_col == "user_type") {
    $order = "users.user_type $order_way, users.user_last_name ASC, users.user_first_name ASC";
}
if ($order_col == "_user_last_login") {
    $order_way == "ASC" ? $order_way_inv = "DESC" : $order_way_inv = "ASC";
    $ljoin[] =
        "(SELECT user_authentication.user_id, MAX(user_authentication.datetime_login) AS last_login 
      FROM user_authentication GROUP BY user_authentication.user_id) AS T 
      ON users.user_id = T.user_id";
    $order   = "T.last_login $order_way_inv";
}

if ($inactif == "1") {
    $where[] = "users_mediboard.actif = '0'";
}
if ($inactif == "0") {
    $where[] = "users_mediboard.actif = '1'";
}

if ($locked == "1" || $locked == "0") {
    $where = array_merge($where, CUser::getIsLockedQuery((bool)$locked));
}

if ($user_loggable) {
    if ($user_loggable == "robot") {
        $where["users.is_robot"] = "= '1'";
    } elseif ($user_loggable == "human") {
        $where["users.is_robot"] = "= '0'";
    }
}

$total_users = $user->countList($where, null, $ljoin);
$users       = $user->loadList($where, $order, "$page, $step", null, $ljoin);

foreach ($users as $_user) {
    $_user->getLastLogin();
    $_user->loadRefMediuser();
    $_user->_ref_mediuser->loadRefFunction();
}

CStoredObject::massCountBackRefs($users, "profiled_users");
//CStoredObject::massCountBackRefs($users, "authentications");

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("template", $template);
$smarty->assign("user_type", $user_type);
$smarty->assign("utypes", CUser::$types);
$smarty->assign("users", $users);
$smarty->assign("user", $user);
$smarty->assign("page", $page);
$smarty->assign("order_col", $order_col);
$smarty->assign("order_way", $order_way);
$smarty->assign("step", $step);
$smarty->assign("total_users", $total_users);
$smarty->assign("specs", $user->getProps());

$smarty->display("inc_list_users");
