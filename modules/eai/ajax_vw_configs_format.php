<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

/**
 * Configs format
 */
CCanDo::checkRead();

$actor_guid  = CValue::getOrSession("actor_guid");
$config_guid = CValue::getOrSession("config_guid");

$format_config = CMbObject::loadFromGuid($config_guid);
$format_config->getConfigFields();

$actor = CMbObject::loadFromGuid($actor_guid);

$fields = $format_config->getPlainFields();
unset($fields[$format_config->_spec->key]);
if (!isset($format_config->_categories)) {
  $categories = array("" => array_keys($fields));
}
else {
  $categories = $format_config->_categories;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("format_config", $format_config);
$smarty->assign("fields"       , $fields);
$smarty->assign("categories"   , $categories);
$smarty->assign("actor"        , $actor);
$smarty->assign("actor_guid"   , $actor_guid);
$smarty->display("inc_configs_format.tpl");

