<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

//Récupération des paramètres
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\PlanningOp\CSejour;

$sejours  = CValue::post("sejours");
$callback = CValue::post("callback");
$mode_sortie = CValue::post("mode_sortie");
$mode_sortie_id = CValue::post("mode_sortie_id");

$sejours = json_decode(utf8_encode(stripslashes($sejours)), true);
foreach ($sejours as $sejour_id => $_sejour) {
  $_checked = $_sejour["_checked"];
  if (!$_checked) {
    continue;
  }
  $sejour = new CSejour();
  $sejour->load($sejour_id);
  $sejour->sortie_reelle = $sejour->sortie_prevue;

  if ($mode_sortie_id) {
    $sejour->mode_sortie_id = $mode_sortie_id;
  }
  else {
    $sejour->mode_sortie = $mode_sortie ? $mode_sortie : "normal";
  }

  if ($msg = $sejour->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
  }
}

if ($callback) {
  CAppUI::callbackAjax($callback);
}

echo CAppUI::getMsg();
CApp::rip();