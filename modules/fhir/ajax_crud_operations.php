<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Interop\Fhir\Actors\CReceiverFHIR;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionDelete;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionHistory;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionRead;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionSearch;
use Ox\Mediboard\Sante400\CIdSante400;
use Symfony\Component\HttpFoundation\Request;

CCanDo::checkAdmin();

$cn_receiver_guid = CValue::sessionAbs("cn_receiver_guid");

$resource_type    = CView::get("resource_type", "str");
$resource_profile = CView::get('profile', 'str');
$interaction      = CView::get("interaction", "str");
$resource_id      = CView::get("resource_id", "str");
$version_id       = CView::get("version_id", "num");
$contents         = CView::get("contents", "str");
$lang             = CView::request("response_type", "enum list|xml|json");
$format           = "application/fhir+" . ($lang ?: 'xml');
CView::checkin();

if (!$cn_receiver_guid) {
    CAppUI::stepAjax("CInteropReceiver.none", UI_MSG_ERROR);
}

/** @var CReceiverFHIR $receiver_fhir */
$receiver_fhir  = CMbObject::loadFromGuid($cn_receiver_guid);
$request_method = Request::METHOD_GET;

if (!$resource = $receiver_fhir->getResource($resource_profile)) {
    CAppUI::stepAjax('CFHIRResource-msg-not supported', UI_MSG_ERROR);
}

$data = null;
switch ($interaction) {
    case CFHIRInteractionRead::NAME:
        $request = (new CFHIRInteractionRead($resource, $format))
            ->setResourceId($resource_id);
        if ($version_id) {
            $request->setVersionId($version_id);
        }
        break;
    case CFHIRInteractionSearch::NAME:
        $request = new CFHIRInteractionSearch($resource, $format);
        break;
    case CFHIRInteractionHistory::NAME:
        $request = (new CFHIRInteractionHistory($resource, $format))
            ->setResourceId($resource_id);
        if ($version_id) {
            $request->setVersionId($version_id);
        }
        break;
    case CFHIRInteractionCreate::NAME:
        if (!$resource_id) {
            CAppUI::stepAjax("CFHIRInteraction-msg-Not resource id", UI_MSG_ERROR);
        }

        $object = $resource->getObject();

        $object->load($resource_id);
        if (!$object || !$object->_id) {
            CAppUI::stepAjax('CFHIRInteraction-msg-Impossible to get object', UI_MSG_ERROR);
        }

        // todo a ref
        /*$request            = new CFHIRInteractionCreate($resource, $format);
        $request->_receiver = $receiver_fhir;
        $resourceRequest    = $request->build($resource, $object);
        $data               = $resourceRequest->output($format);
        */

        $request_method = "POST";
        break;

    case CFHIRInteractionDelete::NAME:
        if (!$resource_id) {
            CAppUI::stepAjax('CFHIRInteraction-msg-Not resource id', UI_MSG_ERROR);
        }

        $request = (new CFHIRInteractionDelete($resource, $format))
            ->setResourceId($resource_id);

        $request_method = 'DELETE';
        break;
    default:
        CAppUI::stepAjax('CFHIRInteraction-action-Not implemented', UI_MSG_ERROR);
}

try {
    CApp::log('Request path', $request->getPath());
    $response = $receiver_fhir->sendEvent($request, null, [$data], [], false, false, $request_method);
} catch (Exception $e) {
    CAppUI::stepAjax($e->getMessage());

    return;
}
if ($interaction == 'create') {
    //$source  = $receiver_fhir->_source;
    //$pattern = '/\/#resource_type\/(?\'resource_location\'([a-z0-9\-\.]{1,64}))/';
    //$pattern = str_replace('#resource_type', $resource_type, $pattern);
    //if (!preg_match($pattern, $source->_location_resource, $matches)) {
    if (!$location_header = $response->getHeader('Location')) {
        CAppUI::stepAjax('HEADER location not found', UI_MSG_ERROR);
    }
    // todo doit être repris !!
    // Création de l'idex
    //if (($resource_id = CMbArray::get($matches, 'resource_location')) && $object) {
    if (($resource_id = CMbArray::get([], 'resource_location')) && $object) {
        $idex        = CIdSante400::getMatchFor($object, $receiver_fhir->_tag_fhir);
        $idex->id400 = $resource_id;
        $idex->store();
    }
}

$smarty = new CSmartyDP();
$smarty->assign("query", $request->buildQuery());
$smarty->assign("lang", $lang);
$smarty->assign("response_code", $response->getStatusCode());
$smarty->assign("response_message", implode(' ', $response->getHeader('HTTP_Message')));
$smarty->assign("response_headers", implode(' ', $response->getHeader('HTTP_Code')));
$smarty->assign("response", $response->getBody());
$smarty->display("inc_vw_crud_operation_result.tpl");
