<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CUser;

$tempUserName    = CValue::post("temp_user_name", "");
$permission_user = CValue::post("permission_user", "");
$delPermissions  = CValue::post("delPerms", false);

// pull user_id for unique user_username (templateUser)
$tempUser = new CUser();
$where = array();
$where["user_username"] = "= '$tempUserName'";
$tempUser->loadObject($where);

$user = new CUser;
$user->user_id = $permission_user;
$msg = $user->copyPermissionsFrom($tempUser->user_id, $delPermissions);

CAppUI::setMsg("Permissions");
CAppUI::setMsg($msg ? $msg : "copied from template", $msg ? UI_MSG_ERROR : UI_MSG_OK, true);
CAppUI::redirect();
