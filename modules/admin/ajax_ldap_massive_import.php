<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbException;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CLDAP;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkRead();

$do_import = CValue::get("do_import");
$start     = CValue::getOrSession("start", 0);
$count     = CValue::get("count", 5);

$group_id = CGroups::loadCurrent()->_id;

$user = new CUser();

// Requêtes
$ljoin                        = [];
$ljoin["users_mediboard"]     = "`users`.`user_id` = `users_mediboard`.`user_id`";
$ljoin["functions_mediboard"] = "`functions_mediboard`.`function_id` = `users_mediboard`.`function_id`";

$where                                 = [];
$where["users.ldap_uid"]               = "IS NOT NULL";
$where["users.template"]               = "= '0'";
$where["users_mediboard.actif"]        = "= '1'";
$where["functions_mediboard.group_id"] = "= '$group_id'";

if (!$do_import) {
    $count_users_ldap = $user->countList($where, null, $ljoin);

    $ljoin                        = [];
    $ljoin["users_mediboard"]     = "`users`.`user_id` = `users_mediboard`.`user_id`";
    $ljoin["functions_mediboard"] = "`functions_mediboard`.`function_id` = `users_mediboard`.`function_id`";

    $where                                 = [];
    $where["users.template"]               = "= '0'";
    $where["users_mediboard.actif"]        = "= '1'";
    $where["functions_mediboard.group_id"] = "= '$group_id'";

    $count_users_all = $user->countList($where, null, $ljoin);
    CAppUI::stepAjax(($count_users_all - $count_users_ldap) . " comptes qui ne sont pas associés");
} else {
    // Récupération de la liste des comptes qui ne sont pas associés
    $users_ldap = $user->loadList($where, null, null, null, $ljoin);

    $ljoin                        = [];
    $ljoin["users_mediboard"]     = "`users`.`user_id` = `users_mediboard`.`user_id`";
    $ljoin["functions_mediboard"] = "`functions_mediboard`.`function_id` = `users_mediboard`.`function_id`";

    $where                                 = [];
    $where["users.template"]               = "= '0'";
    $where["functions_mediboard.group_id"] = "= '$group_id'";
    $users_all                             = $user->loadList($where, null, null, null, $ljoin);

    /** @var $users CUser[] */
    $users = array_diff_key($users_all, $users_ldap);
    $users = array_slice($users, $start, $count);

    $count = $count_no_associate = $count_associate = 0;

    try {
        $chain_ldap = CLDAP::poolConnect(null, $group_id);

        if ($chain_ldap === false || $chain_ldap->areUnreachable()) {
            CAppUI::stepAjax('CSourceLDAP_all-unreachable', UI_MSG_ERROR);
        }

        $chain_ldap->bind(true);
    } catch (CMbException $e) {
        $e->stepAjax(UI_MSG_ERROR);
    }

    foreach ($users as $_user) {
        try {
            $_user = $chain_ldap->searchAndMap($_user, $_user->user_username, null, false, false);
        } catch (CMbException $e) {
            $e->stepAjax();
        }

        if ($_user->_count_ldap != 0) {
            $count_associate++;
        }

        if ($_user->_count_ldap == 0) {
            CAppUI::stepAjax("'$_user->_view' / '$_user->user_username' non associé", UI_MSG_WARNING);
            $count_no_associate++;
        }

        $count++;
    }
    if ($count == 0) {
        echo "<script type='text/javascript'>stop=true;</script>";
    }

    $next = $start + $count_no_associate;

    CAppUI::stepAjax("$count_associate comptes associés");
    CAppUI::stepAjax("$count_no_associate comptes non associés", UI_MSG_WARNING);

    CValue::setSession("start", $next);
    CAppUI::stepAjax("On continuera au n° $next / " . count($users) . " restants");
}
