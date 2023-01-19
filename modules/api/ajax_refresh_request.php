<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Api\CAPITiersStackRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

$api_class = CView::get("api_classname", "str");
$request_id = CView::get("request_id", "ref class|CAPITiersStackRequest");
CView::checkin();
$request = new CAPITiersStackRequest();
$request->load($request_id);
$request->loadRefGroup();
$request->loadRefUserApi();

$smarty = new CSmartyDP();
$smarty->assign("_request", $request);
$smarty->assign("_api_name", $api_class);
$smarty->display("inc_request_api");