<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\System\CRedisServer;

CCanDo::checkAdmin();

$redis_server = new CRedisServer();
$ds = $redis_server->getDS();

$where = array(
  "instance_role" => $ds->prepare("= ?", CAppUI::conf("instance_role")),
);

/** @var CRedisServer[] $servers */
$servers = $redis_server->loadList($where, "host, port");

foreach ($servers as $_server) {
  if ($_server->checkConnectivity()) {
    $_server->getInformation();
    $_server->getKeysInformation();
  }
}

$servers_in_config = CRedisServer::getConfigAdresses();

$smarty = new CSmartyDP();
$smarty->assign("servers", $servers);
$smarty->assign("redis_server", $redis_server);
$smarty->assign("servers_in_config", $servers_in_config);
$smarty->display("vw_redis_servers.tpl");
