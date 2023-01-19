<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\Module\Requirements\CRequirementsDummy;
use Ox\Core\Module\Requirements\CRequirementsManager;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkRead();

$mod_name = CView::get("mod_name", "str");
$group_id = CView::get("group_id", "ref class|CGroups");
CView::checkin();

$establishment = !$group_id ? CGroups::loadCurrent() : (new CGroups())->load($group_id);

$smarty = new CSmartyDP();
$module = CModule::getInstalled($mod_name);
if (!$module) {
  $smarty->assign("tpl_error", CAppUI::tr("common-error-An error occurred"));
  $smarty->display("requirements/vw_requirements");
  CApp::rip();
}

// get requirements
$requirements = $module->getRequirements();
$requirements_class = get_class($requirements);
$resume = [];
foreach (CGroups::loadGroups() as $group) {
  /** @var CRequirementsManager $requirements */
  $requirements = new $requirements_class();
  $requirements->checkRequirements($group);
  $errors = $requirements->countErrors();
  $total  = $requirements->count();

  $resume[$group->text] = [
    'errors' => $errors,
    'total'  => $total,
    'group_id'  => $group->_id,
  ];
}

// check requirements
$requirements = new $requirements_class();
$requirements->checkRequirements($establishment);

// meta data
$description  = $requirements->getDescription();
$tabs_failed   = [];
$groups_failed = [];
foreach ($requirements as $item) {
  if (!$item->isCheck()) {
    $tabs_failed[]   = $item->getTab();
    $groups_failed[] = $item->getTab() . '_' . $item->getGroup();
  }
}

$tabs = $requirements->getTabs();
if ($description && $description->hasDescription()) {
  array_unshift($tabs, "description");
}

$smarty->assign("nb_requirements", count($requirements));
$smarty->assign("nb_errors", $requirements->countErrors());
$smarty->assign("tabs_failed", $tabs_failed);
$smarty->assign("groups_failed", $groups_failed);
$smarty->assign("description", $description ??  null);
$smarty->assign("requirements_tabs", $requirements->serialize());
$smarty->assign("tabs", $tabs);
$smarty->assign("resume", $resume);
$smarty->assign("actual_group", $establishment->_id);
$smarty->assign("mod_name", $mod_name);
$smarty->display("requirements/vw_requirements");
