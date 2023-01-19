<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\System\CRedisServer;

CCanDo::checkAdmin();

$redis_server_id = CValue::post("redis_server_id");

$server = new CRedisServer();
$server->load($redis_server_id);

if ($server->electAsMaster()) {
  CAppUI::setMsg("CRedisServer-msg-Election successful");
}
else {
  CAppUI::setMsg("CRedisServer-msg-Election failed", UI_MSG_WARNING);
}

echo CAppUI::getMsg();
CApp::rip();