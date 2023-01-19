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
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\System\CConfigurationModelManager;

CCanDo::checkAdmin();

$constant      = CValue::post('constant');
$schema        = CValue::post('schema');
$context_guids = explode('|', CValue::post('context_guids'));

$schema = preg_replace(CConfigurationModelManager::PATTERN_SANITIZED_INHERIT, "", $schema);
list($context_class, $parent_class) = explode(' ', $schema);

$config_name = 'dPpatients CConstantesMedicales ';
/* Setting the config name depending on the selected schema */
switch ($context_class) {
  case 'CBlocOperatoire':
    $config_name .= 'alerts_bloc';
    break;
  case 'CFunctions':
    $config_name .= 'alerts_cabinet';
    break;
  case 'CService':
  default:
    $config_name .= 'alerts';
}

$config_name .= " {$constant}";
$props       = CConfigurationModelManager::getConfigSpec($config_name);

/* For update the configurable units in the params list */
$object = new CConstantesMedicales();
$object->updateFormFields();

/* If the values of the selected contexts config's are not the same, we get the first object's config */
$configs = CConfigurationModelManager::getAncestorsConfigs(
  $schema,
  'dPpatients',
  array($config_name),
  CMbObject::loadFromGuid($context_guids[0])
);

$smarty = new CSmartyDP();
$smarty->assign('context_class', $context_class);
$smarty->assign('context_guids', implode('|', $context_guids));
$smarty->assign('schema', $schema);
$smarty->assign('configs', $configs);
$smarty->assign('constant', $constant);
$smarty->assign('config_name', $config_name);
$smarty->assign('props', $props);
$smarty->display('constantes_configs/inc_alert.tpl');