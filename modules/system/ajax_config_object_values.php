<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkAdmin();

$object_id           = CView::get("object_id", "num");
$object_configs_guid = CView::get("object_configs_guid", "str");

CView::checkin();

list($object_configs_class, $object_configs_id) = explode('-', $object_configs_guid);

$default_config = new $object_configs_class;
$default_config->valueDefaults();
$default_config->_default_specs_values = true;

$object = new $object_configs_class;
$object->object_id = $object_id;
$object->loadMatchingObject();
  
// Recherche s'il existe des valeurs par défaut
$where = array();
$where["object_id"]    = " IS NULL";
$default = new $object_configs_class;
$default->loadObject($where);
$default->_default_specs_values = true;

$object->_default_specs_values = ($object->_id && $default->_id) ? false : true;

if (!$object->_id && !$object_id) {
  $object = $default;
}

$fields = $object->getPlainFields();
unset($fields[$object->_spec->key]);
unset($fields["object_id"]);

if (!isset($object->_categories)) {
  $name = $object_id ? "$object_configs_class-$object_id" : $object_configs_class;
  $categories = array("$name" => array_keys($fields));
}
else {
  $categories = $object->_categories;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("object"        , $object);
$smarty->assign("default"       , $default);
$smarty->assign("default_config", $default_config);
$smarty->assign("fields"        , $fields);
$smarty->assign("categories"    , $categories);
$smarty->display("inc_config_object_values.tpl");
