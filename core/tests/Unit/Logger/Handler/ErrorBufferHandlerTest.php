<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Logger\Handler;

use Error;
use ErrorException;
use Exception;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Ox\Core\CMbDT;
use Ox\Core\Kernel\Exception\HttpException;
use Ox\Core\Kernel\Exception\UnavailableApplicationException;
use Ox\Core\Logger\Handler\ErrorBufferHandler;
use Ox\Core\Logger\LoggerLevels;
use Ox\Core\Logger\Processor\ErrorProcessor;
use Ox\Core\Logger\Wrapper\ErrorLoggerWrapper;
use Ox\Mediboard\System\CErrorLogWhiteList;
use Ox\Tests\OxUnitTestCase;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

class ErrorBufferHandlerTest extends OxUnitTestCase
{
    private Logger             $logger;
    private TestHandler        $test_handler;
    private ErrorProcessor     $processor;
    private ErrorBufferHandler $buffer;
    private ReflectionProperty $error_count;

    /**
     * Setup the TestHandler with the ErrorBufferHandler
     * We need to reset the error_count value due to its static state
     * Then we implement the logger
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->processor    = new ErrorProcessor();
        $this->test_handler = new TestHandler();

        $mock = $this->getMockBuilder(ErrorBufferHandler::class)
            ->setConstructorArgs([$this->test_handler, 10])
            ->onlyMethods(["getTotalLimit", "getSameHashCountLimit", "getDistinctHashCountLimit"])
            ->getMock();

        $mock->expects($this->any())->method('getTotalLimit')->willReturn(800);
        $mock->expects($this->any())->method('getSameHashCountLimit')->willReturn(100);
        $mock->expects($this->any())->method('getDistinctHashCountLimit')->willReturn(10);
        $this->buffer = $mock;

        $this->error_count = new ReflectionProperty(ErrorBufferHandler::class, 'error_count');
        $this->error_count->setAccessible(true);
        $this->error_count->setValue($this->buffer, 0);

        $this->logger = new Logger(ErrorLoggerWrapper::CHANNEL, [$this->buffer], [$this->processor]);
    }

    /**
     * One loop of same hash log
     * The default BUFFER_LIMIT is set to 1000, when hit the buffer is sent data to the TestHandler
     * After 10000 logs we hit the SAME_HASH_COUNT_LIMIT
     * Due to batch aggregation we will have at maximum 10 logs in the TestHandler
     * Note: We need to close the logger to send the last buffer
     *
     * @dataProvider numberOfRecordsForLogWithSameHashProvider
     *
     * @param int $number_of_logs
     * @param int $expect_records_to_store
     *
     * @return void
     */
    public function testNumberOfRecordsStoredFromSameHash(int $number_of_logs, int $expect_records_to_store): void
    {
        for ($i = 0; $i < $number_of_logs; $i++) {
            $this->logger->log(LoggerLevels::LEVEL_INFO, 'deprecated', ["exception" => new Exception('deprecated')]);
        }
        $this->logger->close();

        $this->assertEquals($expect_records_to_store, count($this->test_handler->getRecords()));
    }

    /**
     * One loop of different hash log
     * The default BUFFER_LIMIT is set to 1000, when hit the buffer is sent data to the TestHandler
     * After 1000 logs we hit the DISTINCT_HASH_COUNT_LIMIT
     * Since there are no aggregation on distinct hash log we will have at maximum 1000 logs
     * Note: We need to close the logger to send the last buffer
     *
     * @dataProvider numberOfRecordsForLogWithDifferentHashProvider
     *
     * @param int $number_of_logs
     * @param int $expect_records_to_store
     *
     * @return void
     */
    public function testNumberOfRecordsStoredFromDifferentHash(int $number_of_logs, int $expect_records_to_store): void
    {
        for ($i = 0; $i < $number_of_logs; $i++) {
            $prefix = 'deprecated_';
            for ($j = 0; $j < 20; $j++) {
                $prefix .= chr(64 + rand(0, 26));
            }
            $message = uniqid($prefix);
            $this->logger->log(LoggerLevels::LEVEL_INFO, $message, ["exception" => new Exception($message)]);
        }
        $this->logger->close();

        $this->assertEquals($expect_records_to_store, count($this->test_handler->getRecords()));
    }

