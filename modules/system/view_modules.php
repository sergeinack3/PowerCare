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
use Ox\Core\CMbString;
use Ox\Core\CSQLDataSource;
use Ox\Core\Module\CModule;
use Ox\Core\Composer\CComposer;
use Ox\Core\CSetup;
use Ox\Core\CSmartyDP;
use Ox\Core\Libraries\CLibrary;
use Ox\Core\Module\Requirements\CRequirementsManager;

CCanDo::checkAdmin();

CModule::loadModules(true);

$setupClasses = CApp::getChildClasses(CSetup::class);

$mbmodules = [
    "notInstalled" => [],
    "installed"    => [],
];

$coreModules = [];
$upgradable  = false;

foreach ($setupClasses as $setupClass) {
    if (!class_exists($setupClass)) {
        continue;
    }

    $setup = new $setupClass();

    $mbmodule = new CModule();
    $mbmodule->compareToSetup($setup);
    $mbmodule->checkModuleFiles();
    $mbmodule->getUpdateMessages($setup, true);
    $mbmodule->updateFormFields();

    if ($mbmodule->_id) {
        $mbmodules["installed"][$mbmodule->mod_name] = $mbmodule;
        if ($mbmodule->_upgradable) {
            $upgradable = true;
        }
    } else {
        $mbmodules["notInstalled"][$mbmodule->mod_name] = $mbmodule;
    }

    if ($mbmodule->mod_type == "core" && $mbmodule->_upgradable) {
        $coreModules[$mbmodule->mod_name] = $mbmodule;
    }
}

foreach ($mbmodules as $typeModules) {
    /** @var CModule $module */
    foreach ($typeModules as $module) {
        // Check dependency
        foreach ($module->_dependencies as $version => $dependencies) {
            foreach ($dependencies as $dependency) {
                $installed = $mbmodules["installed"];

                $dependency->verified =
                    isset($installed[$dependency->module]) &&
                    $installed[$dependency->module]->mod_version >= $dependency->revision;

                if (!$dependency->verified) {
                    $module->_dependencies_not_verified++;
                }
            }
        }

        if (!$module->mod_active) {
            continue;
        }

        // Check requirements
        /** @var CRequirementsManager $manager */
        try {
            $manager = $module->getRequirements();
            if ($manager) {
                $manager->checkRequirements();
                $module->_requirements        = count($manager);
                $module->_requirements_failed = $manager->countErrors();
            }
        } catch (Exception $e) {
            CApp::log($e->getMessage(), $e);
        }
    }
}


// Ajout des modules installés dont les fichiers ne sont pas présents
if (count(CModule::$absent)) {
    $mbmodules["installed"] += CModule::$absent;
}

$sorter = function (CModule $mod_1, CModule $mod_2): int {
    return strcasecmp(CMbString::removeDiacritics($mod_1->_view), CMbString::removeDiacritics($mod_2->_view));
};

uasort($mbmodules['installed'], $sorter);

uasort($mbmodules['notInstalled'], $sorter);

$smarty = new CSmartyDP();
$smarty->assign("upgradable", $upgradable);
$smarty->assign("mbmodules", $mbmodules);
$smarty->assign("coreModules", $coreModules);
$smarty->assign("php_version", PHP_VERSION);
$smarty->display("view_modules.tpl");
