<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Api\CAPITiersStackRequest;
use Ox\Api\CPatientUserAPI;
use Ox\Api\CWithingsAPI;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

$api_classname  = CView::get("api_classname", "str");
$page           = CView::get("page", "num default|0");
$patient_id     = CView::get("patient_id", "ref class|CPatient");
$datetime_start = CView::get("datetime_start", "dateTime");
$datetime_end   = CView::get("datetime_end", "dateTime");
$group_id       = CView::get("group_id", "ref class|CGroups");
$constant_code  = CView::get("constant_code", "str");
$emetteur       = CView::get("emetteur", "bool");
CView::checkin();

$stack_request = new CAPITiersStackRequest();
$where = array();
if ($datetime_start) {
  $where["datetime_start"] = ">= '$datetime_start'";
}
if ($datetime_end) {
  $where["datetime_end"] = "<= '$datetime_end'";
}
if ($constant_code) {
  $bundle_name_withings = CWithingsAPI::REQUEST_BODY_COMBO;
  $bundle_request_withings = CWithingsAPI::$CONSTANTS_BODY_COMBO;
  $requests_name = array();
  if ($constant_code == $bundle_name_withings) {
    foreach ($bundle_request_withings as $_name) {
      $requests_name[] = "'$_name'";
    }
  }
  else if ($constant_code && $api_classname == "CWithingsAPI" && CMbArray::in($constant_code, $bundle_request_withings)) {
    $where["constant_code"]  = array($constant_code, $bundle_name_withings);
  }
  else {
    $requests_name[] = "'$constant_code'";
  }
  $where["constant_code"]  = "IN(" . implode(", ", $requests_name) . ")";}
if ($group_id) {
  $where["group_id"]  = "= '$group_id'";
}
if ($emetteur != "") {
  $where["emetteur"] = "= '$emetteur'";
}

$order              = "datetime_start DESC";
$user_api_name      = CPatientUserAPI::getAPiUserClass($api_classname);
$where["api_class"] = "= '$user_api_name'";

$patient_user_api = new CPatientUserAPI();
if ($patient_id) {
  $patient_user_api->patient_id = $patient_id;
  $patient_user_api->api_user_class = $user_api_name;
  $patient_user_api->loadMatchingObject();
  $patient_user_api->loadTargetObject();
  $where["api_id"] = "= '".$patient_user_api->_user_api->_id."'";
}

$limit                          = "$page, 50";
$total_requests[$api_classname] = $stack_request->countList($where);
$page                           = array($api_classname => $page);
$requests                       = $stack_request->loadList($where, $order, $limit);
$warning[$api_classname]        = null;
/** @var CAPITiersStackRequest $_request */
foreach ($requests as $_request) {
  if ($_request->group_id >= 0) {
    $_request->loadRefGroup();
  }
  $_request->loadRefUserApi();
}

$smarty = new CSmartyDP();
$smarty->assign("warning", $warning);
$smarty->assign("_requests", $requests);
$smarty->assign("_api_name", $api_classname);
$smarty->assign("page", $page);
$smarty->assign("total_requests", $total_requests);
$smarty->display("inc_tab_requests");