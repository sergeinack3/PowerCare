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
use Ox\Core\CMbSecurity;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CLDAP;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$mediuser_id    = CValue::get("mediuser_id");
$samaccountname = CValue::get("samaccountname");

$mediuser = new CMediusers();
$mediuser->load($mediuser_id);

$user = ($mediuser->_ref_user) ?: new CUser();

$force_create = false;
if (!$mediuser->_id) {
    // Generating a random password for new user from LDAP in order to keep consistency
    $user->_is_changing   = true;
    $user->_user_password = CMbSecurity::getRandomPassword();

    $force_create = true;
}

try {
    $chain_ldap = CLDAP::poolConnect(null, CGroups::loadCurrent()->_id);

    if ($chain_ldap === false || $chain_ldap->areUnreachable()) {
        CAppUI::stepAjax('CSourceLDAP_all-unreachable', UI_MSG_ERROR);
    }

    $chain_ldap->bind(true);

    $user = $chain_ldap->searchAndMap($user, $samaccountname, null, true, false);
} catch (CMbException $e) {
    $e->stepAjax(UI_MSG_ERROR);
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("user", $user);
$smarty->assign("association", $mediuser_id ? 0 : 1);
$smarty->display("inc_create_user_ldap.tpl");
