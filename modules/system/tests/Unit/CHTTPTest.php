<?php

/**
 * @package Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Classes;

use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\Contracts\Client\HTTPClientInterface;
use Ox\Interop\Eai\Resilience\ClientContext;
use Ox\Mediboard\System\Client\ResilienceHTTPClient;
use Ox\Mediboard\System\CExchangeHTTP;
use Ox\Mediboard\System\Client\HTTPClientCurlLegacy;
use Ox\Mediboard\System\CSourceHTTP;
use Ox\Tests\OxUnitTestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class CHTTPTest extends OxUnitTestCase
{
    /** @var CSourceHTTP */
    protected static $source;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $source                 = new CSourceHTTP();
        $source->loggable       = "0";
        $source->name           = 'test_TU_FileSystem';
        $source->host           = 'localhost';
        $source->active         = 1;
        $source->role           = CAppUI::conf('instance_role');
        $source->retry_strategy = "1|5 5|60 10|120 20|";

        if ($msg = $source->store()) {
            throw new CMbException($msg);
        }

        self::$source = $source;
    }


    public function testGetClient(): void
    {
        $source = $this->getMockBuilder(CSourceHTTP::class)->getMock();
        $client = $this->getMockBuilder(HTTPClientInterface::class)->getMock();
        $source->method('getClient')->willReturn($client);
        $this->assertInstanceOf(HTTPClientInterface::class, $client);
    }

    public function testGetClientCache(): void
    {
        $source = self::$source;
        $client = $source->getClient();
        $this->assertSame($client, $source->getClient());
    }

    public function testGetClientRetryable(): void
    {
        $source                 = self::$source;
        $source->retry_strategy = "1|5 5|60 10|120 20|";
        $client                 = $source->getClient();
        $this->assertInstanceOf(ResilienceHTTPClient::class, $client);
    }

    public function testGetClientOx(): void
    {
        $source                 = self::$source;
        $source->retry_strategy = "";
        $source->_client        = "";
        $client                 = $source->getClient();
        $this->assertInstanceOf(HTTPClientCurlLegacy::class, $client);
    }

    public function testOnBeforeRequestIsLoggable(): void
    {
        $source           = self::$source;
        $source->loggable = "1";
        $client           = $source->getClient();
        $client_context   = new ClientContext($client, $source);

        $source->_dispatcher->dispatch($client_context, $client::EVENT_BEFORE_REQUEST);

        $this->assertNotNull($source->_current_echange);
    }

    public function testOnBeforeRequestIsPsr(): void
    {
        $source         = self::$source;
        $source->host   = "http://www.google.com";
        $client         = $source->getClient();
        $client_context = new ClientContext($client, $source);
        $request        = new Request("GET", $source->host);
        $client_context->setRequest($request);

        $source->_dispatcher->dispatch($client_context, $client::EVENT_BEFORE_REQUEST);

        $this->assertNotNull($source->_current_echange);
        $this->assertNotNull($source->_current_echange->destinataire);
        $this->assertIsString($source->_current_echange->destinataire);
        $this->assertNotNull($source->_current_echange->input);
        $this->assertIsString($source->_current_echange->input);
    }

    public function testOnAfterRequest(): void
    {
        $source           = self::$source;
        $source->loggable = "1";
        $source->_client  = "";
        $client           = $source->getClient();
        $client_context   = new ClientContext($client, $source);

        $client_context->setArguments(['function_name' => 'send']);

        $source->_dispatcher->dispatch($client_context, $client::EVENT_BEFORE_REQUEST);

        $response = new Response('200', [], "data");

        $client_context->setResponse($response);
        $source->_dispatcher->dispatch($client_context, $client::EVENT_AFTER_REQUEST);

        $this->assertNotNull($source->_current_echange->output);
    }

    public function testOnExceptionResponse(): void
    {
        $source                   = self::$source;
        $source->_current_echange = new CExchangeHTTP();
        $client                   = $source->getClient();
        $client_context           = new ClientContext($client, $source);

        $response = new Response('500');
        $client_context->setResponse($response);

        $this->assertInstanceOf(ResponseInterface::class, $client_context->getResponse());

        $source->_dispatcher->dispatch($client_context, $client::EVENT_BEFORE_REQUEST);
        $source->_dispatcher->dispatch($client_context, $client::EVENT_EXCEPTION);

        $this->assertNotNull($source->_current_echange->output);
        $this->assertIsString($source->_current_echange->output);
    }

    public function testOnExceptionThrowable(): void
    {
        $source                   = self::$source;
        $source->_current_echange = new CExchangeHTTP();
        $client                   = $source->getClient();
        $client_context           = new ClientContext($client, $source);

        $response = new HttpException('500');
        $client_context->setThrowable($response);

        $this->assertInstanceOf(HttpExceptionInterface::class, $client_context->getThrowable());

        $source->_dispatcher->dispatch($client_context, $client::EVENT_BEFORE_REQUEST);
        $source->_dispatcher->dispatch($client_context, $client::EVENT_EXCEPTION);

        $this->assertNotNull($source->_current_echange);

        $this->assertNotNull($source->_current_echange->output);
        $this->assertIsString($source->_current_echange->output);
    }

    public function testGetHost(): void
    {
        $source       = self::$source;
        $source->host = "test host";

        $host = $source->getHost();

        $this->assertNotNull($host);
        $this->assertIsString($host);
    }
}
