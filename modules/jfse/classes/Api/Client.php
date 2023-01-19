<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Api;

use Exception;
use Ox\Core\HttpClient\Client as HttpClient;
use Ox\Core\HttpClient\Response as HttpResponse;
use Ox\Core\HttpClient\ClientException;
use Ox\Mediboard\Jfse\Exceptions\ApiException;
use Ox\Mediboard\Jfse\Exceptions\ConfigurationException;
use Ox\Mediboard\Jfse\Utils;
use Ox\Mediboard\System\CSourceHTTP;

/**
 * Class Client
 *
 * @package Ox\Mediboard\Jfse\API
 */
class Client
{
    /** @var string The endpoint of the Jfse API */
//    private const API_ENDPOINT = 'application/jfse-web/init';
    private const API_ENDPOINT = 'jfse-proxy/init';

    /** @var ApiAuthenticator */
    private $authenticator;
    /** @var HttpClient */
    private $http_client;

    /**
     * Client constructor.
     *
     * @param HttpClient|null       $http_client   An optional HttpClient for testing purpose
     * @param ApiAuthenticator|null $authenticator An optional ApiAuthenticator for testing purpose
     */
    public function __construct(?HttpClient $http_client = null, ?ApiAuthenticator $authenticator = null)
    {
        $http_client = $http_client ?? self::getHttpClient();

        $this->http_client = $http_client;
        $this->http_client->setHeaders([
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json'
        ]);
        $this->authenticator = $authenticator;
    }

    protected function setTimeout(int $timeout): void
    {
        $this->http_client->setOptions(['timeout' => $timeout]);
    }

    /**
     * Sets the authorization header
     *
     * @param bool $force_api_call If true, a new token will be fetched from the Api, even if it is still in cache
     *
     * @return void
     */
    private function setAuthorizationHeader(bool $force_api_call = false): void
    {
        $this->authenticator = $this->authenticator ?? ApiAuthenticatorFactory::get(Utils::getJfseUserId());

        $token = $this->authenticator->getAuthorizationToken($force_api_call);
        $this->http_client->setHeaders(['Authorization' => "Bearer {$token}"], true);
    }

    /**
     * Send the given request to the Jfse API, and returns the response
     *
     * @param Request $request The request to send
     *
     * @return Response
     * @throws ApiException
     *
     */
    protected function sendRequest(Request $request): Response
    {
        $this->setAuthorizationHeader();

        $response = $this->call($request);

        if ($response->getStatusCode() === 403) {
            $this->setAuthorizationHeader(true);
            $response = $this->call($request);
        }

        if ($response->getStatusCode() !== 200) {
            throw ApiException::httpError($response->getStatusCode());
        }

        $response = Response::forge($request->getMethod(), $response->getBody());

        /* Handle the general errors that can be returned by Jfse */
        if ($response->hasErrors()) {
            $error_codes = $response->getErrorCodes();
            foreach ($error_codes as $error_code) {
                if (in_array($error_code, Error::$general_error_codes)) {
                    $error = $response->getError($error_code);
                    $response->removeError($error_code);
                    throw ApiException::apiError(
                        $error->getDescription(),
                        $error->getCode(),
                        $error->getSource(),
                        $error->getDetails()
                    );
                }
            }
        }

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return HttpResponse
     * @throws ApiException
     */
    protected function call(Request $request): HttpResponse
    {
        try {
            return $this->http_client->call('POST', self::API_ENDPOINT, $request->getContent());
        } catch (ClientException $e) {
            throw ApiException::httpClientError($e);
        } catch (Exception $e) {
            throw ApiException::httpClientError($e);
        }
    }

    /**
     * Creates the HttpClient
     *
     * @return HttpClient
     *
     * @throws ConfigurationException
     */
    private static function getHttpClient(): HttpClient
    {
        $source = self::getHttpSource();

        if (!$source || !$source->_id) {
            throw ConfigurationException::sourceHttpNotFound();
        }

        return new HttpClient($source);
    }

    /**
     * Returns the configured CSourceHTTP
     *
     * @return CSourceHTTP
     */
    private static function getHttpSource(): CSourceHTTP
    {
        return CSourceHTTP::get('JfseApi', 'http');
    }

    /**
     * A facade for the sendRequest method, that allow to inject the client for testing purpose
     *
     * @param Request     $request The request to send
     * @param Client|null $client  An optional client (for testing purpose)
     * @param int         $timeout
     *
     * @return Response
     */
    public static function send(Request $request, Client $client = null, int $timeout = 30): Response
    {
        $client = $client ?? new self();
        $client->setTimeout($timeout);

        return $client->sendRequest($request);
    }
}
