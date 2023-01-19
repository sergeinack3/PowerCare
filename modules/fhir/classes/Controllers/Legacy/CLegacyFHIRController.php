<?php

/**
 * @package Mediboard\Fhir\Controllers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Controllers\Legacy;

use Ox\Core\Api\Request\RequestApiBuilder;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Interop\Eai\CMessageSupported;
use Ox\Interop\Fhir\CExchangeFHIR;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Mediboard\System\CConfiguration;
use Ox\Mediboard\System\Controllers\ConfigurationController;

/**
 * Class CAppFineController
 * @package Ox\Interop\Fhir\Controllers
 */
class CLegacyFHIRController extends CLegacyController
{
    public function fhir_purge_messages_supported(): void
    {
        $this->checkPermAdmin();
        $message_ids = CView::get('message_ids', 'str');
        CView::checkin();

        if (!$message_ids) {
            CAppUI::stepAjax('Aucun message selectionné', UI_MSG_ALERT);
            return;
        }

        (new CMessageSupported())->deleteAll($message_ids);
         CAppUI::stepAjax('Suppression de ' . count($message_ids) . ' messages supportés');
    }

    public function fhir_list_message_supported(): void
    {
        $this->checkPermAdmin();
        $messages = $this->fhir_list_messages();
        $actor_messages = [];
        /** @var CMessageSupported $message */
        foreach ($messages as $message) {
            $object_guid = $message->object_class . '-' . $message->object_id;
            $actor_messages[$object_guid][] = $message;
        }

        $actors = [];
        foreach ($actor_messages as $actor_guid => $messages) {
            $actors[$actor_guid] = CStoredObject::loadFromGuid($actor_guid);
        }

        $this->renderSmarty('inc_list_fhir_message_supported', ['actor_messages' => $actor_messages, 'actors' => $actors]);
    }

    private function fhir_list_messages(): array
    {
        $message_supported = new CMessageSupported();
        $ds                = $message_supported->getDS();
        $interactions      = CFHIR::$evenements;
        $where             = ['message' => $ds->prepareIn($interactions)];

        return $message_supported->loadList($where);
    }

    /**
     * @return void
     * @throws \Ox\Core\Api\Exceptions\ApiException
     * @throws \Ox\Core\Api\Exceptions\ApiRequestException
     */
    public function updateDelegatedValues(): void
    {
        $message_supported_ids = CView::get('message_supported_ids', 'str');
        if ($delegated_values = CView::get('delegated_values', 'str')) {
            $delegated_values = json_decode(stripslashes($_GET['delegated_values']));
        }
        CView::checkin();

        if (!$delegated_values || !$message_supported_ids) {
            return;
        }

        $messages_supported = (new CMessageSupported())->loadAll($message_supported_ids);

        $configurations = [];
        foreach ($delegated_values as $delegated_type => $delegated_value) {
            if (!in_array($delegated_type, CExchangeFHIR::DELEGATED_OBJECTS)) {
                continue;
            }
            $configurations[]            = $configuration = new CConfiguration();
            $configuration->feature      = "fhir delegated_objects $delegated_type";
            $configuration->value        = $delegated_value;
        }

        if (!$configurations) {
            return;
        }

        $uri = $this->generateUrl('system_set_configs');
        foreach ($messages_supported as $message_supported) {
            $request = (new RequestApiBuilder())
                ->setUri($uri)
                ->setMethod('PUT')
                ->setContent(json_encode((new Collection($configurations))->serialize()))
                ->buildRequestApi();
            $request->getRequest()->query->set('context', $message_supported->_guid);

            // request configurations
            $controller = new ConfigurationController();
            $response   = $controller->setConfigurations($request);

            if ($response->getStatusCode() !== 200) {
                CAppUI::stepAjax('CLegacyFHIRController-msg-fail configure message supported', UI_MSG_ERROR);
            }
        }

        CAppUI::stepAjax('CLegacyFHIRController-msg-configure message supported', UI_MSG_OK);
    }
}
