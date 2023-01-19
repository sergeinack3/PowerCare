<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CLDAP;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$mediuser_id = CValue::get("user_id");
$action      = CValue::get("action", "update");
$mediuser    = new CMediusers();
$mediuser->load($mediuser_id);

if ($mediuser->_id) {
    $user = $mediuser->_ref_user;
    if ($user->_id && $user->isLDAPLinked()) {
        $ldap_uid = $user->getLdapUid();

        if ($action == "update") {
            CLDAP::login($user, $ldap_uid);
            CAppUI::stepAjax("user-updated-from-ldap");
        } elseif ($action == "unlink") {
            $user->ldap_uid = '';

            if ($user->store() === null) {
                if ($user->_id == CMediusers::get()->_id) {
                    CAppUI::$instance->_is_ldap_linked = false;
                }
            }

            CAppUI::stepAjax("user-unlink_from_ldap");
        }
    }
}

CApp::rip();
