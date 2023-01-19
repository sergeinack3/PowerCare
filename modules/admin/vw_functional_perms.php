<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CPermModule;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CPreferences;

CCanDo::checkEdit();

$user_id    = CCanDo::edit() ? CView::get("user_id", "str default|default", true) : null;
$user       = CUser::get($user_id);
$prof       = $user->profile_id ? CUser::get($user->profile_id) : new CUser();
$show_icone = CView::get("show_icone", "bool default|1");

CView::checkin();

if ($user_id == "default") {
    $user->_id = null;
}

$prefvalues = [
    "default"  => CPreferences::get(null, true),
    "template" => $user->profile_id ? CPreferences::get($user->profile_id, true) : [],
    "user"     => $user->_id !== "" ? CPreferences::get($user->_id, true) : [],
];

// common sera toujours au debut
$prefs = [
    "common" => [],
];

// Classement par module et par permission fonctionnelle
CPreferences::loadModules(true);
foreach (CPreferences::$modules as $modname => $prefnames) {
    $module  = CModule::getActive($modname);
    $canRead = $module ? CPermModule::getPermModule($module->_id, PERM_READ, $user_id) : false;

    if ($modname == "common" || $user_id == "default" || $canRead) {
        $prefs[$modname] = [];
        foreach ($prefnames as $prefname) {
            $prefs[$modname][$prefname] = [
                "default"  => CMbArray::extract($prefvalues["default"], $prefname),
                "template" => CMbArray::extract($prefvalues["template"], $prefname),
                "user"     => CMbArray::extract($prefvalues["user"], $prefname),
            ];
        }
    }
}

// Warning: user clone necessary!
// Some module index change $user global
$user_clone = $user;
// Chargement des modules
$modules = CPermModule::getVisibleModules();
foreach ($modules as $module) {
    // Module might not be present
    $module->registerTabs();
    //    @include "./modules/$module->mod_name/index.php";
}
$user = $user_clone;

$smarty = new CSmartyDP();
$smarty->assign("user", $user);
$smarty->assign("prof", $prof);
$smarty->assign("user_id", $user_id);
$smarty->assign("modules", $modules);
$smarty->assign("prefs", $prefs);
$smarty->assign("show_icone", $show_icone);

$smarty->display("vw_edit_functional_perms.tpl");
