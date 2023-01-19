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
use Ox\Core\CMbString;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CLDAP;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$user_username   = CValue::get("user_username");
$user_first_name = CValue::get("user_first_name");
$user_last_name  = CValue::get("user_last_name");

// LDAP filtering
$user_username   = CLDAP::escape($user_username);
$user_first_name = CLDAP::escape($user_first_name);
$user_last_name  = CLDAP::escape($user_last_name);

// Création du template
$smarty = new CSmartyDP();

if ($user_username || $user_first_name || $user_last_name) {
    try {
        $chain_ldap = CLDAP::poolConnect(null, CGroups::loadCurrent()->_id);

        if ($chain_ldap === false || $chain_ldap->areUnreachable()) {
            CAppUI::stepAjax('CSourceLDAP_all-unreachable', UI_MSG_ERROR);
        }

        $chain_ldap->bind(true);
    } catch (CMbException $e) {
        $e->stepAjax(UI_MSG_ERROR);
    }

    $results_by_source = $chain_ldap->search($user_username, $user_first_name, $user_last_name);

    $nb_users = $results_by_source["count"];
    unset($results_by_source["count"]);

    $users = [];
    foreach ($results_by_source as $_source_id => $_results) {
        unset($_results['count']);

        foreach ($_results as $key => $_result) {
            $_source = $chain_ldap->getBoundSourceById($_source_id);

            $objectguid = CLDAP::getObjectGUID($_result, $_source);

            if (!$objectguid) {
                continue;
            }

            $users[$objectguid] = [
                'objectguid'      => $objectguid,
                'user_username'   => $_source->isAlternativeBinding() ?
                    CLDAP::getValue($_result, 'cn')
                    : CLDAP::getValue($_result, "samaccountname"),
                'user_first_name' => CLDAP::getValue($_result, "givenname"),
                'user_last_name'  => CLDAP::getValue($_result, "sn"),
                'actif'           => (CLDAP::getValue($_result, 'useraccountcontrol') & 2) ? 0 : 1,
                'associate'       => null,
            ];

            $_user = CUser::loadFromLdapUid($objectguid);

            $users[$objectguid]['associate'] = ($_user && $_user->_id) ? $_user->_id : null;
        }
    }

    $mediuser = new CMediusers();

    $smarty->assign("users", $users);
    $smarty->assign("mediuser", $mediuser);
    $smarty->assign("nb_users", $nb_users);
    $smarty->assign("givenname", CMbString::capitalize($user_first_name));
    $smarty->assign("sn", strtoupper($user_last_name));
    $smarty->assign("samaccountname", strtolower($user_username));
    $smarty->display("inc_search_user_ldap.tpl");
} else {
    $smarty->display("inc_choose_filter_ldap.tpl");
}
