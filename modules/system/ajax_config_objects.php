<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

CCanDo::checkAdmin();

$classname = CValue::get("classname");

$objects = array(
  "default" => null,
  "objects" => array(),
);

$class = new $classname;
$class->loadRefObjectConfigs();

// Détermine si l'on a pas une config par défaut enregistrée
$object_class = $classname."Config";
$where = array();
$where["object_id"]    = " IS NULL";
$default = new $object_class;
$default->loadObject($where);
if ($default->_id) {
  $class->_ref_object_configs = $default;
}

$objects["default"] = $class;

foreach ($class->loadList() as $_object) {
  $_object->loadRefObjectConfigs();
  $objects["objects"][] = $_object;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("objects", $objects);
$smarty->display("inc_config_objects.tpl");
