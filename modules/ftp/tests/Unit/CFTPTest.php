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
use Ox\Core\Contracts\Client\FTPClientInterface;
use Ox\Interop\Ftp\CFTP;
use Ox\Core\Chronometer;
use Ox\Core\CMbException;
use Ox\Interop\Eai\Resilience\ClientContext;
use Ox\Interop\Ftp\CSourceFTP;
use Ox\Interop\Ftp\ResilienceFTPClient;
use Ox\Tests\OxUnitTestCase;
use stdClass;

class CFTPTest extends OxUnitTestCase
{
    /** @var CSourceFTP */
    protected static $source;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $source                 = new CSourceFTP();
        $source->loggable       = "0";
        $source->name           = 'test_TU_FTP';
        $source->host           = 'localhost';
        $source->active         = 1;
        $source->role           = CAppUI::conf('instance_role');
        $source->retry_strategy = "1|5 5|60 10|120 20|";

        if ($msg = $source->store()) {
            throw new CMbException($msg);
        }

        self::$source = $source;
    }

    /**
     * TestTruncate
     */
    public function testTruncate(): void
    {
        $text = new stdClass();
        $this->assertInstanceOf(stdClass::class, CFTP::truncate($text));

        // length 100
        $text = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labor.';

        $string = null;
        for ($i = 0; $i <= 10; $i++) {
            $string .= $text;
        }

        $string_truncated = CFTP::truncate($string);

        $this->assertStringEndsWith('... [1100 bytes]', $string_truncated);

        $this->assertEquals(1024 + 13, strlen($string_truncated));
    }

    /**
     * @return void
     */
    public function testInitError(): void
    {
        $ftp             = new CFTP();
        $exchange_source = new CSourceFTP();
        $this->expectException(CMbException::class);
        $ftp->init($exchange_source);
    }

    /**
     * @return void
     */
    public function testInit(): void
    {
        $ftp = $this->getClientFTP();
        $this->assertInstanceOf(CSourceFTP::class, $ftp->_source);
    }

    private function getClientFTP(): CFTP
    {
        $ftp             = new CFTP();
        $exchange_source = self::$source;
        $ftp->init($exchange_source);

        return $ftp;
    }

    public function testCallError(): void
    {
        $ftp = $this->getClientFTP();
        $this->expectException(\Error::class);
        $ftp->toto();
    }

    /**
     * @return void
     */
    public function testCallLoggable(): void
    {
        CApp::$chrono = new Chronometer();
        CApp::$chrono->start();

        $mock = $this->getMockBuilder(CFTP::class)
            ->onlyMethods(['isReachableSource'])
            ->getMock();

        $mock->method('isReachableSource')->willReturn(true);

        $exchange_source = self::$source;

        $mock->init($exchange_source);

        $this->assertTrue($mock->isReachableSource());
    }

    public function testConnectKo(): void
    {
        $ftp = $this->getClientFTP();
        $this->expectException(CMbException::class);
        $ftp->isReachableSource();

        $this->assertEquals($ftp->_source->_reachable, 0);
        $this->assertNotNull($ftp->_source->_message);
    }

    /**
     * @return void
     */
    public function testGenerateNameFile(): void
    {
        $res   = CSourceFTP::generateFileName();
        $regex = "/(\d.+\_\d.+)/";
        $this->assertTrue((bool)preg_match($regex, $res));
    }

    public function testGetClient(): void
    {
        $source = $this->getMockBuilder(CSourceFTP::class)->getMock();
        $client = $this->getMockBuilder(FTPClientInterface::class)->getMock();
        $source->method('getClient')->willReturn($client);
        $this->assertInstanceOf(FTPClientInterface::class, $client);
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
        $this->assertInstanceOf(ResilienceFTPClient::class, $client);
    }

    public function testGetClientOx(): void
    {
        $source                 = self::$source;
        $source->retry_strategy = "";
        $source->_client        = "";
        $client                 = $source->getClient();
        $this->assertInstanceOf(CFTP::class, $client);
    }

    public function testOnBeforeRequestIsNotLoggable(): void
    {
        $source           = self::$source;
        $source->loggable = "0";
        $client           = $source->getClient();
        $client_context   = new ClientContext($client, $source);

        $source->_dispatcher->dispatch($client_context, $client::EVENT_BEFORE_REQUEST);

        $this->assertNull($source->_current_echange);
    }

    public function testOnBeforeRequest(): void
    {
        $source           = self::$source;
        $source->host     = "www.test.com";
        $source->_client  = "";
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
        $source         = self::$source;
        $source->host   = "www.test.com";
        $client         = $source->getClient();
        $client_context = new ClientContext($client, $source);

        $source->_dispatcher->dispatch($client_context, $client::EVENT_BEFORE_REQUEST);
        $source->_dispatcher->dispatch($client_context, $client::EVENT_AFTER_REQUEST);

        $this->assertNotNull($source->_current_echange);

        $this->assertNotNull($source->_current_echange->date_echange);
        $this->assertIsString($source->_current_echange->date_echange);

        $this->assertNotNull($source->_current_echange->destinataire);
        $this->assertIsString($source->_current_echange->destinataire);

        $this->assertNotNull($source->_current_echange->source_id);
        $this->assertIsInt($source->_current_echange->source_id);

        $this->assertNotNull($source->_current_echange->response_time);
        $this->assertIsFloat($source->_current_echange->response_time);
        $this->assertGreaterThan(0, $source->_current_echange->response_time);

        $this->assertNotNull($source->_current_echange->response_datetime);
        $this->assertIsString($source->_current_echange->response_datetime);
    }

    public function testOnException(): void
    {
        $source         = self::$source;
        $source->host   = "www.test.com";
        $client         = $source->getClient();
        $client_context = new ClientContext($client, $source);
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
