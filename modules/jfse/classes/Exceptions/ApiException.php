<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Exceptions;

use Exception;

/**
 * Class ApiException
 *
 * @package Ox\Mediboard\Jfse\Exceptions
 */
final class ApiException extends JfseException
{
    /**
     * @param int $http_code
     *
     * @return ApiException
     */
    public static function httpError(int $http_code): self
    {
        if ($http_code >= 400 && $http_code < 500) {
            $exception = static::clientSideError($http_code);
        } else {
            $exception = static::serverSideError($http_code);
        }

        return $exception;
    }

    /**
     * @param int $http_code The http code
     *
     * @return ApiException
     */
    public static function clientSideError(int $http_code): self
    {
        return new static('ClientSideError', 'JfseApiException-error-client_side_error', [$http_code], $http_code);
    }

    /**
     * @param int $http_code The http code
     *
     * @return ApiException
     */
    public static function serverSideError(int $http_code): self
    {
        return new static('ServerSideError', 'JfseApiException-error-server_side_error', [$http_code], $http_code);
    }

    /**
     * @param string $method The API method that returned the response
     *
     * @return static
     */
    public static function invalidResponse(string $method): self
    {
        return new static('InvalidResponse', 'JfseApiException-error-invalid_response', [$method]);
    }

    /**
     * @param string      $message The error message returned by the API
     * @param int|null    $code    An optional error code returned by the API
     * @param string|null $source  The name of the method that generated the exception
     * @param string|null $details Additional details on the error
     *
     * @return static
     */
    public static function apiError(
        string $message,
        ?int $code = null,
        ?string $source = null,
        ?string $details = null
    ): self {
        $locales_args = [$message];

        if ($code) {
            $locales_args[] = "(code: $code)";
        } else {
            $locales_args[] = "(code: inconnu)";
        }

        if ($details) {
            $locales_args[] = $details;
        } else {
            $locales_args[] = 'Aucun détails';
        }

        return new static('ApiError', 'JfseApiException-error-api_error', $locales_args);
    }

    /**
     * @param Exception|null $e An exception raised by the HttpClient
     *
     * @return static
     */
    public static function httpClientError(?Exception $e = null): self
    {
        return new static('HttpClientError', 'JfseApiException-error-http_client_error', [], 0, $e);
    }

    /**
     * @param string $error
     * @param array  $data
     *
     * @return static
     */
    public static function requestForgeError(string $error, array $data): self
    {
        return new static('RequestForgeError', 'JfseApiException-error-request_forge_error');
    }
}
