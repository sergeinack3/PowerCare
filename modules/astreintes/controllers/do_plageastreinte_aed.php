<?php
/**
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Object binding
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CView;
use Ox\Mediboard\Astreintes\CPlageAstreinte;

$obj = new CPlageAstreinte();
$obj->bind($_POST);

$del    = CView::post("del", "bool default|0");
$repeat = CView::post("_repeat_week", "num default|1");

if ($del) {
  // Supression des plages
  $obj->load();
  while ($repeat > 0) {
    if (!$obj->_id) {
      CAppUI::setMsg("Plage non trouvée", UI_MSG_ERROR);
    }
    else {
      if ($msg = $obj->delete()) {
        CAppUI::setMsg("Plage non supprimée", UI_MSG_ERROR);
        CAppUI::setMsg("Plage du $obj->date: $msg", UI_MSG_ERROR);
      }
      else {
        CAppUI::setMsg("Plage supprimée", UI_MSG_OK);
      }
    }
    $repeat -= $obj->becomeNext();
  }
  CView::setSession("plage_id");
}
else {
  //Modification des plages
  if ($obj->_id != 0) {
    $oldObj = new CPlageAstreinte();
    $oldObj->load($obj->_id);
    $user_id = $oldObj->user_id;

    while ($repeat > 0) {

      if ($obj->_id) {
        if ($msg = $obj->store()) {
          CAppUI::setMsg("Plage non mise à jour", UI_MSG_ERROR);
          CAppUI::setMsg("Plage du $obj->start au $obj->end: $msg", UI_MSG_ERROR);
        }
        else {
          CAppUI::setMsg("Plage mise à jour", UI_MSG_OK);
        }
      }

      $repeat -= $obj->becomeNext($user_id);
    }
  }
  else {
    // Création des plages
    while ($repeat > 0) {

      if ($msg = $obj->store()) {
        CAppUI::setMsg("Plage non créée", UI_MSG_ERROR);
        CAppUI::setMsg("Plage du $obj->start au $obj->end: $msg", UI_MSG_ERROR);
      }
      else {
        CAppUI::setMsg("Plage créée", UI_MSG_OK);
      }

      $repeat -= $obj->becomeNext();
    }
  }
}

CView::checkin();

if ($ajax) {
  echo CAppUI::getMsg();
  CApp::rip();
}

CAppUI::redirect("m=$m");
