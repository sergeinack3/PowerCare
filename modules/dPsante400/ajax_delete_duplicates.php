<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::checkAdmin();

$object_id    = CValue::get("object_id");
$object_class = CValue::get("object_class");
$tag          = CValue::get("tag");
$idex_value   = CValue::get("id400");

$where = array(
  "object_id"    => "= '$object_id'",
  "object_class" => "= '$object_class'",
  "tag"          => "= '$tag'",
  "id400"        => "= '$idex_value'",
);

$idex  = new CIdSante400();
$idexs = $idex->loadList($where);

$survivor = reset($idexs)->_id;

foreach ($idexs as $_idex) {
  if ($_idex->_id != $survivor) {
    if ($msg = $_idex->delete()) {
      CAppUI::setMsg($msg, UI_MSG_WARNING);
    } else {
      CAppUI::setMsg("Identifiant supprimé", UI_MSG_OK);
    }
  }
}

echo CAppUI::getMsg();