<?php
/**
 * @package Mediboard\Mediusers
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
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();
// init
$step  = 35;
$group = CGroups::loadCurrent();

//filters
$page          = intval(CView::get('page', 'num default|0', true));
$filter        = CView::get("filter", "str", true);
$type          = CView::get("_user_type", "str", true);
$inactif       = CView::get("inactif", "str");
$ldap_bound    = CView::get('_ldap_bound', 'str');
$user_loggable = CView::get("user_loggable", "str");
$human         = CView::get("human", "bool default|0");
$robot         = CView::get("robot", "str default|0");
$locked        = CView::get("locked", "str");
$function_id   = CView::get("function_id", "str");
$order_way     = CView::get("order_way", "str default|ASC", true);
$order_col     = CView::get("order_col", "str default|function_id", true);
$user_id       = CView::get("user_id", "str", true);
CView::checkin();

if ($ldap_bound) {
    $ldap_bound = explode('|', $ldap_bound);

    if (is_array($ldap_bound) && count($ldap_bound) == 1) {
        $ldap_bound = reset($ldap_bound);
    }
}

// search
// Liste des utilisateurs
$mediuser = new CMediusers();

$ds = CSQLDataSource::get('std');

$ljoin                        = [];
$ljoin["users"]               = "users.user_id = users_mediboard.user_id";
$ljoin["functions_mediboard"] = "functions_mediboard.function_id = users_mediboard.function_id";

$where = [];

if ($group->_id) {
    $where["functions_mediboard.group_id"] = "= '$group->_id'";
}

if ($filter) {
    $where[] = "(" .
        "user_username " . $ds->prepareLike("%$filter%") . " OR " .
        "user_first_name " . $ds->prepareLike("%$filter%") . "OR " .
        "user_last_name " . $ds->prepareLike("%$filter%") . ")";
}

if ($type) {
    if ($type == "ps") {
        $user_types  = [
            "Chirurgien",
            "Anesthésiste",
            "Médecin",
            "Infirmière",
            "Rééducateur",
            "Sage Femme",
            "Diététicien",
        ];
        $utypes_flip = array_flip(CUser::$types);

        if (is_array($user_types)) {
            foreach ($user_types as $key => $value) {
                $user_types[$key] = $utypes_flip[$value];
            }

            $where["users.user_type"] = CSQLDataSource::prepareIn($user_types);
        }
    } else {
        $where["users.user_type"] = "= '$type'";
    }
}

$now = CMbDT::date();

if ($inactif == "1") {
    $where["users_mediboard.actif"] = "= '0' OR (users_mediboard.fin_activite IS NOT NULL AND users_mediboard.fin_activite <= '$now')";
}
if ($inactif == "0") {
    $where["users_mediboard.actif"] = "= '1' AND (users_mediboard.fin_activite IS NULL OR users_mediboard.fin_activite > '$now')";
}

if ($locked == "1" || $locked == "0") {
    $where = array_merge($where, CUser::getIsLockedQuery((bool)$locked));
}

if ($function_id) {
    $where["users_mediboard.function_id"] = " = '$function_id'";
}

if (($ldap_bound || $ldap_bound === '0') && !is_array($ldap_bound)) {
    if ($ldap_bound === '0') {
        $where["users.ldap_uid"] = 'IS NULL';
    } else {
        $where["users.ldap_uid"] = 'IS NOT NULL';
    }
}

if ($user_loggable) {
    $robots = [];
    $ds     = CSQLDataSource::get("std");
    $tag    = CMediusers::getTagSoftware();
    if ($tag) {
        $query = "SELECT users.user_id
            FROM users
            LEFT JOIN id_sante400 ON users.user_id = id_sante400.object_id
            WHERE (id_sante400.object_class = 'CMediusers'
              AND id_sante400.tag = ?)
              OR users.is_robot = '1'
            GROUP BY users.user_id";

        $query = $ds->prepare($query, $tag);
    } else {
        $query = "SELECT users.user_id
            FROM users
            WHERE users.is_robot = '1'";
    }
    $robots = $ds->loadColumn($query);

    if ($user_loggable == "robot") {
        if (count($robots)) {
            $where["users.user_id"] = $ds->prepareIn($robots);
        } else {
            $where[] = " 1 = 0";
        }
    } elseif ($user_loggable == "human") {
        if (count($robots)) {
            $where["users.user_id"] = $ds->prepareNotIn($robots);
        }
    }
}

$order = null;
if ($order_col == "function_id") {
    $order = "functions_mediboard.text $order_way, users.user_last_name ASC, users.user_first_name ASC";
}
if ($order_col == "user_username") {
    $order = "users.user_username $order_way, users.user_last_name ASC, users.user_first_name ASC";
}
if ($order_col == "user_last_name") {
    $order = "users.user_last_name $order_way, users.user_first_name ASC";
}
if ($order_col == "user_first_name") {
    $order = "users.user_first_name $order_way, users.user_last_name ASC";
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

$total_mediuser = $mediuser->countList($where, null, $ljoin);
$mediusers      = $mediuser->loadList($where, $order, "$page, $step", "users_mediboard.user_id", $ljoin);

CStoredObject::massLoadFwdRef($mediusers, 'function_id');
CStoredObject::massLoadFwdRef($mediusers, '_profile_id');

/** @var CMediusers[] $mediusers */
foreach ($mediusers as $_mediuser) {
    $_mediuser->loadRefFunction();
    $_mediuser->loadRefProfile();
    $_mediuser->loadRefUser();
    $_mediuser->_ref_user->isLDAPLinked();
    $_mediuser->getLastLogin();
}

$smarty = new CSmartyDP();

$smarty->assign("mediusers", $mediusers);
$smarty->assign("user_id", $user_id);
$smarty->assign("total_mediuser", $total_mediuser);
$smarty->assign("order_col", $order_col);
$smarty->assign("order_way", $order_way);
$smarty->assign("page", $page);
$smarty->assign("step", $step);

$smarty->display("inc_search_mediusers.tpl");
