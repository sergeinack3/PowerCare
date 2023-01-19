<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Board\Tests\Unit;

use Exception;
use Ox\Core\CMbDT;
use Ox\Mediboard\Board\Exception\TdbStatsException;
use Ox\Mediboard\Board\TdbStats;
use Ox\Mediboard\Board\Tests\Fixtures\TdbFixtures;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class TdbStatsTest extends OxUnitTestCase
{
    private TdbStats $service;

    private CMediusers $user;

    private COperation $operation;

    /**
     * @throws TestsException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TdbStats();
        /** @var CMediusers $user */
        $user = $this->getObjectFromFixturesReference(CMediusers::class, TdbFixtures::REF_TDB_CHIR);

        $this->user = $user;
        /** @var COperation $operation */
        $operation       = $this->getObjectFromFixturesReference(COperation::class, TdbFixtures::REF_TDB_OP);
        $this->operation = $operation;
    }

    /**
     * @return void
     * @throws TdbStatsException
     * @config dPplanningOp COperation verif_cote 1
     */
    public function testGetAllStatsViews(): void
    {
        $tdb = new TdbStats();

        $actual = $tdb->getAllStatsViews("viewTraceCotes");

        $this->assertContains("viewTraceCotes", $actual);
    }

    /**
     * @throws TdbStatsException
     */
    public function testGetAllStatsViewsThrowsException(): void
    {
        $this->expectExceptionObject(TdbStatsException::viewNotFound("lorem"));

        $this->service->getAllStatsViews("lorem");
    }

    /**
     * @throws Exception
     */
    public function testGetStatsPrescripteurs(): void
    {
        $tdb = new TdbStats();

        /** @var CMediusers $user */
        $user = $this->getObjectFromFixturesReference(CMediusers::class, TdbFixtures::REF_TDB_CHIR);

        $actual = $tdb->getStatsPrescripteurs($user);

        $expected = [[], "0", []];

        $this->assertArrayContentsEquals($expected, $actual);
    }

    /**
     * @throws Exception
     */
    public function testGetStatsVerifsCote(): void
    {
        $actual = $this->service->getVerificationCotesStats($this->user, CMbDT::date("+1 DAY"));

        $this->assertArrayHasKey($this->operation->_id, $actual);
    }

    public function testGetGraphsConsultationsReturnGraph(): void
    {
        $actual = $this->service->getGraphsConsultations(CMbDT::date("-1 day"), CMbDT::date("+1 day"), $this->user);
        $this->assertNotEmpty($actual[0]["series"][0]);
    }
}
