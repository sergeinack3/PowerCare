<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSetup;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;

CCanDo::checkAdmin();

$cmd      = CValue::post("cmd");
$mod_id   = CValue::post("mod_id");
$mod_name = CValue::post("mod_name");
$mobile   = CValue::post("mobile", 0);

// If it we come from the installer script
if ($cmd == "upgrade-core") {
    // we deactivate errors under error
    $old_er = error_reporting(E_ERROR);

    $module           = new CModule();
    $module->mod_type = "core";

    /** @var CModule[] $list_modules */
    $list_modules = $module->loadMatchingList();

    foreach ($list_modules as $module) {
        $setupClass = CSetup::getCSetupClass($module->mod_name);

        /** @var CSetup $setup */
        $setup = new $setupClass;
        if ($setup->upgrade($module)) {
            if ($setup->mod_version == $module->mod_version) {
                CAppUI::setMsg(
                    "Installation de '%s' à la version %s",
                    UI_MSG_OK,
                    $module->mod_name,
                    $setup->mod_version
                );
            } else {
                CAppUI::setMsg(
                    "Installation de '$module->mod_name' à la version $module->mod_version sur $setup->mod_version",
                    UI_MSG_WARNING,
                    true
                );
            }
        } else {
            CAppUI::setMsg("Module '%s' non mis à jour", UI_MSG_WARNING, $module->mod_name);
        }
    }

    // In case the setup has added some user prefs
    CAppUI::buildPrefs();

    error_reporting($old_er);

    CAppUI::redirect();
}

$module = new CModule();
if ($mod_id) {
    $module->load($mod_id);
    $module->checkModuleFiles();
} else {
    $module->mod_version = "0.0";
    $module->mod_name    = $mod_name;
}

if (!$setupclass = CSetup::getCSetupClass($module->mod_name)) {
    if ($module->mod_type != "core" && !$module->_files_missing) {
        CAppUI::setMsg("CModule-msg-no-setup", UI_MSG_ERROR);
        CAppUI::redirect();
    }
}

if ($module->mod_type == "core" && in_array($cmd, ["remove", "install", "toggle"])) {
    CAppUI::setMsg("Core modules can't be uninstalled or disactivated", UI_MSG_ERROR);
    CAppUI::redirect();
}

if (!$module->_files_missing) {
    $setup = new $setupclass();
}

switch ($cmd) {
    case "toggle":
        // just toggle the active state of the table entry
        $module->mod_active = 1 - $module->mod_active;
        $module->store();
        CAppUI::setMsg("CModule-msg-state-changed", UI_MSG_OK);
        break;

    case "toggleMenu":
        // just toggle the active state of the table entry
        $module->mod_ui_active = 1 - $module->mod_ui_active;
        $module->store();
        CAppUI::setMsg("CModule-msg-state-changed", UI_MSG_OK);
        break;

    case "remove":
        $success = ($module->_files_missing ? true : $setup->remove());

        if ($success !== null) {
            $module->delete();
            CAppUI::setMsg("CModule-msg-removed", $success ? UI_MSG_OK : UI_MSG_ERROR, true);
        }
        break;

    case "install":
        if ($setup->upgrade($module)) {
            if ($setup->mod_version == $module->mod_version) {
                CAppUI::setMsg(
                    "Installation de '%s' à la version %s",
                    UI_MSG_OK,
                    $module->mod_name,
                    $setup->mod_version
                );
            } else {
                CAppUI::setMsg(
                    "Installation de '$module->mod_name' à la version $module->mod_version sur $setup->mod_version",
                    UI_MSG_WARNING,
                    true
                );
            }
        } else {
            CAppUI::setMsg("Module '$module->mod_name' non installé", UI_MSG_ERROR, true);
        }

        // In case the setup has added some user prefs
        CAppUI::buildPrefs();
        break;

    case "upgrade":
        if ($setup->upgrade($module)) {
            if ($setup->mod_version == $module->mod_version) {
                CAppUI::setMsg(
                    "Installation de '%s' à la version %s",
                    UI_MSG_OK,
                    $module->mod_name,
                    $setup->mod_version
                );
            } else {
                CAppUI::setMsg(
                    "Installation de '$module->mod_name' à la version $module->mod_version sur $setup->mod_version",
                    UI_MSG_WARNING,
                    true
                );
            }
        } else {
            CAppUI::setMsg("Module '%s' non mis à jour", UI_MSG_WARNING, $module->mod_name);
        }

        // In case the setup has added some user prefs
        CAppUI::buildPrefs();
        break;

    default:
        CAppUI::setMsg("Unknown Command", UI_MSG_ERROR);
}

// en cas d'un appel en Ajax (mobile)
if (CValue::get("ajax") || CValue::post("ajax")) {
    echo CAppUI::getMsg();

    CApp::rip();
}

$cache = Cache::getCache(Cache::OUTER);

$cache->delete("modules");

if (!$mobile) {
    CAppUI::redirect("m=system&tab=view_modules");
}

