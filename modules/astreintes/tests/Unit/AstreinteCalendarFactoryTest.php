<?php

/**
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Astreintes\Tests\Unit;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Ox\Core\CMbDT;
use Ox\Mediboard\Astreintes\AstreinteCalendarFactory;
use Ox\Mediboard\System\CPlanningDay;
use Ox\Mediboard\System\CPlanningMonth;
use Ox\Mediboard\System\CPlanningWeekNew;
use Ox\Tests\OxUnitTestCase;

class AstreinteCalendarFactoryTest extends OxUnitTestCase
{
    /**
     * @config       ref_pays 1
     *
     * @dataProvider createCalendarProvider
     */
    public function testCreateCalendarObjectReturnPlanningType(
        string $mode,
        DateTimeInterface $date,
        ?string $date_min,
        ?string $date_max
    ): void {
        $date_format = $date->format("Y-m-d");
        switch ($mode) {
            case AstreinteCalendarFactory::PLANNING_MODES[0]:
                $expected        = new CPlanningDay($date->format("Y-m-d"));
                $expected->guid  = "CPlanning-day-$date_format";
                $expected->title = "Astreintes-day-$date_format";
                break;
            case AstreinteCalendarFactory::PLANNING_MODES[1]:
                $expected        = new CPlanningMonth($date->format("Y-m-d"), $date_min, $date_max);
                $expected->guid  = "CPlanning-month-$date_format";
                $expected->title = "Astreintes-month-$date_format";
                break;
            case AstreinteCalendarFactory::PLANNING_MODES[2]:
            default:
                $expected        = new CPlanningWeekNew($date->format("Y-m-d"), $date_min, $date_max);
                $expected->guid  = "CPlanning-week-$date_format";
                $expected->title = "Astreintes-week-$date_format";
        }
        $expected->hour_min = "00";

        $actual = (new AstreinteCalendarFactory())->createCalendarObject($mode, $date, $date_min, $date_max);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @throws Exception
     * @config       ref_pays 1
     */
    public function createCalendarProvider(): array
    {
        $date     = CMbDT::date();
        $datetime = new DateTimeImmutable($date);

        return [
            "CPlanningDay"     => [
                AstreinteCalendarFactory::PLANNING_MODES[0],
                $datetime,
                null,
                null,
            ],
            "CPlanningMonth"   => [
                AstreinteCalendarFactory::PLANNING_MODES[2],
                $datetime,
                $date,
                $date,
            ],
            "CPlanningWeekNew" => [
                AstreinteCalendarFactory::PLANNING_MODES[1],
                $datetime,
                $date,
                $date,
            ],
        ];
    }
}
