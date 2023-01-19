<?php

/**
 * @package Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ftp\Tests\Unit;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CMbPath;
use Ox\Core\Contracts\Client\SOAPClientInterface;
use Ox\Interop\Eai\Resilience\ClientContext;
use Ox\Interop\Webservices\CSOAPLegacy;
use Ox\Interop\Webservices\CSourceSOAP;
use Ox\Interop\Webservices\ResilienceSOAPClient;
use Ox\Tests\OxUnitTestCase;
use stdClass;

class CSOAPTest extends OxUnitTestCase
{
    /** @var CSourceSOAP */
    protected static $source;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $source                 = new CSourceSOAP();
        $source->loggable       = "0";
        $source->name           = 'test_TU_SOAP';
        $source->host           = 'localhost';
        $source->active         = 1;
        $source->role           = CAppUI::conf('instance_role');
        $source->retry_strategy = "1|5 5|60 10|120 20|";

        if ($msg = $source->store()) {
            throw new CMbException($msg);
        }

        self::$source = $source;
    }

    public function testTruncate(): void
    {
        $text = new stdClass();
        $this->assertInstanceOf(stdClass::class, CSourceSOAP::truncate($text));

        // length 100
        $text = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labor.';

        $string = null;
        for ($i = 0; $i <= 10; $i++) {
            $string .= $text;
        }

        $string_truncated = CSourceSOAP::truncate($string);

        $this->assertStringEndsWith('... [1100 bytes]', $string_truncated);

        $this->assertEquals(1024 + 13, strlen($string_truncated));
    }

    public function testUpdateEncryptedFile(): void
    {
        $source             = self::$source;
        $source->passphrase = "testpassphrase";
        $source->updateEncryptedFields();

        $this->assertNotNull($source->passphrase);
        $this->assertIsString($source->passphrase);
        $this->assertEquals("testpassphrase", $source->passphrase);
    }

    public function testUpdateEncryptedFileEmpty(): void
    {
        $source             = self::$source;
        $source->passphrase = "";
        $source->updateEncryptedFields();

        $this->assertNull($source->passphrase);
    }

    public function testGetHeader(): void
    {
        $source = self::$source;
        $client = $source->getClient();
        $header = $client->getHeaders();

        $this->assertIsArray($header);
    }

    public function testGetError(): void
    {
        $source           = self::$source;
        $source->_message = "test";
        $client           = $source->getClient();
        $error            = $client->getError();

        $this->assertIsString($error);
        $this->assertEquals("test", $error);
    }

    public function testSetHeader(): void
    {
        $source = self::$source;

        $header = $source->getClient()->getHeaders();
        $this->assertIsArray($header);
        $this->assertEmpty($header);

        $namespace = "test";
        $name      = "test";
        $data      = ["value1", "value2", 1234];

        $source->setHeaders($namespace, $name, $data);

        $header = $source->getClient()->getHeaders();
        $this->assertIsArray($header);
        $this->assertNotEmpty($header);
    }

    public function testGetClient(): void
    {
        $source = $this->getMockBuilder(CSourceSOAP::class)->getMock();
        $client = $this->getMockBuilder(SOAPClientInterface::class)->getMock();
        $source->method('getClient')->willReturn($client);
        $this->assertInstanceOf(SOAPClientInterface::class, $client);
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
        $this->assertInstanceOf(ResilienceSOAPClient::class, $client);
    }

    public function testGetClientOx(): void
    {
        $source                 = self::$source;
        $source->retry_strategy = "";
        $source->_client        = "";
        $client                 = $source->getClient();
        $this->assertInstanceOf(CSOAPLegacy::class, $client);
    }

    public function testOnBeforeRequestIsNotLoggable(): void
    {
        $source           = self::$source;
        $source->loggable = "0";
        $client           = $source->getClient();
        $client_context   = new ClientContext($client, $source);

        $source->_dispatcher->dispatch($client_context, $client::EVENT_BEFORE_REQUEST);

        $this->assertNotNull($source->_current_echange);
    }

    public function testOnBeforeRequest(): void
    {
        $source           = self::$source;
        $source->host     = "/";
        $source->loggable = "1";
        $client           = $source->getClient();
        $client_context   = new ClientContext($client, $source);

        $source->_dispatcher->dispatch($client_context, $client::EVENT_BEFORE_REQUEST);

        $this->assertNotNull($source->_current_echange);

        $this->assertNotNull($source->_current_echange->date_echange);
        $this->assertIsString($source->_current_echange->date_echange);

        $this->assertNotNull($source->_current_echange->destinataire);
        $this->assertIsString($source->_current_echange->destinataire);

        $this->assertNotNull($source->_current_echange->source_id);
        $this->assertIsInt($source->_current_echange->source_id);
    }

    public function testOnAfterRequest(): void
    {
        $source           = self::$source;
        $source->host     = "/";
        $source->loggable = "1";
        $source->_client  = "";
        $client           = $source->getClient();
        $client_context   = new ClientContext($client, $source);

        $source->_dispatcher->dispatch($client_context, $client::EVENT_BEFORE_REQUEST);
        $source->_dispatcher->dispatch($client_context, $client::EVENT_AFTER_REQUEST);

        $this->assertNotNull($source->_current_echange);

        $this->assertNotNull($source->_current_echange->date_echange);
        $this->assertIsString($source->_current_echange->date_echange);

        $this->assertNotNull($source->_current_echange->destinataire);
        $this->assertIsString($source->_current_echange->destinataire);

        $this->assertNotNull($source->_current_echange->source_id);

        $this->assertNotNull($source->_current_echange->response_time);
        $this->assertIsFloat($source->_current_echange->response_time);
        $this->assertGreaterThan(0, $source->_current_echange->response_time);
    }

    public function testOnException(): void
    {
        $source           = self::$source;
        $source->host     = "/";
        $source->loggable = "1";
        $source->_client  = "";
        $client           = $source->getClient();
        $client_context   = new ClientContext($client, $source);
        $client_context->setResponse("test methode onException");

        $source->_dispatcher->dispatch($client_context, $client::EVENT_BEFORE_REQUEST);
        $source->_dispatcher->dispatch($client_context, $client::EVENT_EXCEPTION);

        $this->assertNotNull($source->_current_echange);

        $this->assertNotNull($source->_current_echange->response_datetime);
        $this->assertIsString($source->_current_echange->response_datetime);

        $this->assertNotNull($source->_current_echange->output);
        $this->assertIsString($source->_current_echange->output);
    }
}