    /**
     * Two loops, the first one to change the hash of the log, the other one to log $number_of_logs times the hash
     * The default BUFFER_LIMIT is set to 1000, when hit the buffer is sent data to the TestHandler
     * If we hit the SAME_HASH_COUNT_LIMIT (10000) for the $number_of_logs no other logs will be sent
     * If we hit the DISTINCT_HASH_COUNT_LIMIT (1000) for the $number_of_loops,
     * All the logs will be sent except for the last one
     *
     * In some case, this function can hit the TOTAL_LIMIT of logs witch is set to 100 000.
     * Once this limit is hit no other logs will be sent.
     *
     * @dataProvider numberOfLoopDifferentAndLogsSimilarForRecords
     *
     * @param int $number_of_loops
     * @param int $number_of_logs
     * @param int $expect_records_to_store
     *
     * @return void
     */
    public function testNumberOfRecordsStoredFromWith1000SimilarHashAndDifferentHash(
        int $number_of_loops,
        int $number_of_logs,
        int $expect_records_to_store
    ): void {
        for ($i = 0; $i < $number_of_loops; $i++) {
            $prefix = 'deprecated_';
            for ($j = 0; $j < 100; $j++) {
                $prefix .= chr(64 + rand(0, 26));
            }
            $message = uniqid($prefix);
            for ($j = 0; $j < $number_of_logs; $j++) {
                $this->logger->log(LoggerLevels::LEVEL_INFO, $message, ["exception" => new Exception($message)]);
            }
        }
        $this->logger->close();

        $this->assertEquals($expect_records_to_store, count($this->test_handler->getRecords()));
    }

    /**
     * This function try to maximize the number of record that can be sent with the ErrorBufferHandle
     * To do that, we maximize the number of record inside a batch:
     * - A batch is 1000 records
     * - But there is a limit of 1000 Distinct hash
     * - Each batch is composed of 999 distinct hash + one that already exist
     *
     * With this function we can hit the maximum of 99900 logs in one hit
     *
     * @dataProvider numberOfLoopToMaximizeNumberOfRecord
     *
     * @param int $loop
     * @param int $expect_records_to_store
     *
     * @return void
     */
    public function testMaximizeNumberOfRecord(int $loop, int $expect_records_to_store): void
    {
        $this->generateMaximumRecords($loop);

        $this->logger->close();

        $this->assertEquals($expect_records_to_store, count($this->test_handler->getRecords()));
    }

    /**
     * Optimise the different limits of the ErrorBufferHandler
     * To generate as much log as possible
     *
     * @param int $loop
     *
     * @return void
     */
    private function generateMaximumRecords(int $loop): void
    {
        $exceptions = [];
        for ($i = 0; $i < 9; $i++) {
            $prefix = 'deprecated_';
            for ($j = 0; $j < 20; $j++) {
                $prefix .= chr(64 + rand(0, 26));
            }
            $exceptions[] = new Exception(uniqid($prefix));
        }

        for ($i = 0; $i < $loop; $i++) {
            for ($j = 0; $j < 9; $j++) {
                $this->logger->log(
                    LoggerLevels::LEVEL_INFO,
                    $exceptions[$j]->getMessage(),
                    ["exception" => $exceptions[$j]]
                );
            }
            $this->logger->log(
                LoggerLevels::LEVEL_INFO,
                $exceptions[$i % 9]->getMessage(),
                ["exception" => $exceptions[$i % 9]]
            );
        }
    }


    /**
     * Check if the default condition of the level is verified
     *
     * @return void
     */
    public function testBufferWillNotHandleTheLogIfUnderHisLevel(): void
    {
        // LEVEL_DEBUG is under LEVEL_INFO witch is the default of ErrorBufferHandler
        $this->logger->log(LoggerLevels::LEVEL_DEBUG, "test", ["exception" => new Exception("test")]);
        $this->logger->close();

        $this->assertEquals(0, count($this->test_handler->getRecords()));
    }

