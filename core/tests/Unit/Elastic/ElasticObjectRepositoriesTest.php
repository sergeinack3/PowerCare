<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Elastic;

use DateTime;
use Ox\Core\Elastic\ElasticObjectManager;
use Ox\Core\Elastic\ElasticObjectRepositories;
use Ox\Core\Elastic\Exceptions\ElasticObjectMissingException;
use Ox\Core\Elastic\QueryBuilder\ElasticQueryBuilder;
use Ox\Core\Elastic\QueryBuilder\Filters\AbstractElasticQueryFilter;
use Ox\Core\Logger\LoggerLevels;
use Ox\Mediboard\System\Elastic\ApplicationLog;
use Ox\Mediboard\System\Elastic\ApplicationLogRepository;
use Ox\Tests\OxUnitTestCase;

class ElasticObjectRepositoriesTest extends OxUnitTestCase
{
    private ElasticObjectRepositories $repository;

    public static function setUpBeforeClass(): void
    {
        // TODO: mock an object to avoid the usage of a specific class
        $obj = new ApplicationLog();
        ElasticObjectManager::createTemplate($obj);
        ElasticObjectManager::createFirstIndex($obj);
        $objs    = [];
        $objs[]  = new ApplicationLog(
            "TEST EXECUTION - Test1",
            ["context" => "test"],
            LoggerLevels::LEVEL_DEBUG
        );
        $objs[]  = new ApplicationLog(
            "TEST EXECUTION - Test2",
            ["test" => "--ada--"],
            LoggerLevels::LEVEL_CRITICAL
        );
        $objs[]  = new ApplicationLog(
            "TEST EXECUTION - Test3",
            ["data" => "test"],
            LoggerLevels::LEVEL_ALERT
        );
        $objs[]  = new ApplicationLog(
            "TEST EXECUTION - Test4",
            ["var" => "test", "multiple" => "var"],
            LoggerLevels::LEVEL_ERROR
        );
        ElasticObjectManager::getInstance()->storeAndWait($objs);
    }

    public function setUp(): void
    {
        $this->repository = new ApplicationLogRepository();
    }

    public static function tearDownAfterClass(): void
    {
        ElasticObjectManager::getInstance()->clear(new ApplicationLog());
    }


    public function testGettingLastObjects(): void
    {
        $count = 5;
        $objs  = $this->repository->last($count);
        self::assertIsArray($objs);
        self::assertLessThanOrEqual($count, count($objs));
        foreach ($objs as $obj) {
            self::assertInstanceOf(ApplicationLog::class, $obj);
        }
        $count = 1;
        $objs  = $this->repository->last($count);
        self::assertIsArray($objs);
        self::assertLessThanOrEqual($count, count($objs));
        self::assertInstanceOf(ApplicationLog::class, $objs[0]);
    }

    public function testGettingFirstObjects(): void
    {
        $count = 5;
        $objs  = $this->repository->first($count);
        self::assertIsArray($objs);
        self::assertLessThanOrEqual($count, count($objs));
        foreach ($objs as $obj) {
            self::assertInstanceOf(ApplicationLog::class, $obj);
        }
        $count = 1;
        $objs  = $this->repository->first($count);
        self::assertIsArray($objs);
        self::assertLessThanOrEqual($count, count($objs));
        self::assertInstanceOf(ApplicationLog::class, $objs[0]);
    }

    public function testCountingIndexDocuments(): void
    {
        $count = $this->repository->count();
        self::assertIsInt($count);
    }

    public function testGettingBetweenObjects(): void
    {
        $count = 5;
        $objs  = $this->repository->list(10, $count);
        self::assertIsArray($objs);
        self::assertLessThanOrEqual($count, count($objs));
    }

    public function testGettingDocumentByIdNotExisting(): void
    {
        self::expectException(ElasticObjectMissingException::class);
        $index = $this->repository->getElasticObject()->getSettings()->getFirstIndexName();
        self::expectExceptionMessage('{"_index":"' . $index . '","_type":"_doc","_id":"a","found":false}');
        $this->repository->findById("a");
    }

    public function testGettingDocumentByIdExisting(): void
    {
        $manager = ElasticObjectManager::getInstance();
        $log     = new ApplicationLog(
            "TEST EXECUTION - Test1",
            ["context" => "test"],
            LoggerLevels::LEVEL_DEBUG
        );
        $log     = $manager->store($log);
        $result  = $this->repository->findById($log->getId());
        self::assertInstanceOf(ApplicationLog::class, $result);
    }

    public function testSearchingObjectsWithDateSorting(): void
    {
        $count = 5;
        $objs  = $this->repository->search($count, "TEST", ["message^2", "context"]);
        self::assertIsArray($objs);
        self::assertLessThanOrEqual($count, count($objs));
        foreach ($objs as $obj) {
            self::assertInstanceOf(ApplicationLog::class, $obj);
        }

        $count = 2;
        $objs  = $this->repository->search(
            $count,
            "TEST",
            ["message^2", "context"],
            0,
            ElasticObjectRepositories::SORTING_DATE_ASC
        );
        self::assertIsArray($objs);
        self::assertLessThanOrEqual($count, count($objs));
        foreach ($objs as $obj) {
            self::assertInstanceOf(ApplicationLog::class, $obj);
        }
    }

    public function testSearchingObjectsWithoutSorting(): void
    {
        $count = 5;
        $objs  = $this->repository->search(
            $count,
            "TEST",
            ["message^2", "context"],
            0,
            ElasticObjectRepositories::SORTING_NO_SORTING
        );
        self::assertIsArray($objs);
        self::assertLessThanOrEqual($count, count($objs));
        foreach ($objs as $obj) {
            self::assertInstanceOf(ApplicationLog::class, $obj);
        }
    }

    public function testCountFromQuery(): void
    {
        $query = new ElasticQueryBuilder((new ApplicationLog())->getSettings()->getAliasName());
        $query->addFilter(AbstractElasticQueryFilter::match("message", "TEST"));

        $count = $this->repository->countFromQuery($query);
        $resp  = $this->repository->execQueryToResult($query);

        self::assertGreaterThanOrEqual($resp["hits"]["total"]["value"], $count);
    }

    public function testExecQuery(): void
    {
        $query = new ElasticQueryBuilder((new ApplicationLog())->getSettings()->getAliasName());
        $query->addFilter(AbstractElasticQueryFilter::match("message", "TEST"));

        $objs = $this->repository->execQuery($query);

        self::assertIsArray($objs);
        foreach ($objs as $_obj) {
            self::assertInstanceOf(ApplicationLog::class, $_obj);
        }
    }


    public function testListBetweenDate(): void
    {
        $number     = 10;
        $from       = 0;
        $date_start = (new DateTime("now-7d"))->modify("-14 days");
        $date_end   = new DateTime("now");

        /**
         * @var ApplicationLog[] $objs
         */
        $objs = $this->repository->listBetweenDate($number, $from, $date_start, $date_end);
        self::assertIsArray($objs);
        self::assertLessThanOrEqual($number, count($objs));
        foreach ($objs as $obj) {
            $date = $obj->getDate();
            self::assertGreaterThanOrEqual($date->getTimestamp(), $date_start->getTimestamp());
            self::assertLessThanOrEqual($date->getTimestamp(), $date_end->getTimestamp());
            self::assertInstanceOf(ApplicationLog::class, $obj);
        }
    }
}
