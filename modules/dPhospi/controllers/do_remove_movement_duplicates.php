<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CRequest;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Hospi\CMovement;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\System\CMergeLog;

CCanDo::checkAdmin();
$original_trigger_code = CValue::post("original_trigger_code");
$do_it                 = CValue::post("do_it");
$count                 = CValue::post("count", 10);
$auto                  = CValue::post("auto");

$request = new CRequest;
$request
  ->addSelect(array(
    "CAST(GROUP_CONCAT(movement_id) AS CHAR) AS ids",
    "original_trigger_code",
    "start_of_movement",
    "sejour_id",
  ))
  ->addTable("movement")
  ->addWhere(array(
    "original_trigger_code" => "= '$original_trigger_code'",
  ))
  ->addGroup(array(
    "original_trigger_code",
    "start_of_movement",
    "sejour_id",
  ))
  ->addHaving("COUNT(movement_id) > 1");

if ($do_it) {
  $request->setLimit($count);
}

$mov   = new CMovement;
$query = $request->makeSelect();
$list  = $mov->_spec->ds->loadList($query);

if (!$do_it) {
  CAppUI::setMsg(count($list) . " doublons à traiter");
}
else {
  foreach ($list as $_mvt) {
    $ids = explode(",", $_mvt["ids"]);
    sort($ids); // IMPORTANT, must use the first movement created as a reference

    $first = new CMovement;
    $first->load($ids[0]);

    $second = new CMovement;
    $second->load($ids[1]);
    $tag = CIdSante400::getMatch($second->_class, $second->getTagMovement(), null, $second->_id);

    if ($tag->_id) {
      $tag->tag = "trash_$tag->tag";
      $tag->store();
    }
    else {
      CAppUI::setMsg("Aucun tag sur mouvement #$second->_id");
    }

    $merge_log = CMergeLog::logStart(CUser::get()->_id, $first, [$second->_id => $second], false);

    try {
        $first->merge(array($second->_id => $second), false, $merge_log);
        $merge_log->logEnd();

        CAppUI::setMsg("Mouvements fusionnés");
    } catch (Throwable $t) {
        $merge_log->logFromThrowable($t);
        CAppUI::setMsg($t->getMessage(), UI_MSG_WARNING);
    }
  }

  if ($auto && count($list)) {
    CAppUI::js("removeMovementDuplicates()");
  }
}

echo CAppUI::getMsg();
CApp::rip();
