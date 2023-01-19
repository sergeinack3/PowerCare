<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Api\CAPITiersStackRequest;
use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

$request_id = CView::get("stack_request_id", "ref class|CAPITiersStackRequest");
CView::checkin();

$request = new CAPITiersStackRequest();
$request->load($request_id);

if (!$request_id) {
  CAppUI::stepAjax(CAppUI::tr("CAPITiersStackRequest.none"), UI_MSG_ERROR);
}

$smarty = new CSmartyDP();
$smarty->assign("request", $request);
$smarty->display("modal_response_request.tpl");