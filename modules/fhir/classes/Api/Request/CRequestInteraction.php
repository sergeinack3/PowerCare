<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Api\Request;

use Ox\Core\CClassMap;
use Ox\Core\CMbModelNotFoundException;
use Ox\Interop\Eai\CMessageSupported;
use Ox\Interop\Fhir\Interactions\CFHIRInteraction;
use Symfony\Component\HttpFoundation\Request;

class CRequestInteraction
{
    /** @var string  */
    public const KEY_INTERN_MESSAGE_SUPPORTED = "interaction_supported_message";

    /** @var Request */
    private $request;

    /** @var CFHIRInteraction */
    private $interaction;

    /**
     * CRequestSearch constructor.
     *
     * @param Request            $request
     */
    public function __construct(Request $request)
    {
        $this->request     = $request;
        $this->interaction = $this->findInteraction();
    }

    /**
     * @return CFHIRInteraction
     */
    public function getInteraction(): CFHIRInteraction
    {
        return $this->interaction;
    }

    /**
     * @return CFHIRInteraction|null
     * @throws CMbModelNotFoundException
     */
    private function findInteraction(): ?CFHIRInteraction
    {
        if (!$interaction_class = $this->request->attributes->get('object_class')) {
            return null;
        }

        if (!CClassMap::getInstance()->getClassMap($interaction_class)) {
            return null;
        }

        $interaction = new $interaction_class();
        if (!$interaction instanceof CFHIRInteraction) {
            return null;
        }

        // message supported
        if ($message_supported_id = $this->request->attributes->get(self::KEY_INTERN_MESSAGE_SUPPORTED)) {
            $message_supported = CMessageSupported::findOrFail($message_supported_id);

            $interaction->setMessageSupported($message_supported);
        }

        return $interaction;
    }
}
