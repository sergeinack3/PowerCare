<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CPermModule;
use Ox\Mediboard\Admin\CPermObject;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;

$user = CUser::get();

$username = trim(CValue::post('username'));
$password = trim(CValue::post('password'));

// If substitution happens when a session is locked
$is_locked = CValue::get("is_locked");

// Because $_SESSION is not renewed
CAppUI::$instance->_is_ldap_linked                = null;
CAppUI::resetPasswordRemainingDays();
CUser::resetPasswordMustChange();

$ldap_connection     = CAppUI::conf('admin LDAP ldap_connection');
$allow_login_as_ldap = CAppUI::conf('admin LDAP allow_login_as_admin');

$_REQUEST['loginas']    = $username;
$_REQUEST['passwordas'] = $password;

// Password isn't need for admin only if session isn't locked
if (($user->user_type != 1 || $is_locked || ($ldap_connection && $user->user_type == 1 && !$allow_login_as_ldap)) && !CUser::checkPassword($username, $password)) {
  $msg = "Auth-failed-" . (!$password ? "nopassword" : "combination");
  CAppUI::setMsg($msg, UI_MSG_ERROR);
  echo CAppUI::getMsg();

  CApp::rip();
}

CAppUI::login(true);

if ($msg = CAppUI::getMsg()) {
  echo $msg;

  CApp::rip();
}

// Substitution can't target current group if the substituted user hasn't write access on it
$indexGroup = CGroups::get();

// Build perms again (old user perms in cache)
CPermObject::loadUserPerms();
CPermModule::loadUserPerms();

if (!$indexGroup->getPerm(PERM_EDIT)) {
  CValue::setSessionAbs("g", CAppUI::$instance->user_group);
}

if ($is_locked) {
  $_SESSION['locked'] = false;
}
CAppUI::callbackAjax('UserSwitch.reload');
