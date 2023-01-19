<?php

/**
 * @package Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ftp\Tests\Unit;

use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\Contracts\Client\MLLPClientInterface;
use Ox\Interop\Eai\Resilience\ClientContext;
use Ox\Interop\Hl7\CMLLPLegacy;
use Ox\Interop\Hl7\CSourceMLLP;
use Ox\Interop\Hl7\ResilienceMLLPClient;
use Ox\Tests\OxUnitTestCase;

class CMLLPTest extends OxUnitTestCase
{
    /** @var CSourceMLLP */
    protected static $source;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $source                 = new CSourceMLLP();
        $source->loggable       = "0";
        $source->name           = 'test_TU_MLLP';
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
        $source = $this->getMockBuilder(CSourceMLLP::class)->getMock();
        $client = $this->getMockBuilder(MLLPClientInterface::class)->getMock();
        $source->method('getClient')->willReturn($client);
        $this->assertInstanceOf(MLLPClientInterface::class, $client);
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
        $this->assertInstanceOf(ResilienceMLLPClient::class, $client);
    }

    public function testGetClientOx(): void
    {
        $source                 = self::$source;
        $source->retry_strategy = "";
        $source->_client        = "";
        $client                 = $source->getClient();
        $this->assertInstanceOf(CMLLPLegacy::class, $client);
    }

    public function testOnBeforeRequestIsNotLoggable(): void
    {
        $source           = self::$source;
        $source->loggable = "0";
        $source->_client  = "";
        $client           = $source->getClient();
        $client_context   = new ClientContext($client, $source);

        $source->_dispatcher->dispatch($client_context, $client::EVENT_BEFORE_REQUEST);

        $this->assertNull($source->_current_echange);
    }

    public function testOnBeforeRequest(): void
    {
        $source           = self::$source;
        $source->host     = "/";
        $source->loggable = "1";
        $source->_client  = "";
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
        $source->host   = "/";
        $client         = $source->getClient();
        $client_context = new ClientContext($client, $source);

        $source->_dispatcher->dispatch($client_context, $client::EVENT_BEFORE_REQUEST);
        $client_context->setResponse("test response");
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

        $this->assertNotNull($source->_current_echange->output);
        $this->assertIsString($source->_current_echange->output);
    }
    
    public function testOnException(): void
    {
        $source         = self::$source;
        $source->host   = "/";
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

    public function testUpdateFormField(): void
    {
        $source        = self::$source;
        $source->port  = 80;
        $source->_view = "";

        $this->assertEmpty($source->_view);

        $source->updateFormFields();

        $this->assertNotNull($source->_view);
        $this->assertIsInt($source->_view);
    }

    public function testUpdateEncryptedFile(): void
    {
        $source                 = self::$source;
        $source->ssl_passphrase = "testpassphrase";
        $source->updateEncryptedFields();

        $this->assertNotNull($source->ssl_passphrase);
        $this->assertIsString($source->ssl_passphrase);
    }

    public function testUpdateEncryptedFileEmpty(): void
    {
        $source                 = self::$source;
        $source->ssl_passphrase = "";
        $source->updateEncryptedFields();

        $this->assertNull($source->ssl_passphrase);
    }
}
