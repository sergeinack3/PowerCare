<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

CCanDo::checkRead();

$locales = CAppUI::flattenCachedLocales(CAppUI::$lang);

$tabs = [];
foreach ($modules = CModule::getInstalled() as $module) {
    $module->registerTabs();
    foreach ($module->_tabs as $_group => $_tabs) {
        foreach ($_tabs as $tab) {
            $tabs[$tab]["name"]   = "mod-$module->mod_name-tab-$tab";
            $tabs[$tab]["locale"] = CValue::read($locales, $tabs[$tab]["name"]);
        }
    }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("module", $modules);
$smarty->assign("tabs", $tabs);

$smarty->display("mnt_module_actions.tpl");
