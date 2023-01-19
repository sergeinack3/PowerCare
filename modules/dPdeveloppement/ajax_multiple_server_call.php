<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

$get_area  = trim(CValue::get("getArea"));
$post_area = trim(CValue::get("postArea"));

CValue::setsession("multiple_server_call_get", $get_area);
CValue::setsession("multiple_server_call_post", $post_area);

$params_get = preg_split("#[\r\n]+#", $get_area, -1, PREG_SPLIT_NO_EMPTY);
$params_post = preg_split("#[\r\n]+#", $post_area, -1, PREG_SPLIT_NO_EMPTY);

$get = array();
foreach ($params_get as $_param_get) {
  $key_value = preg_split("#\s*=\s*#", $_param_get);
  $get[$key_value[0]] = $key_value[1];
}

$post = array();
foreach ($params_post as $_param_post) {
  $key_value = preg_split("#\s*=\s*#", $_param_post);
  $post[$key_value[0]] = $key_value[1];
}

$result_send = CApp::multipleServerCall(trim(CAppUI::conf("servers_ip")), $get, $post);

$smarty = new CSmartyDP();
$smarty->assign("result_send", $result_send);
$smarty->display("inc_result_servers_call.tpl");