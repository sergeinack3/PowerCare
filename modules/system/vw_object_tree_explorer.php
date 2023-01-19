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
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

CCanDo::checkRead();

$object_class = CValue::get("object_class");
$object_guid  = CValue::get("object_guid");
$hide_tree    = CValue::get("hide_tree");
$tree_width   = CValue::get("tree_width");
$group_id     = CValue::get("group_id");
$columns      = CValue::get("col");

if (!$object_class && $object_guid) {
  $parts = explode("-", $object_guid);
  $object_class = $parts[0];
}

if (!$object_class || !is_subclass_of($object_class, CMbObject::class)) {
  CAppUI::stepAjax("Nom de classe invalide <strong>$object_class</strong>", UI_MSG_ERROR);
}

if (!$object_guid) {
  $object_guid = CValue::session("object_guid", "$object_class-0");
}

$smarty = new CSmartyDP("modules/system");
$smarty->assign("object_class", $object_class);
$smarty->assign("object_guid",  $object_guid);
$smarty->assign("columns",      $columns);
$smarty->assign("hide_tree",    $hide_tree);
$smarty->assign("tree_width",   $tree_width);
$smarty->assign("group_id",     $group_id);
$smarty->display("vw_object_tree_explorer.tpl");
