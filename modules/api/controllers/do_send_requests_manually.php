<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Api\CAPITiers;
use Ox\Api\CAPITiersStackRequest;
use Ox\Api\CPatientUserAPI;
use Ox\Core\CAppUI;
use Ox\Core\CView;

$api_class = CView::get("api_class_name", "str");
$requests_ids_str  = CView::get("requests_id_str", "str");
CView::checkin();

if (!$requests_ids_str) {
  CAppUI::displayAjaxMsg("CAPITiersStackRequest-msg-none selected", UI_MSG_WARNING);
  return;
}

$requests_ids  = explode(" ", $requests_ids_str);
$stack_request = new CAPITiersStackRequest();
$where         = array(
  "api_tiers_stack_request_id" => "IN(" . implode(", ", $requests_ids) . ")"
);

$where["api_class"] = "= '" . CPatientUserAPI::getAPiUserClass($api_class) . "'";
$requests           = $stack_request->loadList($where);
$api = new $api_class;

CAPITiers::treatStack($requests, $api);