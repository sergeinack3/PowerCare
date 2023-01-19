<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\System\CTag;

$tag_id       = CValue::post('tag_id');
$object_class = CValue::post("object_class");
if (!$object_class) {
  CAppUI::stepAjax("Pas de classe", UI_MSG_ERROR);
}

$tag = new CTag();
$where = array();
$where["tag.object_class"] = " = '$object_class'";
if ($tag_id) {
  $where["tag.tag_id"] = " = '$tag_id'";
}

/** @var CTag[] $tags */
$tags = $tag->loadList($where);

$nb = 0;
foreach ($tags as $_tag) {
  if (!$_tag->countRefItems()) {
    if ($msg = $_tag->delete()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
      continue;
    }
    $nb++;
  }
}

CAppUI::setMsg("%d tag(s) supprimé(s)", UI_MSG_OK, $nb);

echo CAppUI::getMsg();