<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Unit\Elastic;

use Ox\Core\Elastic\ElasticObjectManager;
use Ox\Mediboard\System\Elastic\ApplicationLog;
use Ox\Mediboard\System\Elastic\ApplicationLogRepository;
use Ox\Tests\OxUnitTestCase;

class ApplicationLogRepositoryTest extends OxUnitTestCase
{
    private ApplicationLogRepository $application_log_repository;

    public static function setUpBeforeClass(): void
    {
        $object = new ApplicationLog();
        ElasticObjectManager::createTemplate($object);
        ElasticObjectManager::createFirstIndex($object);
    }

    protected function setUp(): void
    {
        $this->application_log_repository = new ApplicationLogRepository();
    }

    public static function tearDownAfterClass(): void
    {
        ElasticObjectManager::getInstance()->clear(new ApplicationLog());
    }

    public function testStartScroll(): void
    {
        $data = $this->application_log_repository->startScroll();

        self::assertIsArray($data);
        self::assertIsString($data["scroll_id"]);
        self::assertIsArray($data["logs"]);

        foreach ($data["logs"] as $obj) {
            self::assertInstanceOf(ApplicationLog::class, $obj);
        }
    }

    public function testContinueScroll(): void
    {
        $data = $this->application_log_repository->startScroll(30, 10);

        self::assertIsArray($data);
        self::assertIsString($data["scroll_id"]);
        self::assertIsArray($data["logs"]);

        foreach ($data["logs"] as $obj) {
            self::assertInstanceOf(ApplicationLog::class, $obj);
        }

        $data = $this->application_log_repository->continueScroll($data["scroll_id"]);

        self::assertIsArray($data);
        self::assertIsString($data["scroll_id"]);
        self::assertIsArray($data["logs"]);

        foreach ($data["logs"] as $obj) {
            self::assertInstanceOf(ApplicationLog::class, $obj);
        }
    }

    public function testSearchingWithRegex(): void
    {
        $number         = 10;
        $from           = 0;
        $search_data    = "TEST";
        $fields         = ["message", "context"];
        $case_sensitive = false;
        $objs           = $this->application_log_repository->searchWithRegexAndHighlighting(
            $number,
            $search_data,
            $fields,
            $from,
            $case_sensitive
        );

        self::assertIsArray($objs);
        self::assertLessThanOrEqual($number, count($objs));
        foreach ($objs as $obj) {
            self::assertInstanceOf(ApplicationLog::class, $obj);
        }
    }
}
