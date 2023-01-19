<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Core\Sessions\CSessionHandler;

CCanDo::checkAdmin();

$session_id = CValue::post('session_id');

($session_id && CSessionHandler::destroy($session_id)) ?
  CAppUI::setMsg('CSession-msg-Session destroyed', UI_MSG_OK) :
  CAppUI::setMsg('CSession-msg-Session destruction error', UI_MSG_ERROR);

echo CAppUI::getMsg();
CApp::rip();