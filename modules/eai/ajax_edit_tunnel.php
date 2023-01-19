<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Eai\CHTTPTunnelObject;

$id_tunnel = CValue::get("tunnel_id");

$http_tunnel = new CHTTPTunnelObject();
$http_tunnel->load($id_tunnel);

$smarty = new CSmartyDP();
$smarty->assign("tunnel", $http_tunnel);
$smarty->display("inc_edit_tunnel.tpl");