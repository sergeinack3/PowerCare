<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CDailySalleOccupation;

$date     = CValue::post("date");
$salle_id = CValue::post("salle_id");

$salle_mine = new CDailySalleOccupation();
$salle_mine->mine($salle_id, $date);
if ($msg = $salle_mine->store()) {
  CAppUI::setMsg($msg, UI_MSG_ERROR);
}
else {
  CAppUI::setMsg("CDailySalleOccupation-msg-create");
}

echo CAppUI::getMsg();
CApp::rip();