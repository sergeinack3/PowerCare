<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbException;
use Ox\Core\Security\Csrf\AntiCsrf;
use Ox\Mediboard\Admin\CLDAP;
use Ox\Mediboard\Admin\CUser;

CCanDo::check();

$params = AntiCsrf::validatePOST();

// Do not close the session here otherwise CAppUI::$instance->weak_password mutation will not be persisted
//CView::checkin();

// Ne pas passer en CView
$old_pwd  = $params['old_pwd'];
$new_pwd1 = $params['new_pwd1'];
$new_pwd2 = $params['new_pwd2'];
$callback = $params['callback'];

$user     = CUser::get();
$username = $user->user_username;

if (!$user->checkActivationToken()) {
    // Vérification du mot de passe actuel de l'utilisateur courant
    $user = CUser::checkPassword($username, $old_pwd, true);
}

// Mot de passe actuel correct
if (!$user->_id) {
    CAppUI::stepAjax("CUser-user_password-nomatch", UI_MSG_ERROR);
}

if (!$user->canChangePassword()) {
    CAppUI::stepAjax("CUser-password_change_forbidden", UI_MSG_ERROR);
}

// Mots de passe différents
if ($new_pwd1 != $new_pwd2) {
    CAppUI::stepAjax("CUser-user_password-nomatch", UI_MSG_ERROR);
}

// Enregistrement
$user->_user_password = $new_pwd1;
$user->_is_changing   = true;

// If user was obliged to change and successfully changed, remove flag
if ($user->force_change_password) {
    $user->force_change_password = '0';
}

if ($msg = $user->store()) {
    CAppUI::stepAjax($msg, UI_MSG_ERROR);
}

// Si utilisateur associé au LDAP et modif mdp autorisée
if ($user->isLDAPLinked()) {
    try {
        if (CLDAP::changePassword($user, $old_pwd, $new_pwd1)) {
            CAppUI::resetPasswordRemainingDays();
            CUser::resetPasswordMustChange();

            CAppUI::stepAjax("CLDAP-change_password_succeeded", UI_MSG_OK);
        } else {
            CAppUI::stepAjax("CLDAP-change_password_failed", UI_MSG_WARNING);
        }
    } catch (CMbException $e) {
        // Rétablissement de l'ancien mot de passe
        $user->_user_password = $old_pwd;
        if ($msg = $user->store()) {
            CAppUI::stepAjax($msg, UI_MSG_ERROR);
        }

        $e->stepAjax();
        CAppUI::stepAjax("CLDAP-change_password_failed", UI_MSG_ERROR);
    }
}

CAppUI::stepAjax("CUser-msg-password-updated", UI_MSG_OK);
CAppUI::$instance->weak_password = false;
CAppUI::callbackAjax($callback);

CApp::rip();
