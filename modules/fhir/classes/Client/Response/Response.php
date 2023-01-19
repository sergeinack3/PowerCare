<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Client\Response;

use Ox\Core\CMbXMLDocument;
use Ox\Core\HttpClient\Response as OxResponse;
use Ox\Interop\Fhir\Actors\CReceiverFHIR;
use Ox\Interop\Fhir\Client\CFHIRClient;
use Ox\Interop\Fhir\Client\Response\Middleware\ErrorHandlerMiddleware;
use Ox\Interop\Fhir\Client\Response\Middleware\MiddlewareInterface;
use Ox\Interop\Fhir\Client\Response\Middleware\ResourceLocatorMiddleware;
use Ox\Interop\Fhir\Client\Response\Middleware\ResourceMarkerMiddleware;
use Ox\Interop\Fhir\Client\Response\Middleware\ResourceParserMiddleware;
use Ox\Interop\Fhir\Client\Response\Middleware\ResourceRetrieverMiddleware;
use Ox\Interop\Fhir\Client\Response\Middleware\Stack\StackMiddleware;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\OperationOutcome\CFHIRResourceOperationOutcome;
use Ox\Interop\Fhir\Serializers\CFHIRParser;
use Ox\Mediboard\System\CExchangeHTTP;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\ParameterBag;

class Response extends OxResponse
{
    /** @var array */
    private $options;

    /** @var MiddlewareInterface[] */
    private $middlewares = [];

    /** @var CFHIRClient */
    private $client;

    /** @var CFHIRResource */
    private $resource;

    /**
     * @var Envelope
     */
    private $envelope;

    /**
     * Response constructor.
     *
     * @param ResponseInterface  $response
     * @param CExchangeHTTP|null $exchange_http
     * @param array              $options
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        ResponseInterface $response,
        ?CExchangeHTTP $exchange_http,
        CFHIRClient $client,
        array $options = []
    ) {
        parent::__construct($response, $exchange_http);

        $this->options = $options;

        $this->client = $client;

        $this->envelope = $this->applyMiddlewares($this, $this->getMiddlewares());
    }

    /**
     * @return CFHIRResource|null
     */
    public function getResource(): ?CFHIRResource
    {
        if ($this->resource) {
            return $this->resource;
        }

        $resource = $this->envelope->getResource();
        if ($this->hasError() && !$resource) {
            return null;
        }

        return $this->resource = $resource;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        /** @var ErrorHandlerMiddleware $error_middleware */
        if ($error_middleware = $this->envelope->last(ErrorHandlerMiddleware::class)) {
            return $error_middleware->hasErrors();
        }

        return false;
    }


    /**
     * @param ResponseInterface     $response
     * @param MiddlewareInterface[] $middlewares
     *
     * @return CFHIRResource
     */
    private function applyMiddlewares(Response $response, iterable $middlewares): Envelope
    {
        $stack = new StackMiddleware($middlewares);

        $envelope = new Envelope(
            $response,
            [$this->client, $this, $this->client->getReceiver()]
        );

        return $stack->next()->handle($envelope, $stack);
    }

    /**
     * @return array
     */
    private function getMiddlewares(): array
    {
        // handle error
        $this->addMiddleWare(new ErrorHandlerMiddleware());

        // Parser Middleware
        $this->addMiddleWare(new ResourceParserMiddleware());

        // Locator Middleware
        $this->addMiddleWare(new ResourceLocatorMiddleware());

        // Retrieve resources
        $this->addMiddleWare(new ResourceRetrieverMiddleware());

        // add Idex on object
        if ($this->client->getOption('mark_object') === true && $this->client->getOption('object')) {
            $this->addMiddleWare(new ResourceMarkerMiddleware());
        }

        return $this->middlewares;
    }

    /**
     * @param MiddlewareInterface $middleware
     */
    public function addMiddleWare(MiddlewareInterface $middleware): void
    {
        $this->middlewares[] = $middleware;
    }
}
