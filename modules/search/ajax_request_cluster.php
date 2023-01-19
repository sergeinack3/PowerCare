<?php
/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CHTTPClient;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

CCanDo::checkAdmin();
$request = CValue::get("request");
$type    = CValue::get("type_request");
$content = "";


if ($request && !strripos($request, "delete")) {
  $client = new CHTTPClient($request);

  switch ($type) {
    case "get":
      $content = $client->get();
      break;
    case "put":
      $content = $client->putFile($request);
      break;
    case "post":
      $content = $client->post($request);
      break;
    default:
      $content = $client->get();
  }
}
if (!$content) {
  CAppUI::stepAjax("$request est invalide", UI_MSG_ERROR);
}

$smarty = new CSmartyDP();

$smarty->assign("request", $request);
$smarty->assign("type", $type);
$smarty->assign("content", $content);

$smarty->display("inc_vw_request_cluster.tpl");