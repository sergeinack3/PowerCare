<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Client\Response\Middleware;

use Ox\Interop\Fhir\Client\Response\Envelope;
use Ox\Interop\Fhir\Client\Response\Middleware\Stack\StackInterface;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\ParameterBag;

class ErrorHandlerMiddleware implements MiddlewareInterface
{
    /** @var bool */
    private $has_errors = false;

    /** @var bool */
    private $has_content = false;

    /** @var string|null */
    private $content;

    /**
     * @param CFHIRResource  $resource
     * @param ParameterBag   $envelope
     * @param StackInterface $stack
     *
     * @return CFHIRResource
     * @throws InvalidArgumentException
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $response = $envelope->getResponse();
        $status   = $response->getStatusCode();

        $this->has_content = strlen($response->getBody() ?: '') > 0;
        $this->content     = $response->getBody();
        $this->has_errors  = $status >= 300;
        $envelope          = $envelope->with($this);

        if ($this->has_errors && !$this->has_content) {
            return $envelope;
        }

        return $stack->next()->handle($envelope, $stack);
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return $this->has_errors;
    }


}
