<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Logger\Handler;

use DateTimeImmutable;
use Exception;
use Ox\Core\Elastic\ElasticObjectManager;
use Ox\Core\Elastic\ElasticObjectSettings;
use Ox\Core\Logger\Formatter\ElasticObjectFormatter;
use Ox\Core\Logger\Handler\ApplicationElasticHandler;
use Ox\Core\Logger\Handler\ErrorElasticHandler;
use Ox\Mediboard\System\Elastic\ApplicationLog;
use Ox\Mediboard\System\Elastic\ApplicationLogRepository;
use Ox\Mediboard\System\Elastic\ErrorLog;
use Ox\Tests\OxUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionMethod;
use RuntimeException;

class ErrorElasticHandlerTest extends OxUnitTestCase
{
    private static ErrorLog $error_log;
    private static array    $record1;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$record1   = [
            "message"    => "ErrorException",
            "context"    => [
                "exception" => new Exception("test1"),
            ],
            "level"      => 400,
            "level_name" => "ERROR",
            "channel"    => "error",
            "datetime"   => DateTimeImmutable::createFromFormat("Y-m-d", "2022-08-17"),
            "extra"      => [
                "user_id"        => 1,
                "server_ip"      => "172.18.0.2",
                "session_id"     => "9a8258a15234...",
                "microtime"      => "0.98537600 1660826302",
                "request_uuid"   => "46c13c0547e0df1b72db053b25ca8ca4",
                "type"           => "user_notice",
                "file"           => "modules/system/about.php",
                "signature_hash" => "8943573cbfd60cf6d1c4c1562a42bedf",
                "data"           => [
                    "stacktrace"   => [
                        [
                            "function" => "errorHandler",
                            "class"    => "Ox\Core\CError",
                            "type"     => "::",
                        ],
                        [
                            "file"     => "/var/www/html/modules/system/about.php",
                            "line"     => 30,
                            "function" => "trigger_error",
                        ],
                    ],
                    "param_GET"    => [
                        "m"   => "system",
                        "tab" => "about",
                    ],
                    "param_POST"   => [],
                    "session_data" => [
                        "system" => [
                            "tab" => "about",
                        ],
                    ],
                ],
                "count"          => 1,
            ],
        ];
        self::$error_log = self::getErrorLogMock(uniqid() . "-");
        ElasticObjectManager::init(self::$error_log);
        sleep(2);
    }

    public static function tearDownAfterClass(): void
    {
        ElasticObjectManager::getInstance()->clear(self::$error_log);
        parent::tearDownAfterClass();
    }

    /**
     * @config error_log_using_nosql 1
     *
     * @return void
     */
    public function testCanHandleWillReturnTrue(): void
    {
        $handler = new ErrorElasticHandler();

        $reflection = new ReflectionMethod(ErrorElasticHandler::class, 'canHandle');
        $reflection->setAccessible(true);
        $actual = $reflection->invoke($handler);

        $this->assertTrue($actual);
    }

    /**
     * @config error_log_using_nosql 0
     *
     * @return void
     */
    public function testCanHandleWillReturnFalse(): void
    {
        $handler = new ErrorElasticHandler();

        $reflection = new ReflectionMethod(ErrorElasticHandler::class, 'canHandle');
        $reflection->setAccessible(true);
        $actual = $reflection->invoke($handler);

        $this->assertFalse($actual);
    }

    /**
     * @return void
     */
    public function testTheDefaultFormatterIsElasticObjectFormatterWithApplicationLog(): void
    {
        $handler = new ErrorElasticHandler();
        /** @var ElasticObjectFormatter $formatter */
        $formatter = $handler->getFormatter();

        $this->assertInstanceOf(ErrorLog::class, $formatter->getElasticObject());
    }

    /**
     * @return void
     */
    public function testHandleBatchThrowsExceptionIfCanHandleReturnFalse(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot handle records');

        $handler = $this->getMockBuilder(ErrorElasticHandler::class)
            ->onlyMethods(['canHandle'])
            ->getMock();
        $handler->expects($this->any())
            ->method('canHandle')
            ->will($this->returnValue(false));

        /** @var ErrorElasticHandler $handler */
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
        $handler = $this->getErrorElasticHandlerMock();
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
        $handler = $this->getErrorElasticHandlerMock();
        $handler->setFormatter(new ElasticObjectFormatter(self::$error_log));
        $handler->handleBatch($records);
        ElasticObjectManager::getInstance()->refresh(self::$error_log);

        $repository = $this->getMockBuilder(ApplicationLogRepository::class)
            ->setConstructorArgs([self::$error_log])
            ->onlyMethods([])
            ->getMock();
        $result     = $repository->count();
        $this->assertEquals($expected_total_logs, $result);
    }

    /**
     * @config       elastic test_error_timeout elastic_host 10.0.0.5
     * @config       elastic test_error_timeout elastic_port 9201
     * @config       elastic test_error_timeout elastic_index test_application_timeout
     *
     * @param array $records
     *
     * @return void
     */
    public function testHandleBatchWithRecordsWillThrowAnException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error sending messages to Elasticsearch');

        $settings = new ElasticObjectSettings("test_error_timeout");

        /** @var MockObject&ApplicationLog $mock_error_log */
        $mock_error_log = $this->getMockBuilder(ErrorLog::class)
            ->onlyMethods(['getSettings'])
            ->setMockClassName("Mock_ErrorLog_" . uniqid())
            ->getMock();
        $mock_error_log->expects($this->any())->method('getSettings')->willReturn($settings);

        $records = [
            self::$record1,
        ];

        /** @var ErrorElasticHandler $handler */
        $handler = $this->getErrorElasticHandlerMock();
        $handler->setFormatter(new ElasticObjectFormatter($mock_error_log));
        $handler->handleBatch($records);
    }

    /**
     * @param string $prefix
     *
     * @return MockObject&ErrorLog
     */
    public static function getErrorLogMock(string $prefix)
    {
        $testcase = new self();

        $settings = new ElasticObjectSettings(ErrorLog::DATASOURCE_NAME);
        $settings->setIndexName($prefix . $settings->getIndexName());

        /** @var MockObject&ErrorLog $mock_error_log */
        $mock_error_log = $testcase->getMockBuilder(ErrorLog::class)
            ->onlyMethods(['getSettings'])
            ->getMock();
        $mock_error_log->expects($testcase->any())->method('getSettings')->willReturn($settings);

        return $mock_error_log;
    }

    /**
     * @return ErrorElasticHandler&MockObject
     */
    private function getErrorElasticHandlerMock()
    {
        $mock = $this->getMockBuilder(ErrorElasticHandler::class)
            ->onlyMethods(['canHandle'])
            ->getMock();

        $mock->expects($this->any())
            ->method('canHandle')
            ->will($this->returnValue(true));

        return $mock;
    }

    public function recordsProvider(): array
    {
        $record1 = [
            "message"    => "ErrorException",
            "context"    => [
                "exception" => new Exception("test1"),
            ],
            "level"      => 400,
            "level_name" => "ERROR",
            "channel"    => "error",
            "datetime"   => DateTimeImmutable::createFromFormat("Y-m-d", "2022-08-17"),
            "extra"      => [
                "user_id"        => 1,
                "server_ip"      => "172.18.0.2",
                "session_id"     => "9a8258a15234...",
                "microtime"      => "0.98537600 1660826302",
                "request_uuid"   => "46c13c0547e0df1b72db053b25ca8ca4",
                "type"           => "user_notice",
                "file"           => "modules/system/about.php",
                "signature_hash" => "8943573cbfd60cf6d1c4c1562a42bedf",
                "data"           => [
                    "stacktrace"   => [
                        [
                            "function" => "errorHandler",
                            "class"    => "Ox\Core\CError",
                            "type"     => "::",
                        ],
                        [
                            "file"     => "/var/www/html/modules/system/about.php",
                            "line"     => 30,
                            "function" => "trigger_error",
                        ],
                    ],
                    "param_GET"    => [
                        "m"   => "system",
                        "tab" => "about",
                    ],
                    "param_POST"   => [],
                    "session_data" => [
                        "system" => [
                            "tab" => "about",
                        ],
                    ],
                ],
                "count"          => 1,
            ],
        ];

        $record2                         = $record1;
        $record2["context"]["exception"] = new Exception("test2");
        $record2["count"]                = 100;

        $record3                         = $record1;
        $record3["context"]["exception"] = new Exception("test3");
        $record3["count"]                = 1000;

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
