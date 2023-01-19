<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Api;

use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Exceptions\ApiException;

/**
 * Class Response
 *
 * @package Ox\Mediboard\Jfse\API
 */
final class Response
{
    /** @var string The name of the method that was called */
    private $method;

    /** @var array The content of the response */
    private $content;

    /** @var Error[] The errors returned in the response */
    private $errors;

    /** @var Message[] The messages returned in the response */
    private $messages;

    /** @var int The return mode */
    private $return_mode;

    /** @var string The return URL */
    private $return_url;

    /** @var string */
    private $raw_data;

    /**
     * Response constructor.
     *
     * @param string $method
     */
    private function __construct(string $method)
    {
        $this->method = $method;
        $this->errors = [];
        $this->messages = [];
    }

    /**
     * Instantiate a Response object and set its properties from the given data
     *
     * @param string $method The name of the API that returned the JSON response
     * @param array  $data   The full response returned by the API
     *
     * @return Response
     */
    public static function forge(string $method, array $data): Response
    {
        $data = CMbArray::mapRecursive('utf8_decode', $data);

        return (new self($method))->setContent($data)->setErrors($data)->setMessages($data)->setReturnMode($data);
    }

    /**
     * Parse the returnMode section of the API response
     *
     * @param array $data
     *
     * @return Response
     */
    private function setReturnMode(array $data): self
    {
        if (array_key_exists('returnMode', $data) && is_array($data['returnMode'])) {
            if (array_key_exists('mode', $data['returnMode']) && is_array($data['returnMode']['mode'])) {
                $this->return_mode = $data['returnMode']['mode'];
            }

            if (array_key_exists('URL', $data['returnMode']) && is_array($data['returnMode']['URL'])) {
                $this->return_url = $data['returnMode']['URL'];
            }
        }

        return $this;
    }

    /**
     * Parse the JSON data to extract the errors returned by the API
     *
     * @param array $data
     *
     * @return Response
     */
    private function setErrors(array $data): self
    {
        if (array_key_exists('lstException', $data['method']) && is_array($data['method']['lstException'])) {
            foreach ($data['method']['lstException'] as $error_data) {
                if (is_array($error_data) && $error_data['code'] !== 0) {
                    $error                           = Error::map($error_data);
                    $this->errors[$error->getCode()] = $error;
                }
            }
        }

        return $this;
    }

    /**
     * @param array $data
     *
     * @return self
     */
    private function setMessages(array $data): self
    {
        $messages = [];

        if (
            array_key_exists('output', $data['method'])
            && array_key_exists('lstMessages', $data['method']['output'])
            && is_array($data['method']['output']['lstMessages'])
        ) {
            $messages = array_merge($messages, $data['method']['output']['lstMessages']);
        }

        if (array_key_exists('lstMessages', $data['method']) && is_array($data['method']['lstMessages'])) {
            $messages = array_merge($messages, $data['method']['lstMessages']);
        }

        foreach ($messages as $message_data) {
            if (is_array($message_data)) {
                $this->messages[] = Message::map($message_data);
            }
        }

        return $this;
    }

    /**
     * Set the output of the Jfse response
     *
     * @param array $data The whole content of the response returned by Jfse
     *
     * @return Response
     */
    private function setContent(array $data): self
    {
        $this->raw_data = json_encode(CMbArray::mapRecursive('utf8_encode', $data), JSON_PRETTY_PRINT);

        if (!array_key_exists('method', $data) || !is_array($data['method'])) {
            throw ApiException::invalidResponse($this->method);
        }

        /* The jFSE API responses can contain not output (in case of modifications or deletions of entities) */
        if (array_key_exists('output', $data['method'])) {
            $this->content = $data['method']['output'];
        } elseif (array_key_exists('lstException', $data['method']) && !empty($data['method']['lstException'])) {
            $this->content = $data['method']['lstException'];
        } else {
            $this->content = [];
        }

        return $this;
    }

    /**
     * Returns the output of the api call
     *
     * @return array
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * @param int $code The error code
     *
     * @return bool
     */
    public function hasError(int $code): bool
    {
        return array_key_exists($code, $this->errors);
    }

    /**
     * @return int
     */
    public function countErrors(): int
    {
        return count($this->errors);
    }

    /**
     * Returns a list of the error codes
     *
     * @return array
     */
    public function getErrorCodes(): array
    {
        return array_keys($this->errors);
    }

    /**
     * @param int $code
     *
     * @return Error|null ?Error
     */
    public function getError(int $code): ?Error
    {
        $error = null;
        if (array_key_exists($code, $this->errors)) {
            $error = $this->errors[$code];
        }

        return $error;
    }

    /**
     * @param int $code
     *
     * @return void
     */
    public function removeError(int $code): void
    {
        if (array_key_exists($code, $this->errors)) {
            unset($this->errors[$code]);
        }
    }

    /**
     * @return bool
     */
    public function hasMessages(): bool
    {
        return !empty($this->messages);
    }

    /**
     * @return int
     */
    public function countMessages(): int
    {
        return count($this->messages);
    }

    /**
     * @return Message[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return int
     */
    public function getReturnMode(): int
    {
        return $this->return_mode;
    }

    /**
     * @return string
     */
    public function getReturnUrl(): string
    {
        return $this->return_url;
    }

    public function getRawData(): string
    {
        return $this->raw_data;
    }
}
