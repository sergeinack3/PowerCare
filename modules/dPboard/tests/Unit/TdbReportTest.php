<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Board\Tests\Unit;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Mediboard\Board\TdbReport;
use Ox\Mediboard\Board\Tests\Fixtures\TdbFixtures;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class TdbReportTest extends OxUnitTestCase
{
    private CMediusers $user;

    /**
     * @throws TestsException
     */
    protected function setUp(): void
    {
        parent::setUp();
        /** @var CMediusers $user */
        $user = $this->getObjectFromFixturesReference(CMediusers::class, TdbFixtures::REF_TDB_CHIR);

        $this->user = $user;
    }

    /**
     * @return void
     * @pref allow_other_users_board write_right
     * @throws Exception
     */
    public function testLoadPraticiensContainsSameRightUser(): void
    {
        $service = new TdbReport($this->user);
        $this->invokePrivateMethod($service, "loadPraticiens", false);

        $actual = $service->getPraticiens();
        array_map(function (CMediusers $praticien): void {
            $this->assertTrue($praticien->getPerm(PERM_EDIT));
        }, $actual);
    }

    /**
     * @return void
     * @pref allow_other_users_board only_me
     * @throws Exception
     */
    public function testLoadPraticiensOnlyUser(): void
    {
        $service  = new TdbReport($this->user);
        $actual   = $service->getPraticiens();
        $expected = [$this->user->_id => $this->user];
        $this->assertArrayContentsEquals($expected, $actual);
    }

    /**
     * @return void
     * @pref allow_other_users_board same_function
     * @throws Exception
     */
    public function testLoadPraticiensContainsSameFunction(): void
    {
        $service = new TdbReport($this->user);
        $this->invokePrivateMethod($service, "loadPraticiens", false);

        $actual = $service->getPraticiens();

        array_map(function (CMediusers $praticien): void {
            $function = $this->user->loadRefFunction();
            $this->assertTrue($praticien->loadRefFunction()->group_id === $function->group_id);
        }, $actual);
    }

    /**
     * @throws CMbException
     */
    public function testConstructThrowsException(): void
    {
        $this->expectExceptionMessage("User not found");

        new TdbReport(new CMediusers());
    }

    /**
     * @throws CMbException
     * @dataProvider getCodingReportExceptionProvider
     */
    public function testGetCodingReportThrowsException(string $date_min, string $date_max): void
    {
        $this->expectExceptionMessage("common-error-Invalid data");

        $service = new TdbReport($this->user);

        $service->getCodingReport($date_min, $date_max);
    }

    public function getCodingReportExceptionProvider(): array
    {
        return [
            "date_min empty"      => ["", CMbDT::date("+1 day")],
            "date_max empty"      => [CMbDT::date(), ""],
            "date_min > date_max" => [CMbDT::date("+1 day"), CMbDT::date()],
        ];
    }

    /**
     * @throws CMbException
     * @throws Exception
     */
    public function testGetTransmissionReportReturnsStaysWithTransmissionsOrObservations(): void
    {
        $service = new TdbReport($this->user);

        $service->getTransmissionReport($this->user);

        $actual = $service->getSejours();

        array_map(function (CSejour $sejour): void {
            $this->assertTrue(!empty($sejour->_ref_transmissions) || !empty($sejour->_ref_observations));
        }, $actual);
    }

    /**
     * @throws CMbException
     */
    public function testGetFilterDatesAreNotNull(): void
    {
        $service = new TdbReport($this->user);

        $service->getCodingReport(CMbDT::date(), CMbDT::date("+1 day"));

        $actual = $service->getFilter();

        $this->assertTrue($actual->_date_min && $actual->_date_max);
    }
}
