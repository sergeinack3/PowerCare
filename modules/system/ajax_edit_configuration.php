<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\System\CConfigurationModelManager;

$inherit = CValue::get("inherit");
$module  = CValue::get("module");
$mode = CValue::get('mode');

if (!is_array($inherit)) {
  $inherit = array($inherit);
}

$all_inherits = array_keys(CConfigurationModelManager::_getModel($module, $inherit));

$mode = ($mode) ?: CConfigurationModelManager::getConfigurationMode();

$smarty = new CSmartyDP();
$smarty->assign("module", $module);
$smarty->assign("inherit", $inherit);
$smarty->assign("all_inherits", $all_inherits);
$smarty->assign('mode', $mode);
$smarty->display("inc_edit_configuration.tpl");