<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSetup;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\Module\Requirements\CRequirementsManager;

CCanDo::checkAdmin();

$mod_id = CView::get("mod_id", "ref class|CModule");

CView::checkin();

$module = new CModule();
$module->load($mod_id);
$module->checkModuleFiles();

// Check requirements
if ($module->mod_active) {
  /** @var CRequirementsManager $manager */
  try {
    $manager = $module->getRequirements();
    if ($manager) {
      $manager->checkRequirements();
      $module->_requirements        = count($manager); //count($manager);
      $module->_requirements_failed = $manager->countErrors();
    }
  }
  catch (Exception $e) {
    CApp::log($e->getMessage(), $e);
  }
}

$setupclass = CSetup::getCSetupClass($module->mod_name);
$setup = new $setupclass;
$module->compareToSetup($setup);

$smarty = new CSmartyDP();
$smarty->assign("_mb_module", $module);
$smarty->assign("installed" , true);
$smarty->assign("module_id" , $mod_id);
$smarty->display("inc_module.tpl");
