<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSocketBasedServer;

CCanDo::checkAdmin();

$processes = CSocketBasedServer::getPsStatus();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("processes", $processes);
$smarty->display("vw_servers_socket.tpl");

