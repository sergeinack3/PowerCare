<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Logger\Formatter;

use DateTimeImmutable;
use Ox\Core\Logger\Formatter\ElasticObjectFormatter;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\Elastic\ApplicationLog;
use Ox\Tests\OxUnitTestCase;

class ElasticObjectFormatterTest extends OxUnitTestCase
{
    private static array          $record1 = [];
    private static array          $record2 = [];
    private static ApplicationLog $log1;
    private static ApplicationLog $log2;

    /**
     * @param string|null $name
     * @param array       $data
     * @param string      $dataName
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
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
        self::$log1    = new ApplicationLog();
        self::$log1->setMessage("test");
        self::$log1->setContext("[]");
        self::$log1->setLogLevel(200);
        self::$log1->setDate(DateTimeImmutable::createFromFormat("Y-m-d", "2022-08-17"));
        self::$log1->setUserId(1);
        self::$log1->setServerIp("172.0.0.1");
        self::$log1->setSessionId("ae12a56cfd4...");

        $context = [
            "user" => new CUser(),
        ];

        self::$record2 = [
            "message"    => "test2",
            "context"    => $context,
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

        self::$log2 = clone self::$log1;
        self::$log2->setMessage("test2");
        self::$log2->setContext(json_encode($context));

        parent::__construct($name, $data, $dataName);
    }

    /**
     * @dataProvider recordProvider
     *
     * @param array          $record
     * @param ApplicationLog $log
     *
     * @return void
     */
    public function testFormatRecordIntoElasticObject(array $record, ApplicationLog $log): void
    {
        $formatter = new ElasticObjectFormatter(new ApplicationLog());
        $result    = $formatter->format($record);

        $this->assertEquals($log, $result);
    }

    /**
     * @dataProvider recordsProvider
     *
     * @param array            $records
     * @param ApplicationLog[] $log
     *
     * @return void
     */
    public function testFormatRecordsIntoElasticObject(array $records, array $logs): void
    {
        $formatter = new ElasticObjectFormatter(new ApplicationLog());
        $result    = $formatter->formatBatch($records);

        $this->assertEquals($logs, $result);
    }

    public function recordProvider(): array
    {
        return [
            "basic"        => [
                self::$record1,
                self::$log1,
            ],
            "with context" => [
                self::$record2,
                self::$log2,
            ],
        ];
    }

    public function recordsProvider(): array
    {
        return [
            "similar records"   => [
                [
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                ],
                [
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                ],
            ],
            "different records" => [
                [
                    self::$record2,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record2,
                    self::$record2,
                    self::$record2,
                    self::$record1,
                    self::$record1,
                    self::$record1,
                    self::$record2,
                    self::$record2,
                ],
                [
                    self::$log2,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log2,
                    self::$log2,
                    self::$log2,
                    self::$log1,
                    self::$log1,
                    self::$log1,
                    self::$log2,
                    self::$log2,
                ],
            ],
        ];
    }
}
