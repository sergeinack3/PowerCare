<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

CCanDo::checkAdmin();

$api      = CValue::get('api');
$username = CValue::get('username');
$password = CValue::get('password');
$prettify = CValue::get('prettify');
$data     = CValue::post('data');

$api      = ($api) ?: CValue::post('api');
$username = ($username) ?: CValue::post('username');
$password = ($password) ?: CValue::post('password');
$prettify = ($prettify) ?: CValue::post('prettify');

if (!$api || !$username || !$password) {
  CAppUI::stepAjax('common-error-Missing parameter', UI_MSG_ERROR);
}

$url         = rtrim(CAppUI::conf('external_url'), '/') . "/index.php?login={$username}:{$password}&m=mediusers&raw={$api}";
$url_display = rtrim(CAppUI::conf('external_url'), '/') . "/index.php?login={$username}:<MOT DE PASSE>&m=mediusers&raw={$api}";

if ($data) {
  $post = array(
    'prettify' => 1,
    'data'     => $data
  );
}
else {
  $url .= '&prettify=1';
  $post = null;
}

$http_client = curl_init($url);
curl_setopt($http_client, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($http_client, CURLOPT_TIMEOUT, 10);
curl_setopt($http_client, CURLOPT_RETURNTRANSFER, true);
curl_setopt($http_client, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($http_client, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($http_client, CURLOPT_FRESH_CONNECT, true);

if ($post) {
  $json = json_encode($post);

  curl_setopt($http_client, CURLOPT_POSTFIELDS, $json);
  curl_setopt(
    $http_client,
    CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Content-Length: ' . strlen($json))
  );
}

$response = curl_exec($http_client);

$smarty = new CSmartyDP();
$smarty->assign('url', $url_display);
$smarty->assign('post', $data);
$smarty->assign('response', $response);
$smarty->display('vw_api_results.tpl');