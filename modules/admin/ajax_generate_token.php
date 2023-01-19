<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbSecurity;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CPermModule;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\CViewAccessToken;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$token_default_params = CView::get("token_default_params", 'str');

CView::checkin();

$token = new CViewAccessToken();

$cron_username = CAppUI::conf("admin CViewAccessToken cron_name");
$modules_list  = CAppUI::conf("admin CViewAccessToken modules");

$modules = explode('|', $modules_list);

$perm_modules = array();
foreach ($modules as $module) {
  $perm_modules[$module] = array(
    'permission' => CPermModule::EDIT,
    'view'       => CPermModule::DENY
  );
}

/* Try to load Cron user object from its name */
$cron_user = new CUser();
$cron_user->user_username = $cron_username;

if ($cron_user->loadMatchingObject()) {
  CAppUI::displayAjaxMsg(
    'CViewAccessToken-error-Username for token generation already exists: %s',
    UI_MSG_ERROR,
    $cron_username
  );
  CAppUI::js("Control.Modal.close()");
  CApp::rip();
}

$cron_mediuser = new CMediusers();
$cron_mediuser->_user_username  = $cron_username;
$cron_mediuser->_user_last_name = strtoupper($cron_username);
$cron_mediuser->_user_sexe = 'u';
$cron_mediuser->_user_type = 14; //Personnel
$cron_mediuser->_dont_log_connection = 1;
$cron_mediuser->_user_password = CMbSecurity::getRandomPassword();
$cron_mediuser->function_id = CMediusers::get()->function_id;
$cron_mediuser->actif = 1;
$cron_mediuser->remote = 1;
if ($msg = $cron_mediuser->store()) {
  CAppUI::stepAjax($msg, UI_MSG_ERROR);
}

$cron_mediuser->delFunctionPermission();
$cron_mediuser->loadRefUser();

/* Mark CUser as robot type user */
$cron_user = $cron_mediuser->_ref_user;
if ($cron_user instanceof CUser && $cron_user->_id) {
  $cron_user->is_robot = true;
  if ($msg = $cron_user->store()) {
    CAppUI::stepAjax($msg, UI_MSG_ERROR);
  }
}

if ($cron_mediuser->_id) {

  /* Assign user_id to token */
  $token->user_id = $cron_mediuser->user_id;

  /* Set permissions of monitorClient module for this user */
  foreach ($perm_modules as $module_name => $perm_module) {

    /* Load module from its name */
    $module = new CModule();
    $module->loadByName($module_name);

    /* Create permissions */
    if ($module instanceof CModule && $module->_id) {
      $perm = new CPermModule();
      $perm->mod_id  = $module->_id;
      $perm->user_id = $cron_mediuser->_id;

      if (array_key_exists('permission', $perm_module) && !empty($perm_module['permission'])) {
        $perm->permission = $perm_module['permission'];
      }
      if (array_key_exists('view', $perm_module) && !empty($perm_module['view'])) {
        $perm->view = $perm_module['view'];
      }

      /* Store the newly permission for user */
      if ($msg = $perm->store()) {
        CAppUI::stepAjax($msg, UI_MSG_ERROR);
      }
    }
  }
}

try {
  $params = json_decode(stripslashes($token_default_params));
  if ($params->validator
      && strpos($params->validator, 'TokenValidator') !== false
  ) {
    $token->validator = $params->validator;
  }
  else {
    CAppUI::stepAjax('Invalid Token Validator value !', UI_MSG_ERROR);
  }

  if ($params->params) {
    $params_string = '';
    foreach ($params->params as $key => $value) {
      $params_string .= $key.'='.$value."\n";
    }
    if (!empty($params_string)) {
      $token->params = $params_string;
    }
  }
  else {
    CAppUI::stepAjax('Invalid Token Parameters value !', UI_MSG_ERROR);
  }

  $token->_hash_length = ($params->_hash_length) ?? null;

  $token->datetime_start = CMbDT::dateTime();
  $token->restricted     = true;
  $token->getValidators();
  $token->loadRefUser();

} catch(\Exception $e) {
  CAppUI::stepAjax($e->getMessage(), UI_MSG_ERROR);
}

$smarty = new CSmartyDP();
$smarty->assign("token", $token);
$smarty->display("inc_generate_token.tpl");
