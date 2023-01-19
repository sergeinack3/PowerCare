<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\CConfigurationModelManager;
use Ox\Mediboard\System\CConfigurationStrategy;
use Ox\Mediboard\System\ConfigurationException;
use Ox\Mediboard\System\ConfigurationManager;

CCanDo::check();

$mode        = CView::get('mode', 'str');
$object_guid = CView::get("object_guid", "str");
$module      = CView::get("module", "str");
$inherit     = CView::get("inherit", "str");
$uid         = CView::get("uid", "str");

CView::checkin();

$strategy = new CConfigurationStrategy(CConfigurationModelManager::getStrategy($mode));

$object = null;

$static_configs = false;

if ($module && CModule::exists($module)) {
  $can = CModule::getCanDo($module);
  $can->needsAdmin();
}

if ($inherit === 'static') {
  $static_configs = true;

  if (!CAppUI::$user->isAdmin()) {
    global $can;
    $can->denied();

    return;
  }

  $manager          = ConfigurationManager::get();
  $configs          = [];
  $ancestor_configs = [];

  try {
    $model = $manager->buildModel($module);

    $configs = [];
    foreach ($model as $_key => $_model) {
      $configs["{$module} {$_key}"] = $_model;
    }

    $ancestor_configs = $manager->getAncestorsConfigs($module, array_keys($configs), $strategy->getStrategy());
  }
  catch (ConfigurationException $e) {
    // Module has no static configuration
  }

  $features_global     = [];
  $features            = [];
  $alt_global_features = [];
  $alt_features        = [];
}
else {
  if ($object_guid && $object_guid !== "global") {
    $object  = CMbObject::loadFromGuid($object_guid);
    $configs = CConfigurationModelManager::getClassConfigs($object->_class, $module, $inherit);
  }
  else {
    if (!CAppUI::$user->isAdmin()) {
      global $can;
      $can->denied();

      return;
    }

    $model = CConfigurationModelManager::getModuleConfigs($module, $inherit);

    $configs = [];
    foreach ($model as $_model) {
      $configs = array_merge($configs, $_model);
    }
  }

  $ancestor_configs = CConfigurationModelManager::getAncestorsConfigs($inherit, $module, array_keys($configs), $object, $strategy->getStrategy());

  $obj_split = explode('-', $object_guid);
  $class     = (isset($obj_split[0])) ? $obj_split[0] : null;
  $id        = (isset($obj_split[1])) ? $obj_split[1] : null;

  $features_global = $strategy->getNullStoredConfigurations($module, CConfigurationModelManager::getConfigurationSpec(), null, null, false);

  $features = [];
  if ($class && $id) {
    $features = $strategy->getNullStoredConfigurations($module, CConfigurationModelManager::getConfigurationSpec(), $class, $id, false);
  }

  // Indexed by feature, for searching purposes
  $features_global = array_flip($features_global);
  $features        = array_flip($features);

  $alt_global_features = $strategy->getAltFeatures($module, CConfigurationModelManager::getConfigurationSpec(), null, null);

  $alt_features = [];
  if ($class && $id) {
    $alt_features = $strategy->getAltFeatures($module, CConfigurationModelManager::getConfigurationSpec(), $class, $id);
  }

  // Indexed by feature, for searching purposes
  $alt_global_features = array_flip($alt_global_features);
  $alt_features        = array_flip($alt_features);
}

$smarty = new CSmartyDP();
$smarty->assign('mode', $mode);
$smarty->assign('ancestor_configs', $ancestor_configs);
$smarty->assign("object_guid", $object_guid);
$smarty->assign('configs', $configs);
$smarty->assign('inherit', $inherit);
$smarty->assign('uid', $uid);
$smarty->assign('features', $features);
$smarty->assign('features_global', $features_global);
$smarty->assign('alt_global_features', $alt_global_features);
$smarty->assign('alt_features', $alt_features);
$smarty->assign('static_configs', $static_configs);
$smarty->display('inc_edit_object_config');
