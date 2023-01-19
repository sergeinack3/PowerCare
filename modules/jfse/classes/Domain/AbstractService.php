<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain;

use Ox\Mediboard\Jfse\Api\Error;
use Ox\Mediboard\Jfse\Api\Message;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\ApiClients\AbstractApiClient;
use Ox\Mediboard\Jfse\Exceptions\ApiException;
use Ox\Mediboard\Jfse\Exceptions\ApiMessageException;

/**
 * Class AbstractService
 *
 * @package Ox\Mediboard\Jfse\Domain
 */
abstract class AbstractService
{
    /** @var AbstractApiClient */
    protected $client;

    public function __construct(AbstractApiClient $client)
    {
        $this->client = $client;
        $this->client->setErrorsHandler([$this, 'handleErrors']);
        $this->client->setMessagesHandler([$this, 'handleMessages']);
    }

    /**
     * Displays the errors returned by the API
     *
     * @param Response $response
     *
     * @throws ApiException
     */
    public function handleErrors(Response $response): void
    {
        $error_codes = $response->getErrorCodes();
        foreach ($error_codes as $error_code) {
            $error = $response->getError($error_code);
            $response->removeError($error_code);

            if ($error_code !== Error::GENERAL_API_ERROR || count($error_codes) === 1) {
                throw ApiException::apiError(
                    $error->getDescription(),
                    $error->getCode(),
                    $error->getSource(),
                    $error->getDetails()
                );
            }
        }
    }

    /**
     * Handle the messages returned by the API, by displaying them (or format them in JSON)
     *
     * @param Response $response
     *
     * @throws ApiMessageException
     */
    public function handleMessages(Response $response): void
    {
        throw new ApiMessageException($response->getMessages());
    }


    /**
     * Only display the error messages
     *
     * @param Response $response
     *
     * @throws ApiMessageException
     */
    public function handleErrorMessagesOnly(Response $response): void
    {
        if ($response->hasMessages()) {
            $messages_to_handle = [];

            foreach ($response->getMessages() as $message) {
                if ($message->getLevel() === Message::ERROR) {
                    $messages_to_handle[] = $message;
                }
            }

            if (count($messages_to_handle)) {
                throw new ApiMessageException($messages_to_handle);
            }
        }
    }

    /**
     * Only display the error and warning messages
     *
     * @param Response $response
     *
     * @throws ApiMessageException
     */
    public function handleErrorAndWarningMessagesOnly(Response $response): void
    {
        if ($response->hasMessages()) {
            $messages_to_handle = [];

            foreach ($response->getMessages() as $message) {
                if ($message->getLevel() !== Message::INFO) {
                    $messages_to_handle[] = $message;
                }
            }

            if (count($messages_to_handle)) {
                throw new ApiMessageException($messages_to_handle);
            }
        }
    }

    /**
     * Only display the error and warning messages
     *
     * @param Response $response
     *
     * @throws ApiMessageException
     */
    public function handleErrorAndWarningMessagesForSourcesLibraryOnly(Response $response, array $libraries): void
    {
        if ($response->hasMessages()) {
            $messages_to_handle = [];

            foreach ($response->getMessages() as $message) {
                if (
                    $message->getLevel() === Message::ERROR
                    || ($message->getLevel() === Message::WARNING && in_array($message->getSourceLibrary(), $libraries))
                ) {
                    $messages_to_handle[] = $message;
                }
            }

            if (count($messages_to_handle)) {
                throw new ApiMessageException($messages_to_handle);
            }
        }
    }
}
