<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Core\Security\Csrf\AntiCsrf;
use Ox\Mediboard\Admin\CPermModule;
use Ox\Mediboard\Admin\CPermObject;
use Ox\Mediboard\Admin\CUser;

CCanDo::checkEdit();

$user = CUser::get(CView::get("user_id", "ref class|CUser", true));
CView::checkin();

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

// DROITS SUR LES OBJETS
$permObject  = new CPermObject();
$orderObject = "object_class, object_id";

$permsModules = $permModule->loadList($whereProfil, $order);

CStoredObject::massLoadFwdRef($permsModules, "mod_id");

// Droit du profil sur les modules
foreach ($permsModules as $_perm) {
    $permsModuleCount++;
    $_perm->_owner = "template";
    $_perm->loadRefDBModule();
    $permsModule[$_perm->mod_id ?: "all"]["module"]["profil"] = $_perm;
}

//obj
$whereProfil["object_class"] = "!= 'CNote'"; // Exclusion des notes
$permObjects                 = $permObject->loadList($whereProfil, $orderObject);

CStoredObject::massLoadFwdRef($permObjects, "object_id");

foreach ($permObjects as $_permO) {
    $_permO->_owner                                                                             = "template";
    $object                                                                                     = $_permO->loadRefDBObject(
    );
    $permsModule[$_permO->_ref_db_object->_ref_module->_id]["object"][$object->_guid]["profil"] = $_permO;
}

$permsModules = $permModule->loadList($whereUser, $order);

CStoredObject::massLoadFwdRef($permsModules, "mod_id");

foreach ($permsModules as $_perm) {
    $permsModuleCount++;
    $_perm->_owner                                        = "user";
    $module                                               = $_perm->loadRefDBModule();
    $permsModule[$module->_id ?: "all"]["module"]["user"] = $_perm;
}

$whereUser["object_class"] = "!= 'CNote'"; // Exclusion des notes
$permObjects               = $permObject->loadList($whereUser, $orderObject);
CStoredObject::massLoadFwdRef($permObjects, "object_id");

//obj
foreach ($permObjects as $_permO) {
    $_permO->_owner = "user";
    $object         = $_permO->loadRefDBObject();

    $structure = [
        "user" => $_permO,
    ];

    $permsModule[$_permO->_ref_db_object->_ref_module->_id]["object"][$object->_guid]["user"] = $_permO;
}

foreach ($permsModule as $_module_id => $_permModule) {
    if (isset($permsModule[$_module_id]["object"])) {
        krsort($permsModule[$_module_id]["object"]);
    }
}

$classes        = CApp::getInstalledClasses();
$module_classes = CApp::groupClassesByModule($classes);

$module  = new CModule();
$modules = $module->loadList(["mod_id" => CSQLDataSource::prepareIn(array_keys($permsModule))]);

if (isset($permsModule["all"])) {
    $modules["all"] = $module;
}

$modules_views = CMbArray::pluck($modules, "_view");

array_multisort($modules_views, SORT_REGULAR, $modules);

$token_perm_module = AntiCsrf::prepare()
    ->addParam('perm_module_id')
    ->addParam('user_id', $user->_id)
    ->addParam('element_id')
    ->addParam('mod_id')
    ->addParam('permission')
    ->addParam('view')
    ->getToken();

$token_perm_object = AntiCsrf::prepare()
    ->addParam('perm_object_id')
    ->addParam('user_id', $user->_id)
    ->addParam('object_class')
    ->addParam('object_id')
    ->addParam('_object_view')
    ->addParam('permission')
    ->getToken();

$smarty = new CSmartyDP();
$smarty->assign('token_perm_module_item', $token_perm_module);
$smarty->assign('token_perm_object_item', $token_perm_object);
$smarty->assign("module_classes", $module_classes);
$smarty->assign("permsModule", $permsModule);
$smarty->assign("permModule", $permModule);
$smarty->assign("permObject", $permObject);
$smarty->assign("user", $user);
$smarty->assign("modules", $modules);

$smarty->display("inc_list_perms");
