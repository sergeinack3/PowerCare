<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Astreintes\Tests\Unit;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Ox\Core\CMbDT;
use Ox\Mediboard\Astreintes\AstreinteCalendarBuilder;
use Ox\Mediboard\Astreintes\AstreinteCalendarFactory;
use Ox\Mediboard\Astreintes\CCategorieAstreinte;
use Ox\Mediboard\Astreintes\Tests\Fixtures\AstreintesFixtures;
use Ox\Mediboard\System\CPlanningDay;
use Ox\Mediboard\System\CPlanningMonth;
use Ox\Mediboard\System\CPlanningWeekNew;
use Ox\Tests\OxUnitTestCase;

class AstreinteCalendarBuilderTest extends OxUnitTestCase
{
    /**
     * @dataProvider buildCalendarProvider
     * @throws Exception
     */
    public function testBuildCalendarReturnCPlanning(
        DateTimeInterface $date,
        string $mode,
        ?int $category_id,
        string $expected_class
    ): void {
        $calendar_builder = new AstreinteCalendarBuilder($date, $mode, $category_id);
        $actual           = $calendar_builder->buildAstreinteCalendar(new AstreinteCalendarFactory(), false);
        $this->assertInstanceOf($expected_class, $actual);
    }

    /**
     * @throws Exception
     * @dataProvider constructComputeIntervalProvider
     */
    public function testConstructComputeInterval(
        DateTimeInterface $date,
        string $mode,
        ?int $category_id,
        array $expected
    ): void {
        $actual = new AstreinteCalendarBuilder($date, $mode, $category_id);
        $this->assertEquals($expected[0], $actual->getIntervalStart());
        $this->assertEquals($expected[1], $actual->getIntervalEnd());
    }

    /**
     * @throws Exception
     */
    public function buildCalendarProvider(): array
    {
        $date      = new DateTimeImmutable(CMbDT::date("first day of january"));
        $date2     = new DateTimeImmutable(CMbDT::date("second day of january"));
        $cat_lorem = $this->getObjectFromFixturesReference(
            CCategorieAstreinte::class,
            AstreintesFixtures::TAG_CAT_LOREM
        );
        $cat_ipsum = $this->getObjectFromFixturesReference(
            CCategorieAstreinte::class,
            AstreintesFixtures::TAG_CAT_IPSUM
        );

        return [
            "day"   => [
                $date2,
                AstreinteCalendarBuilder::MODE_DAY,
                (int) $cat_lorem->_id,
                CPlanningDay::class,
            ],
            "week"  => [
                $date,
                AstreinteCalendarBuilder::MODE_WEEK,
                (int) $cat_ipsum->_id,
                CPlanningWeekNew::class,
            ],
            "month" => [
                $date,
                AstreinteCalendarBuilder::MODE_MONTH,
                null,
                CPlanningMonth::class,
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public function constructComputeIntervalProvider(): array
    {
        $date = new DateTimeImmutable(CMbDT::date("first day of january"));

        $midnight = $date->setTime(00, 00, 00);
        $late     = $date->setTime(23, 59, 59);

        $month_first = new DateTimeImmutable(CMbDT::date("first day of january"));
        $month_last  = new DateTimeImmutable(CMbDT::date("last day of january"));

        $week_monday = new DateTimeImmutable(CMbDT::date("this week", $date->format("Y-m-d H:i:s")));
        $week_sunday = (new DateTimeImmutable(
            CMbDT::date(
                "Next Sunday",
                $week_monday->format(AstreinteCalendarBuilder::DATE_FORMAT_YMD)
            )
        ))->add(new DateInterval("PT23H59M59S"));

        $cat_lorem = $this->getObjectFromFixturesReference(
            CCategorieAstreinte::class,
            AstreintesFixtures::TAG_CAT_LOREM
        );
        $cat_ipsum = $this->getObjectFromFixturesReference(
            CCategorieAstreinte::class,
            AstreintesFixtures::TAG_CAT_IPSUM
        );

        return [
            "day"   => [
                $date,
                AstreinteCalendarBuilder::MODE_DAY,
                (int) $cat_lorem->_id,
                [$midnight, $late],
            ],
            "week"  => [
                $date,
                AstreinteCalendarBuilder::MODE_WEEK,
                (int) $cat_ipsum->_id,
                [$week_monday, $week_sunday],
            ],
            "month" => [
                $date,
                AstreinteCalendarBuilder::MODE_MONTH,
                0,
                [$month_first, $month_last],
            ],
        ];
    }
}
