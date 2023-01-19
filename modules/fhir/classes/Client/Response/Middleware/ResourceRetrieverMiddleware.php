<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Client\Response\Middleware;

use Ox\Interop\Fhir\Client\CFHIRClient;
use Ox\Interop\Fhir\Client\Response\Envelope;
use Ox\Interop\Fhir\Client\Response\Middleware\Stack\StackInterface;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionRead;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Psr\SimpleCache\InvalidArgumentException;

class ResourceRetrieverMiddleware implements MiddlewareInterface
{
    /** @var string */
    public const OPTION_RETRIEVE_RESOURCES = 'retrieve_resources';

    /**
     * @param Envelope       $envelope
     * @param StackInterface $stack
     *
     * @return CFHIRResource
     * @throws InvalidArgumentException
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        /** @var CFHIRClient $client */
        $client     = $envelope->last(CFHIRClient::class);
        $references = $envelope->all(CFHIRDataTypeReference::class);
        if (!$client || !$references) {
            return $stack->next()->handle($envelope, $stack);
        }

        $envelope = $envelope->withoutAll(CFHIRDataTypeReference::class);

        // creation call
        $calls = [];
        foreach ($references as $reference) {
            $id              = $reference->getReferenceID();
            $target_resource = $reference->resolveResourceTarget();
            if (!$id || !$target_resource) {
                continue;
            }
            $supported_interactions = $client->getReceiver()->getAvailableInteractions($target_resource);
            if (!in_array(CFHIRInteractionRead::NAME, $supported_interactions)) {
                continue;
            }
            // todo lier le format au format passé en option dans la first request
            $interaction = new CFHIRInteractionRead($target_resource);
            $interaction->setResourceId($id);

            $calls[] = [$interaction, $reference];
        }

        // call && mapping
        foreach ($calls as $call) {
            // todo gestion du call en bundle
            /** @var CFHIRInteractionRead $interaction */
            /** @var CFHIRDataTypeReference $reference */
            [$interaction, $reference] = $call;
            $options  = $client->getOptions(['retrieve_resources' => 'none']);
            $response = $client->request($interaction, $options);
            if (!$response->hasError() && ($target_resource = $response->getResource())) {
                $reference->setTargetResource($target_resource);
            }
        }

        return $stack->next()->handle($envelope, $stack);
    }

}
