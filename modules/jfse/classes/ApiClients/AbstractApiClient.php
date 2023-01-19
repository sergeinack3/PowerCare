<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use Ox\Mediboard\Jfse\Api\Client;
use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;

abstract class AbstractApiClient
{
    /** @var Client An optional API client */
    protected $client;

    /** @var callable */
    protected $errors_handler;

    /** @var mixed Optional parameters to be passed to the handler, in addition to the response */
    protected $errors_handler_parameters;

    /** @var callable */
    protected $messages_handler;

    /** @var mixed Optional parameters to be passed to the handler, in addition to the response */
    protected $messages_handler_parameters;

    /**
     * AbstractApiClient constructor.
     *
     * @param Client|null $client
     */
    public function __construct(?Client $client = null)
    {
        $this->client = $client;
    }

    /**
     * If there is a specific behaviour to have when receiving errors, change the errors handler
     *
     * @param callable $handler
     * @param mixed    $parameters Optional parameters to be passed to the handler, in addition to the response
     */
    public function setErrorsHandler(callable $handler, $parameters = null): void
    {
        $this->errors_handler = $handler;
        $this->errors_handler_parameters = $parameters;
    }

    /**
     * If there is a specific behaviour to have when receiving messages, change the messages handler
     *
     * @param callable $handler
     * @param mixed    $parameters Optional parameters to be passed to the handler, in addition to the response
     */
    public function setMessagesHandler(callable $handler, $parameters = null): void
    {
        $this->messages_handler = $handler;
        $this->messages_handler_parameters = $parameters;
    }

    /**
     * Send a request and inject an API client to the Jfse Client
     *
     * @param Request $request
     * @param int     $timeout
     *
     * @return Response
     */
    protected function sendRequest(Request $request, int $timeout = 10): Response
    {
        $response = Client::send($request, $this->client, $timeout);

        if ($response->hasErrors() && is_callable($this->errors_handler)) {
            call_user_func($this->errors_handler, $response, $this->errors_handler_parameters);
        } elseif ($response->hasMessages() && is_callable($this->messages_handler)) {
            call_user_func($this->messages_handler, $response, $this->messages_handler_parameters);
        }

        return $response;
    }
}
