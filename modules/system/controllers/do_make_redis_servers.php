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
use Ox\Mediboard\System\CRedisServer;

CCanDo::checkAdmin();

$addresses = CRedisServer::getConfigAdresses();

$master = null;

foreach ($addresses as $_i => $_address) {
  $_server = new CRedisServer();

  $_server->host          = $_address[0];
  $_server->port          = $_address[1];
  $_server->instance_role = CAppUI::conf("instance_role");
  $_server->active        = 1;

  if ($_i == 0) {
    $_server->is_master = 1;
    $master = $_server;
  }

  if ($msg = $_server->store()) {
    CAppUI::setMsg($msg, UI_MSG_WARNING);
  }
  else {
    CAppUI::setMsg("CRedisServer-msg-create", UI_MSG_OK);
  }
}

if ($master) {
  $master->electAsMaster();
}

echo CAppUI::getMsg();
CApp::rip();