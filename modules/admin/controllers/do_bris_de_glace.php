<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CBrisDeGlace;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

$bris               = new CBrisDeGlace();
$bris->date         = CMbDT::dateTime();
$bris->user_id      = CMediusers::get()->_id;
$bris->group_id     = CGroups::loadCurrent()->_id;
$bris->comment      = CValue::post("comment");
$bris->object_class = CValue::post("object_class");
$bris->object_id    = CValue::post("object_id");
$bris->role         = CValue::post('role');

if ($msg = $bris->store()) {
  CAppUI::setMsg($msg, UI_MSG_ERROR);
}
else {
  CAppUI::setMsg($bris->_class."-store", UI_MSG_OK);
  CAppUI::js("afterSuccessB2G()");
}

echo CAppUI::getMsg();

CApp::rip();