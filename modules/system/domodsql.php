<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSetup;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CUser;

CCanDo::checkAdmin();

$cmd      = CValue::get("cmd");
$mod_id   = CValue::get("mod_id");
$mod_name = CValue::get("mod_name");

// If it we come from the installer script
if ($cmd == "upgrade-core") {
    // we deactivate errors under error
    $old_er = error_reporting(E_ERROR);

    $module           = new CModule();
    $module->mod_type = "core";
    $list_modules     = $module->loadMatchingList($module->_spec->key);

    foreach ($list_modules as $module) {
        $setupClass = CSetup::getCSetupClass($module->mod_name);

        /** @var CSetup $setup */
        $setup = new $setupClass;
        if ($setup->upgrade($module, true)) {
            if ($setup->mod_version == $module->mod_version) {
                CAppUI::setMsg(
                    "Installation de '%s' à la version %s",
                    UI_MSG_OK,
                    $module->mod_name,
                    $setup->mod_version
                );
            } else {
                CAppUI::setMsg(
                    "Installation de '%s' à la version %s sur %s",
                    UI_MSG_WARNING,
                    $module->mod_name,
                    $module->mod_version,
                    $setup->mod_version
                );
            }
        } else {
            CAppUI::setMsg("Module '%s' non mis à jour", UI_MSG_WARNING, $module->mod_name);
        }

        CModule::loadModules(); // To force dependency re-evaluation
    }

    if (isset($_SESSION["_pass_deferred"]) && CAppUI::$instance->user_id == 1) {
        $user = new CUser();
        $user->load(1);
        $user->_user_password = $_SESSION["_pass_deferred"];
        $user->store();
        unset($_SESSION["_pass_deferred"]);
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
            $module->remove();
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
                    "Installation de '%s' à la version %s sur %s",
                    UI_MSG_WARNING,
                    $module->mod_name,
                    $module->mod_version,
                    $setup->mod_version
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
                    "Installation de '%s' à la version %s sur %s",
                    UI_MSG_WARNING,
                    $module->mod_name,
                    $module->mod_version,
                    $setup->mod_version
                );
            }
        } else {
            CAppUI::setMsg("Module '%s' non mis à jour", UI_MSG_WARNING, $module->mod_name);
        }

        // In case the setup has added some user prefs
        CAppUI::buildPrefs();
        break;

    case "configure":
        if (!$setup->configure()) { //returns true if configure succeeded
            CAppUI::setMsg("CModule-msg-config-failed", UI_MSG_ERROR);
        }
        break;

    default:
        CAppUI::setMsg("Unknown Command", UI_MSG_ERROR);
}

$cache = Cache::getCache(Cache::OUTER);
$cache->delete("modules");

// en cas d'un appel en Ajax (mobile)
if (CValue::get("ajax")) {
    echo CAppUI::getMsg();
}

CAppUI::redirect("m=system&tab=view_modules");
