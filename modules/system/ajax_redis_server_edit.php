<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\System\CRedisServer;

CCanDo::checkAdmin();

$redis_server_id = CValue::get("redis_server_id");

$redis_server = new CRedisServer();
$redis_server->load($redis_server_id);

if (!$redis_server->_id) {
  $redis_server->active = "0";
}

$smarty = new CSmartyDP();
$smarty->assign("server", $redis_server);
$smarty->display("inc_redis_server_edit.tpl");
