<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Erp\Exploitation\COXOperation;
use Ox\Erp\Project\COXProject;
use Ox\Erp\Tasking\CTaskingTicket;
use Ox\Mediboard\Admin\CPermModule;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CPreferences;

$user_id    = CCanDo::edit() ? CView::get("user_id", "str default|default", true) : null;
$user       = CUser::get($user_id);
$prof       = $user->profile_id ? CUser::get($user->profile_id) : new CUser;
$show_icone = CView::get("show_icone", "bool default|1");
CView::checkin();

if ($user_id == "default") {
    $user->_id = null;
}

$prefvalues = [
    "default"  => CPreferences::get(),
    "template" => $user->profile_id ? CPreferences::get($user->profile_id) : [],
    "user"     => $user->_id !== "" ? CPreferences::get($user->_id) : [],
];

// common sera toujours au debut
$prefs = [
    "common" => [],
];

// Module name => Module category mapping
$mod_categories = [];

// Classement par module et par préférences
CPreferences::loadModules();
foreach (CPreferences::$modules as $modname => $prefnames) {
    $module  = CModule::getActive($modname);
    $canRead = $module ? CPermModule::getPermModule($module->_id, PERM_READ, $user_id) : false;

    if ($modname == "common" || $user_id == "default" || $canRead) {
        $prefs[$modname] = [];

        $mod_categories[$modname] = ($module && $module->mod_category) ? $module->mod_category : "";

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

// Locales and styles
$locales = CAppUI::readDirs("locales");
$styles  = CAppUI::readDirs("style");

// Hide the Mediboard Extended Theme option
if (isset($styles["mediboard_ext"])) {
    unset($styles["mediboard_ext"]);
}

// Get session lifetime in php.ini
$gc_maxlifetime   = ini_get("session.gc_maxlifetime");
$session_lifetime = false;
if (!empty($gc_maxlifetime)) {
    $session_lifetime = (int)($gc_maxlifetime / 60);
}

$session_lifetime_values = ["", 5, 10, 15, 20, 25, 30, 45, 60, 120, 180, 240, 300];
$session_lifetime_enum   = implode("|", $session_lifetime_values);
if ($session_lifetime) {
    $session_lifetime_enum = [];

    foreach ($session_lifetime_values as $_enum) {
        if ($_enum <= $session_lifetime) {
            $session_lifetime_enum[] = $_enum;
        }
    }

    if (!empty($session_lifetime_enum)) {
        $session_lifetime_enum = implode("|", $session_lifetime_enum);
    }
}

$smarty = new CSmartyDP();


// Tasking
if (CModule::getActive("tasking") && CModule::getCanDo("tasking")->read) {
    $tasking = CTaskingTicket::getPrefs($prefs, $user);

    $smarty->assign("owners", $tasking['pre_filled_owners']);
    $smarty->assign("digest_owners", $tasking['digest_owners']);
    $smarty->assign("request_ticket", $tasking["request_ticket"]);
}

// oxExploitation
if (CModule::getActive("oxExploitation") && CModule::getCanDo("oxExploitation")->read) {
    $customers = COXOperation::getPrefs($prefs, $user);

    $smarty->assign("customers", $customers);
}

// oxProject
if (CModule::getActive("oxProject") && CModule::getCanDo('oxProject')->read) {
    $oxProject = COXProject::getPrefs($prefs, $user);

    $smarty->assign("oxProject_owners", $oxProject['owners']);
}

$smarty->assign("user", $user);
$smarty->assign("prof", $prof);
$smarty->assign("user_id", $user_id);
$smarty->assign("locales", $locales);
$smarty->assign("styles", $styles);
$smarty->assign("mod_categories", $mod_categories);
$smarty->assign("modules", $modules);
$smarty->assign("prefs", $prefs);
$smarty->assign("show_icone", $show_icone);
$smarty->assign("session_lifetime", $session_lifetime);
$smarty->assign("session_lifetime_enum", $session_lifetime_enum);

$smarty->display("edit_prefs.tpl");
