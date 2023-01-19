<?php
/**
 * @package Mediboard\api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
use Ox\Api\CAPITiers;
use Ox\Api\CAPITiersStackRequest;
use Ox\Api\CPatientUserAPI;
use Ox\Core\CAppUI;

$apis = CAPITiers::getAPIList();
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
  $requests           = $stack_request->loadList($where, "datetime_start DESC", $number_req);
  CAPITiers::treatStack($requests, $api);
}