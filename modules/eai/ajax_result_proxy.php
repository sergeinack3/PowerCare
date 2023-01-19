<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CHTTPClient;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Eai\CHTTPTunnelObject;

$action = CValue::get("action", null);
$id     = CValue::get("idTunnel", null);
$param  = CValue::get("param", null);

$tunnel = new CHTTPTunnelObject();
$tunnel->load($id);

$http_client = new CHTTPClient($tunnel->address);
if ($tunnel->ca_file) {
  $http_client->setSSLPeer($tunnel->ca_file);
}

$result = "";
switch ($action) {
  case "restart":
    $http_client->setOption(CURLOPT_CUSTOMREQUEST, "CMD RESTART");
    $result = $http_client->executeRequest();
    break;
  case "stop":
    $http_client->setOption(CURLOPT_CUSTOMREQUEST, "CMD STOP");
    $result = $http_client->executeRequest();
    break;
  case "stat":
    $http_client->setOption(CURLOPT_CUSTOMREQUEST, "CMD STAT");
    $result = $http_client->executeRequest();
    $result = json_decode($result, true);
    break;
  case "test":
    $http_client->setOption(CURLOPT_HEADER, true);
    $result = $http_client->executeRequest();
    break;
  case "setlog":
    $http_client->setOption(CURLOPT_CUSTOMREQUEST, "CMD SETLOG $param");
    $result = $http_client->executeRequest();
    $result = json_decode($result, true);
    break;
}

$smarty = new CSmartyDP();
$smarty->assign("result", $result);
$smarty->display("inc_tunnel_result.tpl");