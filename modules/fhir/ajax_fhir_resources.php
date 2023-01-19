<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Interop\Eai\CInteropActorFactory;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Fhir\Actors\CReceiverFHIR;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCapabilities;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\R4\CapabilityStatement\CFHIRResourceCapabilityStatement;
use Ox\Interop\Fhir\Utilities\CCapabilitiesResource;

CCanDo::checkAdmin();

$cn_receiver_guid       = CValue::sessionAbs("cn_receiver_guid");
$use_actor_capabilities = CView::get('use_actor_capabilities', 'bool default|0');
CView::checkin();

/** @var CFHIR $class */
$receiver = (new CInteropActorFactory())->receiver()->makeFHIR();
$objects  = CReceiverFHIR::getObjectsBySupportedEvents(
    CFHIR::$evenements,
    $receiver,
    true,
);

/** @var CInteropReceiver[] $receivers */
$receivers = [];
foreach ($objects as $event => $_receivers) {
    if (!$_receivers) {
        continue;
    }

    /** @var CInteropReceiver[] $_receivers */
    foreach ($_receivers as $_receiver) {
        $_receiver->loadRefGroup();
        $receivers[$_receiver->_guid] = $_receiver;
    }
}

$smarty = new CSmartyDP();

if (!$cn_receiver_guid = CValue::sessionAbs("cn_receiver_guid")) {
    $smarty->assign("use_actor_capabilities", $use_actor_capabilities);
    $smarty->assign("receivers", $receivers);
    $smarty->assign("resources", []);
    $smarty->assign("cn_receiver_guid", $cn_receiver_guid);
    $smarty->display("inc_vw_crud_operations.tpl");
    return;
}

/** @var CReceiverFHIR $receiver */
$receiver = CStoredObject::loadFromGuid($cn_receiver_guid);

// use mode with receiver capabalities
if ($use_actor_capabilities) {
    $interaction         = new CFHIRInteractionCapabilities();
    $interaction->profil = "CFHIR";

    // retrieve receiver capabilities
    $response            = $receiver->sendEvent($interaction);

    // map resource with data
    $resource            = new CFHIRResourceCapabilityStatement();
    $capabilities        = $resource->deserialize($response->getBody());
    $resources_available = $capabilities->getResourcesManaged($receiver);

    // generate resources supported & activated
    $resources = [];
    foreach ($resources_available as $capabilities_resource) {
        if (!$resource = $receiver->getResource($capabilities_resource->getProfile())) {
            continue;
        }

        /** @var CFHIR $profile */
        $profile_class = $resource::PROFILE_CLASS;
        $profile       = new $profile_class();

        if (!isset($resources[$profile->type])) {
            $resources[$profile->type] = [
                'profile'   => $profile,
                'resources' => [$capabilities_resource],
            ];
        } else {
            $resources[$profile->type]['resources'][] = $capabilities_resource;
        }
    }

    // generate resources not activated
    $resources_not_actived   = $capabilities->getResourcesNotActivated($receiver);
    // generate resources not supported
    $resources_not_supported = $capabilities->getResourcesNotSupported();

    $smarty->assign("resources_not_actived", $resources_not_actived);
    $smarty->assign("resources_not_supported", $resources_not_supported);
} else {
    // available resources
    $available_resources = $receiver->getAvailableResources();

    // construct capabilities objects
    $resources = [];
    foreach ($available_resources as $resource) {
        $profile_class = $resource::PROFILE_CLASS;
        $profile = new $profile_class();

        // retrieve available interactions from receiver
        $interactions = $receiver->getAvailableInteractions($resource);

        // make a capability from resource
        $capabilities = new CCapabilitiesResource();
        $capabilities->setInteractions($interactions);
        $capabilities->setType($resource::RESOURCE_TYPE);
        $capabilities->setProfile($resource->getProfile());

        // assemble all resources with own profile
        if (!isset($resources[$profile->type])) {
            $resources[$profile->type] = [
                'profile'   => $profile,
                'resources' => [$capabilities],
            ];
        } else {
            $resources[$profile->type]['resources'][] = $capabilities;
        }
    }
}

// Création du template
$smarty->assign("use_actor_capabilities", $use_actor_capabilities);
$smarty->assign("resources", $resources);
$smarty->assign("receivers", $receivers);
$smarty->assign("cn_receiver_guid", $cn_receiver_guid);
$smarty->display("inc_vw_crud_operations.tpl");
