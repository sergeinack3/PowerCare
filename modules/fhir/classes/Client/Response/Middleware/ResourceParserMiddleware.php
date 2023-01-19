<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Client\Response\Middleware;

use Ox\Interop\Fhir\Client\CFHIRClient;
use Ox\Interop\Fhir\Client\Response\Envelope;
use Ox\Interop\Fhir\Client\Response\Middleware\Stack\StackInterface;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionUpdate;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Serializers\CFHIRParser;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\ParameterBag;

class ResourceParserMiddleware implements MiddlewareInterface
{
    /**
     * @param Envelope       $envelope
     * @param StackInterface $stack
     *
     * @return Envelope
     * @throws InvalidArgumentException
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $response = $envelope->getResponse();
        if ($content = $response->getBody()) {
            $parser = CFHIRParser::parse($content);
            if ($resource = $parser->getResource()) {
                $envelope = $envelope->with($resource);
            }
            $envelope = $envelope->with($parser);
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
