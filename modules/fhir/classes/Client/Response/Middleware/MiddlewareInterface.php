<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Client\Response\Middleware;

use Ox\Interop\Fhir\Client\Response\Envelope;
use Ox\Interop\Fhir\Client\Response\Middleware\Stack\StackInterface;
use Ox\Interop\Fhir\Resources\CFHIRResource;

interface MiddlewareInterface
{
    /**
     * @param Envelope   $envelope
     * @param StackInterface $stack
     *
     * @return CFHIRResource
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope;
}
