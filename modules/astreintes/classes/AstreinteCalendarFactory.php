<?php

/**
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Astreintes;

use DateTimeInterface;
use Ox\Mediboard\System\CPlanning;
use Ox\Mediboard\System\CPlanningDay;
use Ox\Mediboard\System\CPlanningMonth;
use Ox\Mediboard\System\CPlanningWeekNew;

/**
 * Create CPlanning inherited object
 */
class AstreinteCalendarFactory
{
    private const MODE_DAY   = "day";
    private const MODE_WEEK  = "week";
    private const MODE_MONTH = "month";

    public const PLANNING_MODES = [
        self::MODE_DAY,
        self::MODE_MONTH,
        self::MODE_WEEK,
    ];

    public function createCalendarObject(
        string $mode,
        DateTimeInterface $date,
        ?string $date_min,
        ?string $date_max
    ): CPlanning {
        $date_format = $date->format("Y-m-d");
        switch ($mode) {
            case self::MODE_DAY:
                $calendar = new CPlanningDay($date_format);
                break;
            case self::MODE_MONTH:
                $calendar = new CPlanningMonth($date_format, $date_min, $date_max);
                break;
            case self::MODE_WEEK:
            default:
                $calendar = new CPlanningWeekNew($date_format, $date_min, $date_max);
                break;
        }
        $calendar->guid     = "CPlanning-$mode-$date_format";
        $calendar->title    = "Astreintes-$mode-$date_format";
        $calendar->hour_min = "00";

        return $calendar;
    }
}
