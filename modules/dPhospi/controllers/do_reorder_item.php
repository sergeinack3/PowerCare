<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CItemPrestation;

CCanDo::checkEdit();
$item_id_move = CView::post("item_id_move", "ref class|CItemPrestation");
$direction    = CView::post("direction", "enum list|up|down");
CView::checkin();

$item = new CItemPrestation();
$item->load($item_id_move);

switch ($direction) {
  case "up":
    $item->rank--;
    break;
  case "down":
    $item->rank++;
}

$item_to_move               = new CItemPrestation();
$item_to_move->object_class = $item->object_class;
$item_to_move->object_id    = $item->object_id;
$item_to_move->rank         = $item->rank;
$item_to_move->loadMatchingObject();

if ($item_to_move->_id) {
  $direction === "up" ? $item_to_move->rank++ : $item_to_move->rank--;
  $item_to_move->store();
}

$msg = $item->store();

CAppUI::setMsg($msg ?: CAppUI::tr("CItemPrestation-msg-modify"), $msg ? UI_MSG_ERROR : UI_MSG_OK);

$prestation = new $item->object_class;
$prestation->load($item->object_id);

$items = $prestation->loadBackRefs("items", "rank");

$i = 1;
foreach ($items as $item) {
  $item->rank = $i++;
  $msg        = $item->store();

  CAppUI::setMsg($msg ?: CAppUI::tr("CItemPrestation-msg-modify"), $msg ? UI_MSG_ERROR : UI_MSG_OK);
}

echo CAppUI::getMsg();