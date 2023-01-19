<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Client\Response\Middleware\Stack;

use Ox\Interop\Fhir\Client\Response\Envelope;
use Ox\Interop\Fhir\Client\Response\Middleware\MiddlewareInterface;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Symfony\Component\HttpFoundation\ParameterBag;

class StackMiddleware implements StackInterface, MiddlewareInterface
{
    /** @var array */
    private $middlewares;

    /** @var int */
    private $offset = 0;

    public function __construct(iterable $middlewares)
    {
        $this->middlewares = $middlewares;
    }

    /**
     * @return MiddlewareInterface
     */
    public function next(): MiddlewareInterface
    {
        if (isset($this->middlewares[$this->offset])) {
            $next = $this->middlewares[$this->offset];
            $this->offset++;

            return $next;
        }

        return $this;
    }

    /**
     * @param CFHIRResource  $resource
     * @param ParameterBag   $envelope
     * @param StackInterface $stack
     *
     * @return Envelope
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        return $envelope;
    }
}

