<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Binary\Handle;

use Exception;
use Ox\Interop\Eai\CEAIDispatcher;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectHandleInterface;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\Binary\CFHIRResourceBinary;

/**
 * Description
 */
class BinaryEAIDispatcher implements DelegatedObjectHandleInterface
{
    /**
     * @inheritDoc
     */
    public function onlyProfiles(): array
    {
        return [CFHIR::class];
    }

    /**
     * @return string[]
     */
    public function onlyRessources(): array
    {
        return [CFHIRResourceBinary::class];
    }

    /**
     * @param CFHIRResource $resource
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource): bool
    {
        return $resource->getInteropActor() instanceof CInteropSender;
    }

    /**
     * @param CFHIRResourceBinary $resource
     *
     * @return CFHIRResource|null
     * @throws Exception
     * @inheritDoc
     */
    public function handle(CFHIRResource $resource): ?CFHIRResource
    {
        $sender          = $resource->getInteropActor();
        $content_decoded = $resource->getData() ? $resource->getData()->getDecodedData() : null;

        if ($content_decoded) {
            $result = CEAIDispatcher::dispatch($content_decoded, $sender);
        }

        return null; // nothing in response
    }
}
