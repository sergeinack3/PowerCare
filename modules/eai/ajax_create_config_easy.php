<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\CInteropReceiver;

$actor_guid = CView::get("actor_guid", "str");
CView::checkin();

/** @var CInteropReceiver $actor */
$actor = CMbObject::loadFromGuid($actor_guid);

if (!$actor->_id) {
  CAppUI::stepAjax("CInteropActor-back-domains.empty", UI_MSG_ERROR);
}

$actor->loadRefsExchangesSources();
$actor->loadRefObjectConfigs();

list($object_configs_class, $object_configs_id) = explode('-', $actor->_ref_object_configs->_guid);

$object_id = $actor->_id;

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

$messages_supported = $actor->getMessagesSupportedSort($actor);

$source = false;
foreach ($actor->_ref_msg_supported_family as $_msg_supported) {
  $_source = $actor->_ref_exchanges_sources[$_msg_supported];
  if ($_source->_id) {
    $source = true;
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("actor"         , $actor);
$smarty->assign("source"        , $source);
$smarty->assign("object"        , $object);
$smarty->assign("default"       , $default);
$smarty->assign("default_config", $default_config);
$smarty->assign("fields"        , $fields);
$smarty->assign("categories"    , $categories);
$smarty->assign("messages_supported", $actor->getMessagesSupportedSort($actor));
$smarty->assign("tabs_menu"    , "configs_receiver");
$smarty->display("inc_choose_config.tpl");


