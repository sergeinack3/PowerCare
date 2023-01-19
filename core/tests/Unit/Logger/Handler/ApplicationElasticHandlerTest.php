<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Logger\Handler;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Ox\Core\Elastic\ElasticObjectManager;
use Ox\Core\Elastic\ElasticObjectSettings;
use Ox\Core\Logger\Formatter\ElasticObjectFormatter;
use Ox\Core\Logger\Handler\ApplicationElasticHandler;
use Ox\Mediboard\System\Elastic\ApplicationLog;
use Ox\Mediboard\System\Elastic\ApplicationLogRepository;
use Ox\Mediboard\System\Elastic\ErrorLog;
use Ox\Tests\OxUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionMethod;
use RuntimeException;

class ApplicationElasticHandlerTest extends OxUnitTestCase
{
    private static ApplicationLog $application_object;
    private static array $record1;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$record1 = [
            "message"    => "test",
            "context"    => [],
            "level"      => 200,
            "level_name" => "INFO",
            "channel"    => "app",
            "datetime"   => DateTimeImmutable::createFromFormat("Y-m-d", "2022-08-17"),
            "extra"      => [
                "user_id"    => 1,
                "server_ip"  => "172.0.0.1",
                "session_id" => "ae12a56cfd4...",
            ],
        ];
        self::$application_object = self::getApplicationLogMock(uniqid() . "-");
        ElasticObjectManager::init(self::$application_object);
        sleep(2);
    }

    public static function tearDownAfterClass(): void
    {
        ElasticObjectManager::getInstance()->clear(self::$application_object);
        parent::tearDownAfterClass();
    }

    /**
     * @config application_log_using_nosql 1
     *
     * @return void
     */
    public function testCanHandleWillReturnTrue(): void
    {
        $handler = new ApplicationElasticHandler();

        $reflection = new ReflectionMethod(ApplicationElasticHandler::class, 'canHandle');
        $reflection->setAccessible(true);
        $actual = $reflection->invoke($handler);

        $this->assertTrue($actual);
    }

    /**
     * @config application_log_using_nosql 0
     *
     * @return void
     */
    public function testCanHandleWillReturnFalse(): void
    {
        $handler = new ApplicationElasticHandler();

        $reflection = new ReflectionMethod(ApplicationElasticHandler::class, 'canHandle');
        $reflection->setAccessible(true);
        $actual = $reflection->invoke($handler);

        $this->assertFalse($actual);
    }

    /**
     * @dataProvider validFormatterProvider
     *
     * @param ElasticObjectFormatter $formatter
     *
     * @return void
     */
    public function testSetCorrectFormatter(ElasticObjectFormatter $formatter): void
    {
        /** @var ApplicationElasticHandler $handler */
        $handler = $this->getApplicationElasticHandlerMock();
        $result  = $handler->setFormatter($formatter);

        $this->assertInstanceOf(ApplicationElasticHandler::class, $result);
    }

    /**
     * @dataProvider invalidFormatterProvider
     *
     * @param ElasticObjectFormatter $formatter
     *
     * @return void
     */
    public function testSetIncorrectFormatter(FormatterInterface $formatter): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ElasticsearchHandler is only compatible with ElasticObjectFormatter');

        /** @var ApplicationElasticHandler $handler */
        $handler = $this->getApplicationElasticHandlerMock();
        $handler->setFormatter($formatter);
    }

    /**
     * @return void
     */
    public function testTheDefaultFormatterIsElasticObjectFormatterWithApplicationLog(): void
    {
        $handler = new ApplicationElasticHandler();
        /** @var ElasticObjectFormatter $formatter */
        $formatter = $handler->getFormatter();

        $this->assertInstanceOf(ApplicationLog::class, $formatter->getElasticObject());
    }

    /**
     * @return void
     */
    public function testHandleBatchThrowsExceptionIfCanHandleReturnFalse(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot handle records');

        $handler = $this->getMockBuilder(ApplicationElasticHandler::class)
            ->onlyMethods(['canHandle'])
            ->getMock();
        $handler->expects($this->any())
            ->method('canHandle')
            ->will($this->returnValue(false));

        /** @var ApplicationElasticHandler $handler */
        $handler->handleBatch([]);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testHandleBatchWithEmptyRecords(): void
    {
        $records = [];

        /** @var ApplicationElasticHandler $handler */
        $handler = $this->getApplicationElasticHandlerMock();
        $handler->handleBatch($records);

        $this->assertTrue(true);
    }

    /**
     * @dataProvider recordsProvider
     *
     * @param array $records
     *
     * @return void
     */
    public function testHandleBatchWithRecordsWillInsertIntoElastic(array $records, int $expected_total_logs): void
    {
        /** @var ApplicationElasticHandler $handler */
        $handler = $this->getApplicationElasticHandlerMock();
        $handler->setFormatter(new ElasticObjectFormatter(self::$application_object));
        $handler->handleBatch($records);
        ElasticObjectManager::getInstance()->refresh(self::$application_object);

        $repository = $this->getMockBuilder(ApplicationLogRepository::class)
            ->setConstructorArgs([self::$application_object])
            ->onlyMethods([])
            ->getMock();
        $result     = $repository->count();
        $this->assertEquals($expected_total_logs, $result);
    }

    /**
     * @config       elastic test_application_timeout elastic_host 10.0.0.5
     * @config       elastic test_application_timeout elastic_port 9201
     * @config       elastic test_application_timeout elastic_index test_application_timeout
     *
     * @param array $records
     *
     * @return void
     */
    public function testHandleBatchWithRecordsWillThrowAnException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error sending messages to Elasticsearch');

        $settings = new ElasticObjectSettings("test_application_timeout");

        /** @var MockObject&ApplicationLog $mock_application_log */
        $mock_application_log = $this->getMockBuilder(ApplicationLog::class)
            ->onlyMethods(['getSettings'])
            ->setMockClassName("Mock_ApplicationLog_" . uniqid())
            ->getMock();
        $mock_application_log->expects($this->any())->method('getSettings')->willReturn($settings);

        $records = [
            self::$record1,
        ];

        /** @var ApplicationElasticHandler $handler */
        $handler = $this->getApplicationElasticHandlerMock();
        $handler->setFormatter(new ElasticObjectFormatter($mock_application_log));
        $handler->handleBatch($records);
    }

    /**
     * @param string $prefix
     *
     * @return MockObject&ApplicationLog
     */
    public static function getApplicationLogMock(string $prefix)
    {
        $testcase = new self();

        $settings = new ElasticObjectSettings(ApplicationLog::DATASOURCE_NAME);
        $settings->setIndexName($prefix . $settings->getIndexName());

        /** @var MockObject&ApplicationLog $mock_application_log */
        $mock_application_log = $testcase->getMockBuilder(ApplicationLog::class)
            ->onlyMethods(['getSettings'])
            ->getMock();
        $mock_application_log->expects($testcase->any())->method('getSettings')->willReturn($settings);

        return $mock_application_log;
    }

    /**
     * @return ApplicationElasticHandler&MockObject
     */
    private function getApplicationElasticHandlerMock()
    {
        $mock = $this->getMockBuilder(ApplicationElasticHandler::class)
            ->onlyMethods(['canHandle'])
            ->getMock();

        $mock->expects($this->any())
            ->method('canHandle')
            ->will($this->returnValue(true));

        return $mock;
    }

    public function validFormatterProvider(): array
    {
        return [
            "Valid application formatter" => [
                new ElasticObjectFormatter(new ApplicationLog()),
            ],
            "Valid error formatter"       => [
                new ElasticObjectFormatter(new ErrorLog()),
            ],
        ];
    }

    public function invalidFormatterProvider(): array
    {
        return [
            "Invalid line formatter - Unauthorized Formatter" => [
                new LineFormatter(),
            ],
            "Invalid json formatter - Unauthorized Formatter" => [
                new JsonFormatter(),
            ],
        ];
    }

    public function recordsProvider(): array
    {
        $record1 = [
            "message"    => "test",
            "context"    => [],
            "level"      => 200,
            "level_name" => "INFO",
            "channel"    => "app",
            "datetime"   => DateTimeImmutable::createFromFormat("Y-m-d", "2022-08-17"),
            "extra"      => [
                "user_id"    => 1,
                "server_ip"  => "172.0.0.1",
                "session_id" => "ae12a56cfd4...",
            ],
        ];

        $record2               = $record1;
        $record2["message"]    = "test2";
        $record2["level"]      = 300;
        $record2["level_name"] = "WARNING";

        $record3               = $record1;
        $record2["message"]    = "test3";
        $record2["level"]      = 400;
        $record2["level_name"] = "ERROR";

        return [
            "Only one record"     => [
                [
                    $record1,
                ],
                1,
            ],
            "3 different records" => [
                [
                    $record1,
                    $record2,
                    $record3,
                ],
                4,
            ],
            "3 similar records"   => [
                [
                    $record3,
                    $record3,
                    $record3,
                ],
                7,
            ],
        ];
    }
}
