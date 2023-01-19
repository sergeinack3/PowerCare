<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\Sessions\CSessionManager;

CCanDo::checkRead();

$session_id = CValue::get("session_id");
$timeout    = CValue::get("timeout", 30);

if (!$session_id) {
  global $rootName;
  $session_name = CSessionManager::forgeSessionName($rootName);
  $session_id   = CValue::cookie($session_name);
}

$ip_server = $_SERVER["SERVER_ADDR"];

$smarty = new CSmartyDP();

$smarty->assign("session_id", $session_id);
$smarty->assign("timeout", $timeout);
$smarty->assign("ip_server", $ip_server);

$smarty->display("routage.tpl");
