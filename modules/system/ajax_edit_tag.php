<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\System\CTag;

$object_guid  = CValue::get("object_guid");
$object_class = CValue::get("object_class");

$tag = new CTag();
if ($object_guid) {
  $tag = CStoredObject::loadFromGuid($object_guid);
}
else {
  if ($object_class) {
    $tag->object_class = $object_class;
  }
}

if ($tag->_id) {
  $tag->countRefItems();
  $tag->loadRefParent();
}

// smarty
$smarty = new CSmartyDP();
$smarty->assign("tag", $tag);
$smarty->display("inc_edit_tag.tpl");