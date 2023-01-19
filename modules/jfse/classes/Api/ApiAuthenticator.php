<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Api;

use Exception;
use Ox\Core\Cache;
use Ox\Core\HttpClient\Client as HttpClient;
use Ox\Core\HttpClient\ClientException;
use Ox\Mediboard\Jfse\Exceptions\ApiException;
use Ox\Mediboard\Jfse\Exceptions\ConfigurationException;
use Ox\Mediboard\Jfse\Utils;
use Ox\Mediboard\System\CSourceHTTP;

/**
 * Handles the authorization token, either by getting it from the Jfse server or from the cache
 *
 * Class JfseApiAuthenticator
 *
 * @package Ox\Mediboard\Jfse\API
 */
class ApiAuthenticator
{
    /** @var string The endpoint for getting the token from the API */
//    private const TOKEN_ENDPOINT = 'application/jfse-web/get-token';
    private const TOKEN_ENDPOINT = 'jfse-proxy/get-token';

    /** @var int The ttl of the authorization token, in minutes (default 120) */
    private $ttl;

    /** @var int The Jfse user id */
    private $user;

    /** @var string The authorization token */
    private $token;

    /** @var Cache The cache object */
    private $cache;

    /** @var HttpClient The HTTP client */
    private $http_client;

    /**
     * JfseApiAuthenticator constructor.
     *
     * @param int             $user        The id of the Jfse user
     * @param Cache           $cache       The cache object
     * @param int             $ttl         The duration of the token before it expires, in minutes
     * @param HttpClient|null $http_client The HTTP client
     */
    public function __construct(int $user, Cache $cache, int $ttl = 60, ?HttpClient $http_client = null)
    {
        $this->user = $user;
        $this->ttl  = $ttl;

        $this->cache = $cache;

        $http_client       = $http_client ?? self::getHttpClient();
        $this->http_client = $http_client;
        $this->http_client->setOptions(['timeout' => 5]);
    }

    /**
     * Creates the HttpClient
     *
     * @return HttpClient
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
     * Returns the authorization token, either from the cache, or from the Jfse Server
     *
     * @param bool $force_api_call If true, a new token will be fetched from the Api, even if it is still in cache
     *
     * @return string
     * @throws ClientException
     */
    public function getAuthorizationToken(bool $force_api_call = false): string
    {
        if ($this->isTokenCached() && !$force_api_call) {
            return $this->token;
        } else {
            $this->setTokenFromApi();
            $this->storeTokenInCache();
        }

        return $this->token;
    }

    /**
     * Checks if the token has been stored in the cache
     * If so, set the token and return true
     *
     * @return bool
     */
    private function isTokenCached(): bool
    {
        $this->token = $this->cache->get();

        return is_string($this->token) && $this->token !== '';
    }

    /**
     * Get an authentication token from the Jfse server for the given user
     *
     * @return void
     * @throws ClientException
     */
    private function setTokenFromApi(): void
    {
        try {
            $response = $this->http_client->call(
                'GET',
                static::TOKEN_ENDPOINT . '?' . http_build_query(
                    [
                        'idJfse'     => $this->user,
                        'editorKey'  => Utils::getEditorKey(),
                        'editorName' => Utils::getEditorName(),
                        'expires'    => $this->ttl + 30,
                    ]
                )
            );
        } catch (ClientException $e) {
            throw ApiException::httpClientError($e);
        } catch (Exception $e) {
            throw ApiException::httpClientError($e);
        }

        if ($response->getStatusCode() !== 200) {
            throw ApiException::httpError($response->getStatusCode());
        }

        $data = $response->getBody();
        if (
            !is_array($data) || !array_key_exists('token', $data)
            || $data['token'] === '' || !is_string($data['token'])
        ) {
            throw ApiException::invalidResponse(static::TOKEN_ENDPOINT);
        }

        if (array_key_exists('errorMessage', $data) && $data['errorMessage']) {
            throw ApiException::apiError($data['errorMessage']);
        }

        $this->token = $data['token'];
    }

    /**
     * Stores the token in the cache
     *
     * @return void
     */
    private function storeTokenInCache(): void
    {
        $this->cache->put($this->token);
    }
}
