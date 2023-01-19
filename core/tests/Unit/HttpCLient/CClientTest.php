<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Exception;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use Ox\Core\HttpClient\Client;
use Ox\Core\HttpClient\ClientException;
use Ox\Core\HttpClient\Response;
use Ox\Mediboard\System\CExchangeHTTP;
use Ox\Mediboard\System\CSourceHTTP;
use Ox\Tests\OxUnitTestCase;

/**
 * Class CClientTest
 */
class CClientTest extends OxUnitTestCase
{
    private const END_POINT = 'https://httpbin.org/';

    /**
     * @return CSourceHTTP
     * @throws Exception
     */
    private function getSourceHttp(): CSourceHTTP
    {
        $source       = new CSourceHTTP();
        $source->host = static::END_POINT;

        return $source;
    }

    /**
     * @throws Exception
     */
    public function testConstruct(): void
    {
        $source = $this->getSourceHttp();
        $client = new Client($source);
        $this->assertInstanceOf(Client::class, $client);
    }

    /**
     * @group schedules
     * @throws GuzzleException
     * @throws ClientException
     */
    public function testCallGet(): void
    {
        $source = $this->getSourceHttp();
        $client = new Client($source);

        $response = $client->call('GET', '/get');
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @group schedules
     * @throws ClientException
     */
    public function testCallGetFaild(): void
    {
        $source           = $this->getSourceHttp();
        $source->host     = 'https://toto-tata-titi.ipsum/';
        $source->loggable = true;
        $client           = new Client($source);
        $this->expectException(ClientException::class);
        $client->call('GET', '/get');
    }

    /**
     * @group schedules
     * @throws GuzzleException
     * @throws ClientException
     */
    public function testCallGetLoggable(): void
    {
        $source           = $this->getSourceHttp();
        $source->loggable = true;
        $client           = new Client($source);
        $response         = $client->call('GET', '/get');
        $this->assertInstanceOf(CExchangeHTTP::class, $response->getExchangeHttp());
        $this->assertEquals($response->getExchangeHttp()->status_code, 200);
    }

    /**
     * @group schedules
     * @throws GuzzleException
     * @throws ClientException
     */
    public function testCallAuth(): void
    {
        $source           = $this->getSourceHttp();
        $source->user     = 'lorem';
        $source->password = 'azerty1';
        $client           = new Client($source);

        $response = $client->call('GET', '/basic-auth/lorem/azerty1');
        $this->assertEquals(200, $response->getStatusCode());
        $body = $response->getBody();
        $this->assertTrue($body['authenticated']);
    }

    public function testTimeoutIsSet(): void
    {
        $default_connect_timeout = Client::DEFAULT_CONNECT_TIMEOUT;
        $default_timeout         = Client::DEFAULT_TIMEOUT;

        // Default values are available
        $this->assertNotNull($default_connect_timeout);
        $this->assertNotNull($default_timeout);

        $source = $this->getSourceHttp();
        $client = new Client($source);

        // Default values are set
        $this->assertEquals($default_connect_timeout, $client->getConnectTimeout());
        $this->assertEquals($default_timeout, $client->getTimeout());

        // Client config values are used
        $guzzle = new Guzzle(['connect_timeout' => $default_connect_timeout + 1, 'timeout' => $default_timeout + 1]);
        $client = new Client($source, $guzzle);

        $this->assertEquals($default_connect_timeout + 1, $client->getConnectTimeout());
        $this->assertEquals($default_timeout + 1, $client->getTimeout());

        // Client config and default ones are used
        $guzzle = new Guzzle(['connect_timeout' => $default_connect_timeout + 5]);
        $client = new Client($source, $guzzle);

        $this->assertEquals($default_connect_timeout + 5, $client->getConnectTimeout());
        $this->assertEquals($default_timeout, $client->getTimeout());

        $guzzle = new Guzzle(['timeout' => $default_timeout + 4]);
        $client = new Client($source, $guzzle);

        $this->assertEquals($default_connect_timeout, $client->getConnectTimeout());
        $this->assertEquals($default_timeout + 4, $client->getTimeout());
    }
}
