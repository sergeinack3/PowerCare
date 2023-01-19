<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Api;

use Ox\Core\Cache;
use Ox\Core\HttpClient\Client as HttpClient;

/**
 * Class ApiAuthenticatorFactory
 *
 * @package Ox\Mediboard\Jfse\API
 */
abstract class ApiAuthenticatorFactory
{
    /**
     * Returns an ApiAuthenticator
     *
     * @param int             $user        The Jfse user id
     * @param int             $ttl         The ttl of the cache object
     * @param HttpClient|null $http_client The HTTP client
     *
     * @return ApiAuthenticator
     */
    public static function get(int $user = 0, int $ttl = 60, HttpClient $http_client = null): ApiAuthenticator
    {
        $cache = new Cache('Jfse-ApiAuthorizationToken', $user, Cache::INNER_DISTR, $ttl);

        return new ApiAuthenticator($user, $cache, $ttl, $http_client);
    }
}
