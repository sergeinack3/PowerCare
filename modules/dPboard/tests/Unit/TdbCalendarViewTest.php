<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Board\Tests\Unit;

use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Mediboard\Board\TdbCalendarView;
use Ox\Mediboard\Board\Tests\Fixtures\TdbFixtures;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class TdbCalendarViewTest extends OxUnitTestCase
{
    /**
     * @dataProvider getListPraticiensProvider
     */
    public function testGetListPraticiensContainsOrNotUserId(
        CMediusers $praticien,
        CMediusers $user,
        string $perm,
        bool $haskey = false
    ): void {
        $calendarview = new TdbCalendarView();

        $calendarview->prepareMonthView($praticien, $user, $perm, false);

        $actual = CMbArray::flip(CMbArray::pluck($calendarview->getListPraticiens(), "user_id"));

        $this->assertArrayHasKey($user->_id, $actual);

        if ($haskey) {
            $this->assertArrayHasKey($praticien->_id, $actual);
        } else {
            $this->assertArrayNotHasKey($praticien->_id, $actual);
        }
    }

    /**
     * @dataProvider getListFunctionsProvider
     */
    public function testGetListFunctionsContainsOrNotFunctionId(
        CMediusers $praticien,
        CMediusers $user,
        string $perm,
        bool $haskey = false
    ): void {
        $calendarview = new TdbCalendarView();

        $calendarview->prepareMonthView($praticien, $user, $perm, false);

        $actual = CMbArray::flip(CMbArray::pluck($calendarview->getListFunctions(), "function_id"));

        if ($haskey) {
            $this->assertArrayHasKey($user->function_id, $actual);
        } else {
            $this->assertArrayNotHasKey($user->function_id, $actual);
        }
    }

    /**
     * @throws TestsException
     * @throws Exception
     */
    public function testCreatePlanningWeek(): void
    {
        /** @var CMediusers $praticien */
        $praticien = $this->getObjectFromFixturesReference(CMediusers::class, TdbFixtures::REF_TDB_CHIR);

        /** @var CMediusers $user */
        $user = $this->getObjectFromFixturesReference(CMediusers::class, TdbFixtures::REF_TDB_USER);

        $calendarview = new TdbCalendarView();

        $date = CMbDT::date();
        $fin  = CMbDT::date("+7 days");

        $planning = $calendarview->createPlanningWeek($date, $date, $fin, $praticien, $user);

        $this->assertTrue($planning->isDayActive($date));
    }

    /**
     * @throws TestsException
     */
    public function getListPraticiensProvider(): array
    {
        /** @var CMediusers $praticien */
        $praticien = $this->getObjectFromFixturesReference(CMediusers::class, TdbFixtures::REF_TDB_CHIR);

        /** @var CMediusers $user */
        $user = $this->getObjectFromFixturesReference(CMediusers::class, TdbFixtures::REF_TDB_USER);

        return [
            "same_function" => [$praticien, $user, "same_function", false],
            "write_right"   => [$praticien, $user, "write_right", true],
            "read_right"    => [$praticien, $user, "read_right", true],
            "only_me"       => [$praticien, $user, "only_me", false],
        ];
    }

    /**
     * @throws TestsException
     */
    public function getListFunctionsProvider(): array
    {
        /** @var CMediusers $praticien */
        $praticien = $this->getObjectFromFixturesReference(CMediusers::class, TdbFixtures::REF_TDB_CHIR);

        /** @var CMediusers $user */
        $user = $this->getObjectFromFixturesReference(CMediusers::class, TdbFixtures::REF_TDB_USER);

        return [
            "same_function" => [$praticien, $user, "same_function", true],
            "write_right"   => [$praticien, $user, "write_right", false],
            "read_right"    => [$praticien, $user, "read_right", false],
            "only_me"       => [$praticien, $user, "only_me", false],
        ];
    }
}
