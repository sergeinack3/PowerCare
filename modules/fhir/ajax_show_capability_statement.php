<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Interop\Fhir\Actors\CReceiverFHIR;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCapabilities;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkAdmin();

$cn_receiver_guid = CValue::sessionAbs("cn_receiver_guid");
$lang             = CView::request("response_type", "enum list|xml|json");
$format           = "application/fhir+" . ($lang ?: 'xml');
CView::checkin();

if (!$cn_receiver_guid) {
    CAppUI::stepAjax("CInteropReceiver.none", UI_MSG_ERROR);
}

$request = new CFHIRInteractionCapabilities(null, $format);

/** @var CReceiverFHIR $receiver_fhir */
$receiver_fhir = CMbObject::loadFromGuid($cn_receiver_guid);
try {
    $response = $receiver_fhir->sendEvent($request);
} catch (CMbException $e) {
    $e->stepAjax();

    return;
}

$smarty = new CSmartyDP();
$smarty->assign("query", $request->buildQuery());
$smarty->assign("lang", $lang);
$smarty->assign("response_code", $response->getStatusCode());
$smarty->assign("response_message", implode(' ', $response->getHeader('HTTP_Message')));
$smarty->assign("response_headers", implode(' ', $response->getHeader('HTTP_Code')));
$smarty->assign("response", $response->getBody());
$smarty->display("inc_vw_crud_operation_result.tpl");
