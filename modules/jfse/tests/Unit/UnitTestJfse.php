<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Ox\Core\CView;
use Ox\Core\HttpClient\Client as HttpClient;
use Ox\Mediboard\Jfse\Api\ApiAuthenticator;
use Ox\Mediboard\Jfse\Api\Client;
use Ox\Mediboard\System\CSourceHTTP;
use Ox\Tests\OxUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class UnitTestJfse
 *
 * This is a helper class for jFSE unit test calls
 *
 * Client part:
 * To get a client (usually used), @see UnitTestJfse::makeClientFromGuzzleResponses()
 * To get the HTTP client, @see UnitTestJfse::makeHttpClientMockFromGuzzleResponses()
 * To get the authenticator, @see UnitTestJfse::makeAuthenticator()
 *
 * Response part:
 * A helper function to make json Guzzle responses, @see UnitTestJfse::makeJsonGuzzleResponse()
 *
 * @package Ox\Mediboard\Jfse\Tests\Unit
 */
class UnitTestJfse extends OxUnitTestCase
{
    /** @var ApiAuthenticator */
    protected $authenticator;
    /** @var HttpClient */
    protected $http_client;

    public function setUp(): void
    {
        parent::setUp();

        CView::reset();
    }

    /**
     * Util function to easily make a Guzzle response adapted to jFSE
     *
     * @param int    $code Status response code
     * @param string $data json string which will be returned
     *
     * @return Response
     */
    protected static function makeJsonGuzzleResponse(int $code, string $data): Response
    {
        return new Response($code, ['Content-Type' => 'application/json'], $data);
    }

    /**
     * Makes a client from an array of Guzzle responses
     *
     * @param array $responses
     *
     * @return Client
     */
    protected function makeClientFromGuzzleResponses(array $responses): Client
    {
        $this->makeAuthenticator();
        $this->makeHttpClientMockFromGuzzleResponses($responses);

        return new Client($this->http_client, $this->authenticator);
    }

    /**
     * Make the authenticator mock
     *
     * @return MockObject
     */
    protected function makeAuthenticator(): MockObject
    {
        $token = 'eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJURVNUIiwiZXhwIjoxNTk3NzY3NjYwLCJlZGl0b3IiOiJSRVNJUCIsImlkSmZzZSI6IjEifQ';

        $mock = $this->getMockBuilder(ApiAuthenticator::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAuthorizationToken'])
            ->getMock();

        $mock->method('getAuthorizationToken')->willReturn($token);

        $this->authenticator = $mock;

        return $mock;
    }

    /**
     * Initializes the HttpClient and mocks the GuzzleClient
     *
     * @param array $responses An array of Guzzle Responses to mock the API
     *
     * @return HttpClient
     */
    protected function makeHttpClientMockFromGuzzleResponses(array $responses): HttpClient
    {
        $mock = new MockHandler($responses);

        $handler_stack = HandlerStack::create($mock);
        $guzzle_client = new GuzzleClient(['handler' => $handler_stack]);

        $this->http_client = new HttpClient(new CSourceHTTP(), $guzzle_client);

        return $this->http_client;
    }
}
