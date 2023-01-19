<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Interop\Eai\CHTTPTunnelObject;

$http_tunnel = new CHTTPTunnelObject();
$tunnels = $http_tunnel->loadList();

$smarty = new CSmartyDP();
$smarty->assign("tunnels", $tunnels);
$smarty->display("vw_tunnel_tools.tpl");