<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit;

use Ox\Core\Cache;
use Ox\Mediboard\Jfse\Api\ApiAuthenticator;

/**
 * Class JfseApiAuthenticatorTest
 */
class JfseApiAuthenticatorTest extends UnitTestJfse
{
    /**
     * Test the recuperation of the authorization token when it is stored in the cache
     */
    public function testGetTokenFromCache(): void
    {
        $token = 'eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJURVNUIiwiZXhwIjoxNTk3NzY3NjYwLCJlZGl0b3IiOiJSRVNJUCIsImlkSmZzZSI6IjEifQ';
        $cache = $this->getMockBuilder(Cache::class)
            ->setConstructorArgs(['Jfse-ApiAuthorizationToken', '1', Cache::NONE])->setMethods(
                ['exists', 'get']
            )->getMock();
        $cache->method('exists')->willReturn(true);
        $cache->method('get')->willReturn($token);

        $http_client = self::makeHttpClientMockFromGuzzleResponses([self::makeJsonGuzzleResponse(200, '1')]);

        $authenticator = new ApiAuthenticator(1, $cache, 120, $http_client);
        $this->assertEquals($token, $authenticator->getAuthorizationToken());
    }

    /**
     * Test the case when the API returns the token (with a mock client : http://docs.guzzlephp.org/en/v5/testing.html)
     */
    public function testGetTokenFromApi(): void
    {
        $token       = 'eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJURVNUIiwiZXhwIjoxNTk3NzY3NjYwLCJlZGl0b3IiOiJSRVNJUCIsImlkSmZzZSI6IjEifQ';
        $responses   = [
            self::makeJsonGuzzleResponse(200, json_encode(['error' => false, 'errorMessage' => '', 'token' => $token])),
        ];
        $http_client = self::makeHttpClientMockFromGuzzleResponses($responses);

        $cache = new Cache('Jfse-ApiAuthorizationToken', '1', Cache::NONE);

        $authenticator = new ApiAuthenticator(1, $cache, 120, $http_client);
        $this->assertEquals($token, $authenticator->getAuthorizationToken());
    }

    /**
     * Test when the API return a client side error (HTTP code 4XX)
     */
    public function testGetTokenFromApiClientSideError(): void
    {
        $this->expectExceptionMessage('ClientSideError');

        $cache = new Cache('Jfse-ApiAuthorizationToken', '1', Cache::NONE);

        $http_client = self::makeHttpClientMockFromGuzzleResponses([self::makeJsonGuzzleResponse(400, '')]);

        $authenticator = new ApiAuthenticator(1, $cache, 120, $http_client);
        $authenticator->getAuthorizationToken();
    }

    /**
     * Test when the API return a server side error (HTTP code 5XX)
     */
    public function testGetTokenFromApiServerSideError(): void
    {
        $this->expectExceptionMessage('ServerSideError');

        $cache = new Cache('Jfse-ApiAuthorizationToken', '1', Cache::NONE);

        $http_client = self::makeHttpClientMockFromGuzzleResponses([self::makeJsonGuzzleResponse(500, '')]);

        $authenticator = new ApiAuthenticator(1, $cache, 120, $http_client);
        $authenticator->getAuthorizationToken();
    }

    /**
     * Test when the API returns an invalid response
     */
    public function testGetTokenFromApiInvalidResponse(): void
    {
        $this->expectExceptionMessage('InvalidResponse');

        $cache       = new Cache('Jfse-ApiAuthorizationToken', '1', Cache::NONE);
        $responses   = [
            self::makeJsonGuzzleResponse(200, json_encode(['error' => false, 'errorMessage' => ''])),
        ];
        $http_client = self::makeHttpClientMockFromGuzzleResponses($responses);

        $authenticator = new ApiAuthenticator(1, $cache, 120, $http_client);
        $authenticator->getAuthorizationToken();
    }

    /**
     * Test when the API returns an empty token response
     */
    public function testGetTokenFromApiEmptyToken(): void
    {
        $this->expectExceptionMessage('InvalidResponse');

        $cache       = new Cache('Jfse-ApiAuthorizationToken', '1', Cache::NONE);
        $responses   = [
            self::makeJsonGuzzleResponse(200, json_encode(['error' => false, 'errorMessage' => '', 'token' => ''])),
        ];
        $http_client = self::makeHttpClientMockFromGuzzleResponses($responses);

        $authenticator = new ApiAuthenticator(1, $cache, 120, $http_client);
        $authenticator->getAuthorizationToken();
    }

    /**
     * Test when the API returns an invalid token response
     */
    public function testGetTokenFromApiInvalidToken(): void
    {
        $this->expectExceptionMessage('InvalidResponse');

        $cache       = new Cache('Jfse-ApiAuthorizationToken', '1', Cache::NONE);
        $responses   = [
            self::makeJsonGuzzleResponse(
                200,
                json_encode(
                    [
                        'error'        => false,
                        'errorMessage' => '',
                        'token'        => ['jgnkjnerkjgnerjgnjerng'],
                    ]
                )
            ),
        ];
        $http_client = self::makeHttpClientMockFromGuzzleResponses($responses);

        $authenticator = new ApiAuthenticator(1, $cache, 120, $http_client);
        $authenticator->getAuthorizationToken();
    }

    /**
     * Test when the API returns an error
     */
    public function testGetTokenFromApiError(): void
    {
        $this->expectExceptionMessage('ApiError');

        $cache         = new Cache('Jfse-ApiAuthorizationToken', '1', Cache::NONE);
        $text_response = json_encode(
            ['error' => false, 'errorMessage' => 'An error message', 'token' => 'brberblnvzoebfc']
        );
        $responses     = [
            self::makeJsonGuzzleResponse(200, $text_response),
        ];
        $http_client   = self::makeHttpClientMockFromGuzzleResponses($responses);

        $authenticator = new ApiAuthenticator(1, $cache, 120, $http_client);
        $authenticator->getAuthorizationToken();
    }
}
