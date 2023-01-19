<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\CConfigurationModelManager;

CCanDo::checkAdmin();

$constants     = CView::post('constant', 'str');
$schema        = CView::post('schema', 'str');
$context_guids = explode('|', CView::post('context_guids', 'str'));

CView::checkin();

$schema = preg_replace('@([\w ]+ / )@', "", $schema);
list($context_class, $parent_class) = explode(' ', $schema);

$constants = explode('|', $constants);

$configs_names   = array();
$props           = array();
$display_comment = array();
foreach ($constants as $constant) {
  $config_name         = 'dPpatients CConstantesMedicales ';
  $config_name_comment = 'dPpatients CConstantesMedicales ';
  /* Setting the config name depending on the selected schema */
  switch ($context_class) {
    case 'CBlocOperatoire':
      $config_name         .= 'selection_bloc';
      $config_name_comment .= 'comment_bloc';
      break;
    case 'CFunctions':
      $config_name         .= 'selection_cabinet';
      $config_name_comment .= 'comment_cabinet';
      break;
    case 'CService':
    default:
      $config_name         .= 'selection';
      $config_name_comment .= 'comment';
  }

  $config_name         .= " {$constant}";
  $config_name_comment .= " {$constant}";
  $configs_names[]     = $config_name;
  $props[]             = CConfigurationModelManager::getConfigSpec($config_name);
  $display_comment[]   = CConfigurationModelManager::getConfigSpec($config_name_comment);
}

$context = null;
if (count($context_guids)) {
  $guid = $context_guids[0];
  if ($guid != 'global') {
    $context = CMbObject::loadFromGuid($context_guids[0]);
  }
}

/* If the values of the selected contexts config's are not the same, we get the first object's config */
$configs        = CConfigurationModelManager::getAncestorsConfigs($schema, 'dPpatients', $configs_names, $context);
$config_comment = CConfigurationModelManager::getConfigs($schema, 'dPpatients', array($config_name_comment), $context);

$smarty = new CSmartyDP();
$smarty->assign('context_class', $context_class);
$smarty->assign('context_guids', $context_guids);
$smarty->assign('schema', $schema);
$smarty->assign('configs', $configs);
$smarty->assign('config_comment', $config_comment);
$smarty->assign('config_name_comment', $config_name_comment);

if (count($constants) == 1) {
  $smarty->assign('constant', $constants[0]);
  $smarty->assign('config_name', $configs_names[0]);
  $smarty->assign('props', $props[0]);
  $smarty->display('constantes_configs/inc_config.tpl');
}
else {
  $smarty->assign('constants', $constants);
  $smarty->assign('configs_names', $configs_names);
  $smarty->assign('props', $props);
  $smarty->display('constantes_configs/inc_configs.tpl');
}
