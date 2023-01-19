<?php

/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CUserAuthentication;
use Ox\Mediboard\System\CUserAuthenticationError;

CCanDo::checkEdit();

$_start_date           = CView::get('_start_date', "dateTime");
$_end_date             = CView::get('_end_date', "dateTime");
$expiration_start_date = CView::get('_expiration_start_date', 'dateTime');
$expiration_end_date   = CView::get('_expiration_end_date', 'dateTime moreThan|_expiration_start_date');
$_auth_method          = CView::get('_auth_method', "set list|" . implode("|", CUserAuthentication::AUTH_METHODS));
$user_id               = CView::get('user_id', "str");
$ip_address            = CView::get('ip_address', "str");
$type                  = CView::get('type', "enum list|success|error default|success");
$start                 = (int)CView::get('start', "num default|0");

// Only errors
$login      = CView::get('login', "str");
$identifier = CView::get('identifier', "str");

// Only success
$session_id = CView::get('session_id', "str");
$user_type  = CView::get('_user_type', 'enum list|all|human|bot');

// Domain
$domain     = CView::get('_domain', "enum list|all|group|function default|group", true);

CView::checkin();

CView::enforceSlave();

switch ($type) {
    case "success":
        $user_auth = new CUserAuthentication();
        $datefield = "datetime_login";
        break;

    case "error":
        $user_auth = new CUserAuthenticationError();
        $datefield = "datetime";
        break;

    default:
        throw new CMbException("Unknown auth type $type");
}

$ds    = $user_auth->getDS();
$table = $user_auth->getSpec()->table;
$key   = $user_auth->getSpec()->key;

$ljoin = [];
$where = [];

if ($_start_date) {
    $where[] = $ds->prepare("$table.$datefield >= ?", $_start_date);
}

if ($_end_date) {
    $where[] = $ds->prepare("$table.$datefield <= ?", $_end_date);
}

if ($_auth_method) {
    $where["{$table}.auth_method"] = $ds->prepareIn(explode('|', $_auth_method));
}

if ($user_id) {
    $user = new CMediusers();
    $user->load($user_id);
    $user->needsRead();

    $where["{$table}.user_id"] = $ds->prepare('= ?', $user_id);
} else {
    $user  = new CMediusers();
    switch ($domain) {
        case 'all':
            $users = $user->loadUsers(PERM_READ, null, null, true, false);
            break;
        case 'function':
            $users = $user->loadUsers(PERM_READ, CFunctions::getCurrent()->_id);
            break;
        case 'group':
        default:
            $users = $user->loadUsers();
            break;
    }

    $where["{$table}.user_id"] = $ds->prepareIn(CMbArray::pluck($users, 'user_id')) . " OR {$table}.user_id IS NULL";
}

if ($ip_address) {
    $where["{$table}.ip_address"] = $ds->prepareLike("%$ip_address%");
}

if ($user_auth instanceof CUserAuthentication) {
    if ($session_id) {
        $where["{$table}.session_id"] = $ds->prepareLike("$session_id%");
    }

    switch ($user_type) {
        case 'human':
            $ljoin = [
                'users' => "{$table}.user_id = users.user_id",
            ];

            $where[] = "users.is_robot = '0'";
            break;

        case 'bot':
            $ljoin = [
                'users' => "{$table}.user_id = users.user_id",
            ];

            $where[] = "users.is_robot = '1'";
            break;

        case 'all':
        default:
    }

    if ($expiration_start_date) {
        $where[] = $ds->prepare("{$table}.expiration_datetime >= ?", $expiration_start_date);
    }

    if ($expiration_end_date) {
        $where[] = $ds->prepare("{$table}.expiration_datetime <= ?", $expiration_end_date);
    }
}

if ($user_auth instanceof CUserAuthenticationError) {
    if ($login) {
        $where["{$table}.login"] = $ds->prepare('= ?', $login);
    }

    if ($identifier) {
        $where["{$table}.identifier"] = $ds->prepare('= ?', $identifier);
    }
}

$limit    = "{$start}, 50";
$group_by = "{$table}.{$key}";

/** @var CUserAuthentication[] $user_auths */
$user_auths = $user_auth->loadList($where, "{$table}.{$key} DESC", $limit, $group_by, $ljoin);
$total      = $user_auth->countMultipleList($where, null, $group_by, $ljoin);
$total      = count($total);

CStoredObject::massLoadFwdRef($user_auths, 'user_id');

if ($type === "success") {
    CStoredObject::massLoadFwdRef($user_auths, 'previous_auth_id');
}

foreach ($user_auths as $_user_auth) {
    $_user_auth->loadRefUser()->loadRefMediuser()->loadRefFunction();

    if ($_user_auth instanceof CUserAuthentication) {
        if ($_user_auth->loadRefPreviousUserAuthentication()) {
            $_user_auth->_ref_previous_auth->loadView();
        }

        if ($_user_auth->auth_method === CUserAuthentication::AUTH_METHOD_REACTIVE) {
            $_user_auth->ip_address = null;
            $_user_auth->session_id = null;
        }
    }
}

$smarty = new CSmartyDP();
$smarty->assign('start', $start);
$smarty->assign('total', $total);
$smarty->assign('user_auths', $user_auths);

if ($user_auth instanceof CUserAuthentication) {
    $smarty->display('../../admin/templates/inc_vw_users_auth.tpl');
} elseif ($user_auth instanceof CUserAuthenticationError) {
    $smarty->display('../../admin/templates/inc_vw_users_auth_error.tpl');
}
