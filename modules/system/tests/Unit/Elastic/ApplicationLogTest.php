<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Unit\Elastic;

use Ox\Core\Logger\LoggerLevels;
use Ox\Mediboard\System\Elastic\ApplicationLog;
use Ox\Tests\OxUnitTestCase;

class ApplicationLogTest extends OxUnitTestCase
{

    public function testPrepareToRender(): void
    {
        $level   = LoggerLevels::LEVEL_DEBUG;
        $message = "TEST EXECUTION - Test1";
        $log     = new ApplicationLog(
            $message,
            ["context" => "test"],
            $level
        );

        $data = $log->prepareToRender();

        self::assertIsArray($data);
        self::assertStringMatchesFormat("[%d-%d-%d %d:%d:%d.%d]", $data["date"]);
        $level_name = LoggerLevels::getLevelName($level);
        self::assertEquals("[" . $level_name . "]", $data["level"]);
        self::assertEquals(LoggerLevels::getLevelColor($log->getLogLevel()), $data["color"]);
        self::assertEquals($message, $data["message"]);
    }

    public function testToLogFile(): void
    {
        $level   = LoggerLevels::LEVEL_DEBUG;
        $message = "TEST EXECUTION - Test1";
        $log     = new ApplicationLog(
            $message,
            ["context" => "test"],
            $level
        );

        $data = $log->toLogFile();

        self::assertIsString($data);
        self::assertMatchesRegularExpression(
            "/\[(.*?)] \[(\w+)] (.*?) \[(.*?)] \[(.*?)]/",
            $data
        );
    }
}
