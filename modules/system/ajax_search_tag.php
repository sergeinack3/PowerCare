<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\System\CTag;

$tag_name     = CValue::get("name");
$no_item      = CValue::get("no_item");
$parent_id    = CValue::get("parent_id");
$is_child     = CValue::get("is_child");
$page         = CValue::get("page", "0");
$object_class = CValue::get("object_class");
$limit        = 15;

$tag_parent = new CTag();
if ($parent_id) {
  $tag_parent->load($parent_id);
}

// $tag
$tag = new CTag();
$tag->canDo();
$order = "name";
$where = array();
$where["object_class"] = " = '$object_class'";
if ($is_child) {
  $where["parent_id"] = " IS NOT NULL";
  $order = "parent_id, name";
}
if ($parent_id) {
  $where["parent_id"] = " = '$parent_id'";
}
if ($tag_name) {
  $where["name"] = " LIKE '%$tag_name%'";
}

/** @var CTag[] $tags */
$total = $tag->countList($where);
$tags  = $tag->loadList($where, $order, "$page, $limit");
foreach ($tags as $key => $_tag) {
  $_tag->countRefItems();
  $_tag->loadRefParent();
}

// smarty
$smarty = new CSmartyDP();
$smarty->assign("tags"      , $tags);
$smarty->assign("tag"       , $tag);
$smarty->assign("tag_parent", $tag_parent);
$smarty->assign("page"      , $page);
$smarty->assign("limit"     , $limit);
$smarty->assign("total"     , $total);
$smarty->display("inc_list_tags.tpl");