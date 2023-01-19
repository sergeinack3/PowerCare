<?php
/**
 * @package Mediboard\API_tiers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Api\CAPITiers;
use Ox\Api\CAPITiersStackRequest;
use Ox\Api\CPatientUserAPI;
use Ox\Core\CAppUI;
use Ox\Core\CView;

$apis = array();
foreach ($_GET as $_key => $_value) {
  if (!strstr($_key, "choice_api")) {
    continue;
  }
  $apis[$_value] = $_value;
}
CView::checkin();
$number_req    = CAppUI::conf("api number_request");
$stack_request = new CAPITiersStackRequest();
$where         = array(
  "receive_datetime" => "IS NULL",
  "send_datetime"    => "IS NULL"
);

foreach ($apis as $_api_name) {
  /** @var CAPITiers $api */
  $api = new $_api_name;

  $where["api_class"] = "= '" . CPatientUserAPI::getAPiUserClass($_api_name) . "'";
  $requests           = $stack_request->loadList($where, "RAND()", $number_req, null, null, null, null, false);
  CAPITiers::treatStack($requests, $api);
}