    /**
     * Check if there is no Exception that ErrorBufferHandler does not handle the record
     *
     * @return void
     */
    public function testBufferWillNotHandleTheLogIfHasNotAnExceptionInTheContext(): void
    {
        $this->logger->log(LoggerLevels::LEVEL_INFO, "test");
        $this->logger->close();

        $this->assertEquals(0, count($this->test_handler->getRecords()));
    }

    /**
     * Check if the Exception is an instance of CHTTPException and the exception is not loggable
     * ErrorBufferHandler does not handle the record
     *
     * @return void
     */
    public function testBufferWillNotHandleTheLogIfTheExceptionIsInstanceOfCHTTPExceptionAndIsNotLoggable(): void
    {
        $exception = new UnavailableApplicationException(500);
        $this->logger->log(LoggerLevels::LEVEL_DEBUG, "test", ["exception" => $exception]);
        $this->logger->close();

        $this->assertEquals(0, count($this->test_handler->getRecords()));
    }

    /**
     * This will check if the inserted CErrorLogWhiteList is in the array of error_whitelist
     *
     * @return void
     * @throws ReflectionException
     */
    public function testInitWhitelist(): void
    {
        $exception = new Exception("test init whitelist : " . uniqid());
        $error     = $this->logAnException($exception);
        $whitelist = $this->createWhitelistFromRecord($error);

        $buffer = new ErrorBufferHandler(new TestHandler());

        $init = new ReflectionMethod(ErrorBufferHandler::class, 'initWhiteList');
        $init->setAccessible(true);
        $init->invoke($buffer);

        $property = new ReflectionProperty(ErrorBufferHandler::class, 'error_whitelist');
        $property->setAccessible(true);
        $error_whitelist = $property->getValue($buffer);

        $expected_hash = $error['extra']['signature_hash'];
        $this->assertArrayHasKey($expected_hash, $error_whitelist);

        $whitelist->delete();
    }

    /**
     * This will check that the count increase when logging an error in the whitelist
     *
     * @return void
     * @throws Exception
     */
    public function testErrorWhitelistCount(): void
    {
        $exception = new Exception("test count whitelist log: " . uniqid());
        $error     = $this->logAnException($exception);
        $whitelist = $this->createWhitelistFromRecord($error);

        $buffer       = new ErrorBufferHandler(new TestHandler());
        $this->logger = new Logger(ErrorLoggerWrapper::CHANNEL, [$buffer], [$this->processor]);

        for ($i = 0; $i < 10; $i++) {
            $this->logger->log(LoggerLevels::LEVEL_INFO, $exception->getMessage(), ["exception" => $exception]);
        }
        $this->logger->close();

        $property = new ReflectionProperty(ErrorBufferHandler::class, 'error_whitelist');
        $property->setAccessible(true);
        $error_whitelist = $property->getValue($buffer);

        $expected_hash = $error['extra']['signature_hash'];
        $this->assertArrayHasKey($expected_hash, $error_whitelist);
        $this->assertEquals(10, $error_whitelist[$expected_hash]);
        // First Log + 1o for the whitelist
        $this->assertEquals(11, $buffer::getErrorCount());

        $whitelist->delete();
    }

    /**
     * Verify that the update of the error log whitelist is done in the close function of the ErrorBufferHandler
     * And verify the count of the Whitelist
     *
     * @return void
     * @throws Exception
     */
    public function testStoreOfWhitelistOnlyOccursOnClose(): void
    {
        $exception = new Exception("test store on close: " . uniqid());
        $error     = $this->logAnException($exception);
        $this->createWhitelistFromRecord($error);

        $buffer       = new ErrorBufferHandler(new TestHandler());
        $this->logger = new Logger(ErrorLoggerWrapper::CHANNEL, [$buffer], [$this->processor]);
        for ($i = 0; $i < 20; $i++) {
            $this->logger->log(LoggerLevels::LEVEL_INFO, $exception->getMessage(), ["exception" => $exception]);
        }

        $whitelist       = new CErrorLogWhiteList();
        $whitelist->hash = $error['extra']['signature_hash'];
        $whitelist->loadMatchingObject();

        $this->assertEquals(0, $whitelist->count);

        $this->logger->close();

        $whitelist       = new CErrorLogWhiteList();
        $whitelist->hash = $error['extra']['signature_hash'];
        $whitelist->loadMatchingObject();

        $this->assertEquals(20, $whitelist->count);

        $whitelist->delete();
    }

