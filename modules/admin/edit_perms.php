<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Core\Security\Csrf\AntiCsrf;
use Ox\Mediboard\Admin\CPermModule;
use Ox\Mediboard\Admin\CPermObject;
use Ox\Mediboard\Admin\CUser;

CCanDo::checkEdit();

$user = CUser::get(CValue::getOrSession("user_id"));

$user_id = CValue::getOrSession("user_id", $user->_id);

if (!$user_id) {
    CAppUI::setMsg("Vous devez sélectionner un utilisateur");
    CAppUI::redirect("m=admin&tab=vw_edit_users");
}

$modulesInstalled = CModule::getInstalled();
$isAdminPermSet   = false;

$profile = new CUser();
if ($user->profile_id) {
    $where["user_id"] = "= '$user->profile_id'";
    $profile->loadObject($where);
}

$order = "mod_id";

//Droit de l'utilisateur sur les modules
$whereUser            = [];
$whereUser["user_id"] = "= '$user->user_id'";

$whereProfil            = [];
$whereProfil["user_id"] = "= '$user->profile_id'";

// DROITS SUR LES MODULES
$permModule       = new CPermModule();
$permsModule      = [];
$permsModuleCount = 0;

$perms_modules = $permModule->loadList($whereUser, $order);

CStoredObject::massLoadFwdRef($perms_modules, "mod_id");

foreach ($perms_modules as $_perm) {
    $module = $_perm->loadRefDBModule();
    if (!$module->_id) {
        $isAdminPermSet = true;
    }
    $permsModule[$module->_id]["user"] = $_perm;
    unset($modulesInstalled[$module->mod_name]);
}

$modulesInstalled_names = CMbArray::pluck($modulesInstalled, "mod_name");
$modulesInstalled_trad  = [];

foreach ($modulesInstalled as $_mod) {
    if ($modulesInstalled_names[$_mod->mod_name] == $_mod->mod_name) {
        $modulesInstalled_trad[$_mod->_id] = CAppUI::tr("module-$_mod->mod_name-court");
    }
}

CMbArray::naturalSort($modulesInstalled_trad);

// DROITS SUR LES OBJETS
$permObject = new CPermObject();

$token_perm_module = AntiCsrf::prepare()
    ->addParam('perm_module_id')
    ->addParam('callback', 'LoadListExistingRights')
    ->addParam('user_id', $user->_id)
    ->addParam('element_id')
    ->addParam('mod_id')
    ->addParam('permission')
    ->addParam('view')
    ->getToken();

$token_perm_object = AntiCsrf::prepare()
    ->addParam('perm_object_id')
    ->addParam('callback', 'LoadListExistingRights')
    ->addParam('user_id', $user->_id)
    ->addParam('object_class')
    ->addParam('object_id')
    ->addParam('_object_view')
    ->addParam('autocomplete_input')
    ->addParam('permission')
    ->getToken();

$smarty = new CSmartyDP();
$smarty->assign('token_perm_module', $token_perm_module);
$smarty->assign('token_perm_object', $token_perm_object);
$smarty->assign("user", $user);
$smarty->assign("modulesInstalled_trad", $modulesInstalled_trad);
$smarty->assign("isAdminPermSet", $isAdminPermSet);
$smarty->assign("permModule", $permModule);
$smarty->assign("permObject", $permObject);
$smarty->assign("profile", $profile);

$smarty->display("edit_perms");
