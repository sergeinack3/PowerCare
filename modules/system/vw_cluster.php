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
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\System\CHeartbeat;

CCanDo::checkRead();

if (!$ds = CSQLDataSource::get("cluster")) {
  CAppUI::stepMessage(UI_MSG_ERROR, "Unable to connect to cluster database");
  return;
}

$heartbeat = new CHeartbeat();
$heartbeats = $heartbeat->loadList();

$smarty = new CSmartyDP();
$smarty->assign("heartbeats", $heartbeats);
$smarty->display("vw_cluster.tpl");
