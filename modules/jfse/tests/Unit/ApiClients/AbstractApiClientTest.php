<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\ApiClients;

use Ox\Mediboard\Jfse\Api\Client;
use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\ApiClients\AbstractApiClient;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class AbstractApiClientTest extends UnitTestJfse
{
    public function testSendRequest(): void
    {
        $empty_response = Response::forge('method', ['method' => ['output' => []]]);

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['sendRequest', 'setTimeout'])
            ->getMock();
        $client->method('sendRequest')->willReturn($empty_response);

        $api_client = $this->getMockForAbstractClass(AbstractApiClient::class, [$client]);

        $this->assertEquals(
            $empty_response,
            $this->invokePrivateMethod($api_client, 'sendRequest', Request::forge('method'))
        );
    }
}
