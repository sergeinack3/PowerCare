<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Object binding
use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CPlageRessourceCab;

$obj = new CPlageRessourceCab();
$obj->bind($_POST);

$del    = CValue::post("del", 0);
$repeat = min(CValue::post("_repeat", 0), 100);

if ($del) {
  // Suppression des plages
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
  
  CValue::setSession("plage_ressource_cab_id");

}
else {
  if ($obj->_id != 0) {
    // Modification des plages
    while ($repeat > 0) {
      if ($msg = $obj->store()) {
        CAppUI::setMsg("Plage non mise à jour", UI_MSG_ERROR);
        CAppUI::setMsg("Plage du $obj->date: $msg", UI_MSG_ERROR);
      }
      else {
        CAppUI::setMsg("Plage mise à jour", UI_MSG_OK);
      }
      $repeat -= $obj->becomeNext();
    }
  }
  else {
    // Creation des plages
    while ($repeat > 0) {     
      if ($msg = $obj->store()) {
        CAppUI::setMsg("Plage non créée", UI_MSG_ERROR);
        CAppUI::setMsg("Plage du $obj->date: $msg", UI_MSG_ERROR);
      }
      else {
        CAppUI::setMsg("Plage créée", UI_MSG_OK);
      }
      $repeat -= $obj->becomeNext();
    }
  }
}

echo CAppUI::getMsg();