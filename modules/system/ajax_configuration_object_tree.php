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
$uid     = CValue::get("uid");
$mode    = CValue::get('mode');

$mode = ($mode) ?: CConfigurationModelManager::getConfigurationMode();

if ($inherit === 'static') {
  $object_tree = [];
}
else {
  $object_tree = CConfigurationModelManager::getObjectTree($inherit);
}

$smarty = new CSmartyDP();
$smarty->assign("object_tree", $object_tree);
$smarty->assign("inherit", $inherit);
$smarty->assign("uid", $uid);
$smarty->assign('mode', $mode);
$smarty->display("inc_select_configuration_object.tpl");