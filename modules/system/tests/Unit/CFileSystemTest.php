<?php
/**
 * @package Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Unit;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbPath;
use Ox\Core\Contracts\Client\FileSystemClientInterface;
use Ox\Interop\Eai\Resilience\ClientContext;
use Ox\Mediboard\System\CFileSystem;
use Ox\Core\Chronometer;
use Ox\Core\CMbException;
use Ox\Mediboard\System\ResilienceFileSystemClient;
use Ox\Mediboard\System\CSourceFileSystem;
use Ox\Tests\OxUnitTestCase;
use stdClass;

class CFileSystemTest extends OxUnitTestCase
{
    /** @var CFileSystem */
    protected static $source;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $source                 = new CSourceFileSystem();
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


    /**
     * @return CFileSystem
     */
    public function testInit(): CFileSystem
    {
        $fs              = new CFileSystem();
        $exchange_source = self::$source;
        $fs->init($exchange_source);

        $this->assertInstanceOf(CSourceFileSystem::class, $exchange_source);

        return $fs;
    }

    /**
     * @return bool
     * @throws CMbException
     */
    public function testSend(): void
    {
        CMbPath::forceDir(CAppUI::getTmpPath("source"));
        $host_name             = CAppUI::getTmpPath("source");
        $exchange_source       = self::$source;
        $exchange_source->host = $host_name;
        $exchange_source->setData("test");

        $client = $exchange_source->getClient();
        $client->send("test.txt");
        $this->assertFileExists($host_name . "/test.txt");
    }

    /**
     * @return array
     * @throws CMbException
     */
    public function testReceive(): void
    {
        $source = self::$source;
        $fs     = $source->getClient();
        $res    = $fs->receive();
        $this->assertNotNull($res);
        $this->assertIsArray($res);
    }

    public function testCreateDirectory(): void
    {
        CMbPath::forceDir(CAppUI::getTmpPath("source"));
        $host_name                = CAppUI::getTmpPath("source");
        $exchange_source          = self::$source;
        $exchange_source->_client = "";
        $exchange_source->host    = $host_name;
        $exchange_source->setData("test");

        $client = $exchange_source->getClient();
        $client->createDirectory("directory_created");
        $this->assertDirectoryExists($host_name . "/directory_created/");
    }

    public function testGetCurrentDirectory(): void
    {
        CMbPath::forceDir(CAppUI::getTmpPath("source"));
        $host_name             = CAppUI::getTmpPath("source");
        $exchange_source       = self::$source;
        $exchange_source->host = $host_name;
        $exchange_source->setData("test");
        $exchange_source->_client = "";
        $client                   = $exchange_source->getClient();
        $directory                = $client->getCurrentDirectory();
        $this->assertDirectoryExists($directory);
    }

    public function testGetListFilesDetails(): void
    {
        CMbPath::forceDir(CAppUI::getTmpPath("source"));
        $host_name             = CAppUI::getTmpPath("source");
        $exchange_source       = self::$source;
        $exchange_source->host = $host_name;
        $exchange_source->setData("test");
        $exchange_source->_client = "";
        $client                   = $exchange_source->getClient();
        $client->send("test0.txt");
        $client->send("test1.txt");
        $client->send("test2.txt");
        $client->send("test3.txt");
        $client->send("test4.txt");

        $current_directory = $client->getCurrentDirectory();
        $listfiles         = $client->getListFilesDetails($current_directory);

        $this->assertIsArray($listfiles);
        $this->assertNotEmpty($listfiles);
    }

    public function testGetListDirectory(): void
    {
        CMbPath::forceDir(CAppUI::getTmpPath("source"));
        $host_name             = CAppUI::getTmpPath("source");
        $exchange_source       = self::$source;
        $exchange_source->host = $host_name;
        $exchange_source->setData("test");
        $exchange_source->_client = "";
        $client                   = $exchange_source->getClient();
        $current_directory        = $client->getCurrentDirectory();
        $listDirectory            = $client->getListDirectory($current_directory);

        $this->assertIsArray($listDirectory);
        $this->assertNotEmpty($listDirectory);
    }

    public function testAddFile(): void
    {
        CMbPath::forceDir(CAppUI::getTmpPath("source"));
        $host_name             = CAppUI::getTmpPath("source");
        $exchange_source       = self::$source;
        $exchange_source->host = $host_name;
        $exchange_source->setData("test");
        $exchange_source->_client = "";
        $name_file                = "addedFile.txt";
        $client                   = $exchange_source->getClient();

        if (file_exists($host_name . "/" . $name_file)) {
            $client->delFile($name_file);
        }

        $client->send("addedFile.txt");
        $this->assertFileExists($host_name . "/" . $name_file);

        $client->addFile($host_name . "/" . $name_file, "copy.txt");
        $this->assertFileExists($host_name . "/copy.txt");
    }

    public function testDelFile(): void
    {
        CMbPath::forceDir(CAppUI::getTmpPath("source"));
        $host_name             = CAppUI::getTmpPath("source");
        $exchange_source       = self::$source;
        $exchange_source->host = $host_name;
        $exchange_source->setData("test");
        $exchange_source->_client = "";
        $client                   = $exchange_source->getClient();

        $name_file = "test_delfile.txt";

        $client->send($name_file);
        $this->assertFileExists($host_name . "/" . $name_file);

        $client->delFile($name_file);
        $this->assertFileDoesNotExist($host_name . "/" . $name_file);
    }

    public function testGetData(): void
    {
        CMbPath::forceDir(CAppUI::getTmpPath("source"));
        $host_name             = CAppUI::getTmpPath("source");
        $exchange_source       = self::$source;
        $exchange_source->host = $host_name;
        $exchange_source->setData("test");
        $exchange_source->_client = "";
        $name_file                = "test_getdata.txt";
        $client                   = $exchange_source->getClient();

        if (file_exists($host_name . "/" . $name_file)) {
            $client->delFile($name_file);
        }

        $client->send($name_file);
        $this->assertFileExists($host_name . "/" . $name_file);
        $exchange_source->_client = "";
        $client                   = $exchange_source->getClient();
        $data                     = $client->getData($host_name . "/" . $name_file);

        $this->assertIsString($data);
    }

    public function testRenameFile(): void
    {
        CMbPath::forceDir(CAppUI::getTmpPath("source"));
        $host_name             = CAppUI::getTmpPath("source");
        $exchange_source       = self::$source;
        $exchange_source->host = $host_name;
        $exchange_source->setData("test");
        $exchange_source->_client = "";
        $client                   = $exchange_source->getClient();
        $client->send("test.txt");
        $client->renameFile("test.txt", "renamed.txt");

        $this->assertFileDoesNotExist($host_name . "/test.txt");
        $this->assertFileExists($host_name . "/renamed.txt");
    }

    public function testCallLoggable(): void
    {
        CApp::$chrono = new Chronometer();
        CApp::$chrono->start();

        $mock = $this->getMockBuilder(CFileSystem::class)
            ->setMethods(['_connect'])
            ->getMock();

        $mock->method('_connect')->willReturn(true);

        $exchange_source = self::$source;

        $mock->init($exchange_source);

        $this->assertTrue($mock->_connect());
    }

    public function testGenerateNameFile(): void
    {
        $res   = CSourceFileSystem::generateFileName();
        $regex = "/(\d.+\_\d.+)/";
        $this->assertTrue((bool)preg_match($regex, $res));
    }

    public function testUpdateFormField(): void
    {
        $source       = self::$source;
        $source->host = "tmp/out/";

        $this->assertNotEmpty($source->_view);

        $source->updateFormFields();

        $this->assertNotNull($source->_view);
        $this->assertIsString($source->_view);
    }

    public function testGetClient(): void
    {
        $source = $this->getMockBuilder(CSourceFileSystem::class)->getMock();
        $client = $this->getMockBuilder(FileSystemClientInterface::class)->getMock();
        $source->method('getClient')->willReturn($client);
        $this->assertInstanceOf(FileSystemClientInterface::class, $client);
    }

    public function testGetClientCache(): void
    {
        $source          = self::$source;
        $source->_client = "";
        $client          = $source->getClient();
        $this->assertSame($client, $source->getClient());
    }

    public function testGetClientRetryable(): void
    {
        $source          = self::$source;
        $source->_client = "";
        $client          = $source->getClient();
        $this->assertInstanceOf(ResilienceFileSystemClient::class, $client);
    }

    public function testGetClientOx(): void
    {
        $source                 = self::$source;
        $source->retry_strategy = "";
        $source->_client        = "";
        $client                 = $source->getClient();
        $this->assertInstanceOf(CFileSystem::class, $client);
    }

    public function testOnBeforeRequestIsNotLoggable(): void
    {
        $source         = self::$source;
        $client         = $source->getClient();
        $client_context = new ClientContext($client, $source);
        $source->_dispatcher->dispatch($client_context, $client::EVENT_BEFORE_REQUEST);
        $this->assertNull($source->_current_echange);
    }

    public function testOnBeforeRequest(): void
    {
        $source           = self::$source;
        $source->_client  = "";
        $source->loggable = "1";
        $client           = $source->getClient();

        $client_context = new ClientContext($client, $source);


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
        $source                 = self::$source;
        $source->loggable       = "1";
        $source->retry_strategy = "1|5 5|60 10|120 20|";
        $source->host           = "/";
        $client                 = $source->getClient();

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

        if ($source->_current_echange->output !== null) {
            $this->assertIsString($source->_current_echange->output);
        } else {
            $this->assertNull($source->_current_echange->output);
        }
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
}
