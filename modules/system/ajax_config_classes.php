<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

CCanDo::checkAdmin();

$module = CValue::get("module");

$classes = array();
foreach (CModule::getClassesFor($module) as $_class) {
  $class = new $_class;
  $props = $class->_backProps;
  if (!array_key_exists("object_configs", $props)) {
    continue;
  }
  
  $classes[] = $class;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("classes", $classes);
$smarty->display("inc_config_classes.tpl");
