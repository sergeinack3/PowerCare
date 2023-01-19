<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\Hospi\CItemLiaison;

$date      = CValue::post("date");
$sejour_id = CValue::post("sejour_id");

if (!$date) {
  CApp::rip();
}

$liaison            = new CItemLiaison();
$liaison->date      = $date;
$liaison->sejour_id = $sejour_id;

foreach ($liaison->loadMatchingList() as $_liaison) {
  $msg = $_liaison->delete();
  CAppUI::setMsg($msg ?: 'CItemLiaison-msg-delete', $msg ? UI_MSG_ERROR : UI_MSG_OK);
}
