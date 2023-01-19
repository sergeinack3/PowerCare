<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Api\CAPITiers;
use Ox\Api\CAPITiersException;
use Ox\Api\CAPITiersStackRequest;
use Ox\Api\CPatientUserAPI;
use Ox\Api\CWithingsAPI;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CAPITiersStackRequest::optimizeStack();
$patient_id     = CView::get("patient_id", "ref class|CPatient");
$emetteur       = CView::get("emetteur", "bool");
$datetime_start = CView::get("datetime_start", "dateTime");
$datetime_end   = CView::get("datetime_end", "dateTime");
$group_id       = CView::get("group_id", "ref class|CGroups");
$constant_code  = CView::get("constant_code", "str");
$refresh_filter = CView::get("refresh_filter", "bool default|0");
CView::checkin();

$stack_request = new CAPITiersStackRequest();
$where         = array();
if ($datetime_start) {
  $where["datetime_start"] = ">= $datetime_start'";
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
    $requests_name[] = "'$bundle_name_withings'";
  }
  else {
    $requests_name[] = "'$constant_code'";
  }
  $where["constant_code"]  = "IN(" . implode(", ", $requests_name) . ")";
}
if ($group_id) {
  $where["group_id"]  = "= '$group_id'";
}
if ($emetteur != "") {
  $where["emetteur"] = "= '$emetteur'";
}
$patient_user_api = new CPatientUserAPI();
if ($patient_id) {
  $patient_user_api->patient_id = $patient_id;
}

$limit          = 50;
$order          = "datetime_start DESC";
$total_requests = array();
$page           = array();
$warning        = array();
$requests       = array();
foreach (CAPITiers::getAPIList() as $_name) {
  try {
    $page[$_name]           = 0;
    /** @var $api CAPITiers */
    $warning[$_name] = null;
    $requests[$_name] = array();
    $total_requests[$_name] = 0;
    $api             = new $_name;
    $api->loadConf($api->getGroupId());

    $api_class = CPatientUserAPI::getAPiUserClass($_name);
    $where["api_class"] = "= '$api_class'";
    if ($constant_code && $_name == "CWithingsAPI" && CMbArray::in($constant_code, $bundle_request_withings)) {
      $where["constant_code"]  = "IN('$constant_code', '$bundle_name_withings')";
    }

    if ($patient_id) {
      $patient_user_api->api_user_class = $api_class;
      $patient_user_api->loadMatchingObject();
      if (!$patient_user_api->_id) {
        continue;
      }
      $patient_user_api->loadTargetObject();
      if (!$patient_user_api->_user_api) {
        continue;
      }
      $where["api_id"] = "= '".$patient_user_api->_user_api->_id."'";
    }
  }
  catch (CAPITiersException $e) {
    if ($e->getCode() === CAPITiersException::INVALID_CONF) {
      $warning[$_name] = $e->getMessage();
      continue;
    }
  }

  $total_requests[$_name] = $stack_request->countList($where);
  $requests[$_name]       = $stack_request->loadList($where, $order, "$limit");
  /** @var CAPITiersStackRequest $_request */
  foreach ($requests[$_name] as $_request) {
    if ($_request->group_id >= 0) {
      $_request->loadRefGroup();
    }
    $_request->loadRefUserApi();
  }
};

$smarty = new CSmartyDP();
$smarty->assign("refresh_filter", $refresh_filter);
$smarty->assign("warning", $warning);
$smarty->assign("request", $stack_request);
$smarty->assign("requests", $requests);
$smarty->assign("page", $page);
$smarty->assign("patient_user_api", $patient_user_api);
$smarty->assign("total_requests", $total_requests);
$smarty->display("vw_stack_request_api.tpl");