    /**
     * @param Exception $exception
     *
     * @return array
     */
    public function logAnException(Exception $exception): array
    {
        $this->logger->log(LoggerLevels::LEVEL_INFO, $exception->getMessage(), ["exception" => $exception]);
        $this->logger->close();

        return $this->test_handler->getRecords()[0];
    }

    /**
     * @param array $error
     *
     * @return CErrorLogWhiteList
     * @throws Exception
     */
    public function createWhitelistFromRecord(array $error): CErrorLogWhiteList
    {
        $error_log_whitelist              = new CErrorLogWhiteList();
        $error_log_whitelist->hash        = $error['extra']['signature_hash'];
        $error_log_whitelist->text        = $error['message'];
        $error_log_whitelist->type        = $error['extra']['type'];
        $error_log_whitelist->file_name   = $error['extra']['file'];
        $error_log_whitelist->line_number = $error['context']['exception']->getLine();
        $error_log_whitelist->user_id     = $error['extra']['user_id'];
        $error_log_whitelist->datetime    = CMbDT::dateTime();
        $error_log_whitelist->count       = 0;
        $error_log_whitelist->store();

        return $error_log_whitelist;
    }

    /**
     * @dataProvider limitGetterProvider
     *
     * @param string $getter
     * @param int    $expected_limit
     *
     * @return void
     * @throws ReflectionException
     */
    public function testDefaultTotalLimitValueShouldBe100000(string $getter, int $expected_limit): void
    {
        $buffer     = new ErrorBufferHandler(new TestHandler());
        $reflection = new ReflectionMethod(ErrorBufferHandler::class, $getter);
        $reflection->setAccessible(true);
        $actual = $reflection->invoke($buffer);

        $this->assertEquals($expected_limit, $actual);
    }

    /**
     * @dataProvider canLogProvider
     */
    public function testCanLog(array $record, bool $expected): void
    {
        $buffer = new ErrorBufferHandler(new TestHandler());
        $this->assertEquals($expected, $this->invokePrivateMethod($buffer, 'canLog', $record));
    }

    public function canLogProvider(): array
    {
        return [
            'too_low_level'               => [
                [
                    'channel' => 'php',
                    'level'   => LoggerLevels::LEVEL_DEBUG,
                    'context' => ['exception' => new Exception()],
                ],
                false,
            ],
            'no_exception'                => [
                [
                    'channel' => 'php',
                    'level'   => LoggerLevels::LEVEL_INFO,
                ],
                false,
            ],
            'error_on_php_channel'        => [
                [
                    'channel' => 'php',
                    'level'   => LoggerLevels::LEVEL_INFO,
                    'context' => ['exception' => new Error()],
                ],
                false,
            ],
            'error_on_request'            => [
                [
                    'channel' => 'request',
                    'level'   => LoggerLevels::LEVEL_INFO,
                    'context' => ['exception' => new Error()],
                ],
                true,
            ],
            'non_loggable_http_exception' => [
                [
                    'channel' => 'php',
                    'level'   => LoggerLevels::LEVEL_INFO,
                    'context' => ['exception' => new UnavailableApplicationException(500)],
                ],
                false,
            ],
            'loggable_http_exception'     => [
                [
                    'channel' => 'php',
                    'level'   => LoggerLevels::LEVEL_INFO,
                    'context' => ['exception' => new HttpException(500)],
                ],
                true,
            ],
            'basic_exception'             => [
                [
                    'channel' => 'php',
                    'level'   => LoggerLevels::LEVEL_INFO,
                    'context' => ['exception' => new Exception()],
                ],
                true,
            ],
            'error_exception'     => [
                [
                    'channel' => 'php',
                    'level'   => LoggerLevels::LEVEL_INFO,
                    'context' => ['exception' => new ErrorException()],
                ],
                true,
            ],
        ];
    }

