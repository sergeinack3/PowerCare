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
      CAppUI::setMsg("Plage non trouv�e", UI_MSG_ERROR);
    }
    else {
      if ($msg = $obj->delete()) {
        CAppUI::setMsg("Plage non supprim�e", UI_MSG_ERROR);
        CAppUI::setMsg("Plage du $obj->date: $msg", UI_MSG_ERROR);
      }
      else {
        CAppUI::setMsg("Plage supprim�e", UI_MSG_OK);
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
        CAppUI::setMsg("Plage non mise � jour", UI_MSG_ERROR);
        CAppUI::setMsg("Plage du $obj->date: $msg", UI_MSG_ERROR);
      }
      else {
        CAppUI::setMsg("Plage mise � jour", UI_MSG_OK);
      }
      $repeat -= $obj->becomeNext();
    }
  }
  else {
    // Creation des plages
    while ($repeat > 0) {     
      if ($msg = $obj->store()) {
        CAppUI::setMsg("Plage non cr��e", UI_MSG_ERROR);
        CAppUI::setMsg("Plage du $obj->date: $msg", UI_MSG_ERROR);
      }
      else {
        CAppUI::setMsg("Plage cr��e", UI_MSG_OK);
      }
      $repeat -= $obj->becomeNext();
    }
  }
}

echo CAppUI::getMsg();