    public function limitGetterProvider(): array
    {
        return [
            "Default TOTAL_LIMIT should be 100 000"            => [
                'getTotalLimit',
                100_000,
            ],
            "Default SAME_HASH_COUNT_LIMIT should be 10 000"   => [
                'getSameHashCountLimit',
                10_000,
            ],
            "Default DISTINCT_HASH_COUNT_LIMIT should be 1000" => [
                'getDistinctHashCountLimit',
                1000,
            ],
        ];
    }

    public function numberOfRecordsForLogWithSameHashProvider(): array
    {
        return [
            "1 logs will return 1 record"                                    => [1, 1],
            "9 logs will return 1 record - under BUFFER_LIMIT"               => [9, 1],
            "10 logs will return 1 record - equals BUFFER_LIMIT"             => [10, 1],
            "11 logs will return 2 record - over BUFFER_LIMIT"               => [11, 2],
            "999 logs will return 10 record - under SAME_HASH_COUNT_LIMIT"   => [99, 10],
            "1000 logs will return 10 record - equals SAME_HASH_COUNT_LIMIT" => [1000, 10],
            "1001 logs will return 10 record - over SAME_HASH_COUNT_LIMIT"   => [1001, 10],
            "2000 logs will return 10 record - over SAME_HASH_COUNT_LIMIT"   => [2000, 10],
        ];
    }

    public function numberOfRecordsForLogWithDifferentHashProvider(): array
    {
        return [
            "1 logs will return 1 record"                                                     => [1, 1],
            "9 logs will return 9 record - under DISTINCT_HASH_COUNT_LIMIT & BUFFER_LIMIT"    => [9, 9],
            "10 logs will return 10 record - equals DISTINCT_HASH_COUNT_LIMIT & BUFFER_LIMIT" => [10, 10],
            "11 logs will return 10 record - over DISTINCT_HASH_COUNT_LIMIT & BUFFER_LIMIT"   => [11, 10],
            "50 logs will return 10 record - over DISTINCT_HASH_COUNT_LIMIT"                  => [50, 10],
        ];
    }

    public function numberOfLoopDifferentAndLogsSimilarForRecords(): array
    {
        return [
            "1 different logs occurred 10 times will return 1 record"     => [1, 10, 1],
            "9 different logs occurred 10 times will return 9 record"     => [9, 10, 9],
            "10 different logs occurred 10 times will return 10 record"   => [10, 10, 10],
            "11 different logs occurred 10 times will return 10 record"   => [11, 10, 10],
            "100 different logs occurred 10 times will return 10 record"  => [50, 10, 10],
            "1 different logs occurred 80 times will return 9 record"     => [1, 80, 8],
            "9 different logs occurred 80 times will return 90 record"    => [9, 80, 72],
            "10 different logs occurred 80 times will return 99 record"   => [10, 80, 73],
            "11 different logs occurred 80 times will return 1000 record" => [11, 80, 73],
            "1 different logs occurred 100 times will return 10 record"   => [1, 100, 10],
            "2 different logs occurred 100 times will return 10 record"   => [2, 100, 10],
        ];
    }

    public function numberOfLoopToMaximizeNumberOfRecord(): array
    {
        return [
            "1 loop of (9 different logs then send the buffer with a similar log)"  => [1, 9],
            "2 loop of (9 different logs then send the buffer with a similar log)"  => [2, 18],
            "10 loop of (9 different logs then send the buffer with a similar log)" => [10, 90],
            "11 loop of (9 different logs then send the buffer with a similar log)" => [11, 99],
            "50 loop of (9 different logs then send the buffer with a similar log)" => [50, 450],
            "79 loop of (9 different logs then send the buffer with a similar log)" => [79, 711],
            "80 loop of (9 different logs then send the buffer with a similar log)" => [80, 720],
            "81 loop of (9 different logs then send the buffer with a similar log)" => [81, 720],
        ];
    }
}
