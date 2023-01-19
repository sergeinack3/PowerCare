<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Astreintes;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\CPlanning;
use Ox\Mediboard\System\CPlanningEvent;

/**
 * Build Astreintes Calendar with data from user
 */
class AstreinteCalendarBuilder
{
    public const MODE_DAY   = "day";
    public const MODE_WEEK  = "week";
    public const MODE_MONTH = "month";

    private const DATE_FORMAT_YMD_HIS  = "Y-m-d H:i:s";
    public const  DATE_FORMAT_YMD      = "Y-m-d";
    private const DATE_FORMAT_YMD_2359 = "Y-m-d 23:59:59";
    private const DATE_FORMAT_YMD_0000 = "Y-m-d 00:00:00";

    private const DATE_FORMATS = [
        self::MODE_DAY   => [
            self::DATE_FORMAT_YMD_HIS,
            self::DATE_FORMAT_YMD_HIS,
        ],
        self::MODE_WEEK  => [
            self::DATE_FORMAT_YMD_0000,
            self::DATE_FORMAT_YMD_2359,
        ],
        self::MODE_MONTH => [
            self::DATE_FORMAT_YMD_0000,
            self::DATE_FORMAT_YMD_2359,
        ],
    ];

    private const ORDER = "type ASC, categorie ASC";

    /** @var CSQLDataSource */
    private $ds;
    /** @var DatetimeInterface */
    private $date;
    /** @var string */
    private $mode;
    /** @var string */
    private $category_id;
    /** @var DateTimeImmutable */
    private $today_midnight;
    /** @var DateTimeImmutable */
    private $today_late;
    /** @var DateTimeImmutable */
    private $week_monday;
    /** @var DateTimeImmutable */
    private $week_sunday;
    /** @var DateTimeImmutable */
    private $month_first;
    /** @var DateTimeImmutable */
    private $month_last;
    /** @var int */
    private $max_height_event = 0;
    /** @var array */
    private $days = [];
    /** @var array */
    private $events = [];

    /**
     * @throws Exception
     */
    public function __construct(DatetimeInterface $date, string $mode = self::MODE_WEEK, ?string $category_id = null)
    {
        // In the calendar view, the year cannot be selected though in the on call list page you can.
        // If the "year" value is kept in session, a warning pops up saying bad CView value.
        $mode = ($mode === "year") ? self::MODE_WEEK : $mode;

        $this->ds          = CSQLDataSource::get("std");
        $this->date        = $date;
        $this->mode        = $mode;
        $this->category_id = $category_id;
        $this->computeDateInterval();
    }

    /**
     * @throws CMbException
     * @throws Exception
     */
    private function computeDateInterval(): void
    {
        switch ($this->mode) {
            case self::MODE_DAY:
                $this->today_midnight = $this->date->setTime(00, 00, 00);
                $this->today_late     = $this->date->setTime(23, 59, 59);
                break;
            case self::MODE_MONTH:
                $this->month_first = new DateTimeImmutable(
                    CMbDT::date("first day of this month", $this->date->format(self::DATE_FORMAT_YMD_HIS))
                );
                $this->month_last  = new DateTimeImmutable(
                    CMbDT::date(
                        "last day of this month",
                        $this->month_first->format(self::DATE_FORMAT_YMD)
                    )
                );
                break;
            case self::MODE_WEEK:
                $this->week_monday = new DateTimeImmutable(
                    CMbDT::date("this week", $this->date->format(self::DATE_FORMAT_YMD))
                );
                $this->week_sunday = (new DateTimeImmutable(
                    CMbDT::date("Next Sunday", $this->week_monday->format(self::DATE_FORMAT_YMD))
                ))
                    ->add(new DateInterval("PT23H59M59S"));
                break;
            default:
                throw new CMbException("Incorrect mode");
        }
    }

    /**
     * @throws CMbException
     */
    public function getIntervalStart(): DateTimeInterface
    {
        switch ($this->mode) {
            case self::MODE_DAY:
                return $this->today_midnight;
            case self::MODE_MONTH:
                return $this->month_first;
            case self::MODE_WEEK:
                return $this->week_monday;
            default:
                throw new CMbException("Incorrect mode");
        }
    }

    /**
     * @throws CMbException
     */
    public function getIntervalEnd(): DateTimeInterface
    {
        switch ($this->mode) {
            case self::MODE_DAY:
                return $this->today_late;
            case self::MODE_MONTH:
                return $this->month_last;
            case self::MODE_WEEK:
                return $this->week_sunday;
            default:
                throw new CMbException("Incorrect mode");
        }
    }

    /**
     * @throws Exception
     */
    public function buildAstreinteCalendar(
        AstreinteCalendarFactory $calendar_factory,
        bool $build_data = true
    ): CPlanning {
        switch ($this->mode) {
            case self::MODE_MONTH:
                $date_min = $this->month_first->format(self::DATE_FORMAT_YMD);
                $date_max = $this->month_last->format(self::DATE_FORMAT_YMD);
                break;
            case self::MODE_WEEK:
                $date_min = $this->week_monday->format(self::DATE_FORMAT_YMD_0000);
                $date_max = $this->week_sunday->format(self::DATE_FORMAT_YMD_2359);
                break;
            default:
                $date_min = $date_max = null;
                break;
        }

        $calendar = $calendar_factory->createCalendarObject(
            $this->mode,
            $this->date,
            $date_min,
            $date_max
        );

        $this->buildCalendarData($calendar, $build_data);

        return $calendar;
    }

    /**
     * @throws Exception
     */
    protected function buildCalendarData(CPlanning $calendar, bool $build_data = true): void
    {
        $astreintes_by_type = $this->loadAstreintesByType(CGroups::loadCurrent());

        $events_sorted = $this->buildEventCalendar($calendar, $astreintes_by_type, $build_data);

        $events_merged = $this->mergeEventsCalendar($events_sorted);

        $calendar->events_sorted    = $events_merged;
        $calendar->days             = $this->days ?: $calendar->days;
        $calendar->events           = $this->events;
        $calendar->max_height_event = $this->max_height_event;
    }

    /**
     * @throws Exception
     */
    public function loadAstreintesByType(CGroups $group): array
    {
        $astreintes_by_type = [];
        $ds                 = $this->ds;
        $where              = [
            "group_id" => $ds->prepare("= ?", $group->_id),
        ];

        if ($this->category_id) {
            $where["categorie"] = $ds->prepare("= ?", $this->category_id);
        }

        switch ($this->mode) {
            case self::MODE_DAY:
                $where[] = $ds->prepare(
                    "(?1 BETWEEN start AND end) OR (start >= ?1 AND end <= ?2) OR (?2 BETWEEN start AND end)",
                    $this->today_midnight->format(self::DATE_FORMAT_YMD_HIS),
                    $this->today_late->format(self::DATE_FORMAT_YMD_HIS)
                );
                break;

            case self::MODE_MONTH:
                $where[] = $ds->prepare(
                    "(?1 BETWEEN start AND end) OR (start >= ?2 AND end <= ?3) OR (start <= ?3 AND end >= ?2)",
                    $this->date->format(self::DATE_FORMAT_YMD_HIS),
                    $this->month_first->format(self::DATE_FORMAT_YMD_0000),
                    $this->month_last->format(self::DATE_FORMAT_YMD_2359)
                );
                break;
            case self::MODE_WEEK:
            default:  // week
                $where[] = $ds->prepare(
                    "(?1 BETWEEN start AND end) OR (start <= ?2 AND end >= ?3)",
                    $this->date->format(self::DATE_FORMAT_YMD_HIS),
                    $this->week_sunday->format(self::DATE_FORMAT_YMD_2359),
                    $this->week_monday->format(self::DATE_FORMAT_YMD_0000)
                );
                break;
        }
        $astreintes = (new CPlageAstreinte())->loadList($where, self::ORDER);

        CStoredObject::massLoadFwdRef($astreintes, "categorie");
        foreach ($astreintes as $_astreinte) {
            $_astreinte->loadRefCategory();
            $astreintes_by_type[$_astreinte->type][$_astreinte->_ref_category->name][] = $_astreinte;
        }

        return $astreintes_by_type;
    }

    /**
     * @throws Exception
     */
    protected function buildEventCalendar(
        CPlanning $calendar,
        array $astreintes_by_type,
        bool $build_data = true
    ): array {
        $events_sorted = [];
        foreach ($astreintes_by_type as $type => $_astreintes_categories) {
            foreach ($_astreintes_categories as $_category => $_astreintes) {
                foreach ($_astreintes as $_astreinte) {
                    [$start, $end] = $this->computeStartEnd($_astreinte);

                    $length = CMbDT::minutesRelative($start, $end);

                    //not in the current group
                    $_astreinte->loadRefUser();
                    $_astreinte->loadRefColor();
                    $_astreinte->loadRefCategory();

                    $libelle = $build_data ? $this->getLibellePlageAstreinte($_astreinte) : $_astreinte->libelle;

                    $plage = new CPlanningEvent(
                        $_astreinte->_guid,
                        $start,
                        $length,
                        $libelle,
                        "#" . $_astreinte->_color,
                        true,
                        'astreinte',
                        false,
                        false
                    );
                    $plage->setObject($_astreinte);
                    $plage->plage["id"]   = $_astreinte->_id;
                    $plage->type          = $_astreinte->type;
                    $plage->end           = $end;
                    $plage->display_hours = true;

                    if (
                        $build_data &&
                        ($_astreinte->locked == 0 && CCanDo::edit()) ||
                        ($_astreinte->locked == 1 && CCanDo::admin())
                    ) {
                        $plage->addMenuItem("edit", CAppUI::tr("CPlageAstreinte-title-modify"));
                    }

                    //add the event to the planning
                    $calendar->addEvent($plage);
                }
                $calendar->rearrange(true);

                $events_sorted[$type][$_category] = $calendar->events_sorted;
                $this->events                     = array_merge($this->events, $calendar->events);
                $this->days                       = array_merge_recursive($this->days, $calendar->days);

                $this->max_height_event += count($_astreintes);

                $calendar->events_sorted = [];
                $calendar->events        = [];
                if ($this->days) {
                    $calendar->days = [];
                }
            }
        }

        return $events_sorted;
    }

    /**
     * @throws Exception
     */
    protected function computeStartEnd(CPlageAstreinte $astreinte): array
    {
        $astreinte_start = new DateTimeImmutable($astreinte->start);
        $start_format    = $astreinte_start->format(self::DATE_FORMAT_YMD_HIS);

        $astreinte_end = new DateTimeImmutable($astreinte->end);
        $end_format    = $astreinte_end->format(self::DATE_FORMAT_YMD_HIS);

        return $this->getFormattedInterval($astreinte_start, $start_format, $astreinte_end, $end_format);
    }

    /**
     * @throws CMbException
     */
    private function getFormattedInterval(
        DateTimeInterface $astreinte_start,
        string $start_format,
        DateTimeInterface $astreinte_end,
        string $end_format
    ): array {
        $start           = $start_format;
        $end             = $end_format;
        $to_format_start = false;
        $to_format_end   = false;

        if ($astreinte_start < $this->getIntervalStart()) {
            $to_format_start = true;
            $start           = $this->getIntervalStart();
        }

        if ($astreinte_end > $this->getIntervalEnd()) {
            $to_format_end = true;
            $end           = $this->getIntervalEnd();
        }

        if ($to_format_start) {
            $start = $start->format(self::DATE_FORMATS[$this->mode][0]);
        }
        if ($to_format_end) {
            $end = $end->format(self::DATE_FORMATS[$this->mode][1]);
        }

        return [$start, $end];
    }

    protected function mergeEventsCalendar(array $events_sorted): array
    {
        $astreintes_by_step = [];
        foreach ($events_sorted as $type => $_events_by_type) {
            foreach ($_events_by_type as $_category => $_events_by_category) {
                foreach ($_events_by_category as $_events_by_day) {
                    foreach ($_events_by_day as $_events_by_hour) {
                        $heights = CMbArray::pluck($_events_by_hour, "height");
                        if (isset($astreintes_by_step[$type][$_category])) {
                            $heights = array_merge($heights, [$astreintes_by_step[$type][$_category]]);
                        }
                        $astreintes_by_step[$type][$_category] = max($heights);
                    }
                }
            }
        }

        $i   = 0;
        $num = 0;

        foreach ($events_sorted as $type => $_events_by_type) {
            foreach ($_events_by_type as $_category => $_events_by_category) {
                if (!isset($astreintes_by_step[$type][$_category])) {
                    $astreintes_by_step[$type] = 0;
                }

                if ($i == 0) {
                    $i++;
                    $num = $astreintes_by_step[$type][$_category] + 1;
                    continue;
                }

                foreach ($_events_by_category as $_events_by_day) {
                    foreach ($_events_by_day as $_events_by_hour) {
                        foreach ($_events_by_hour as $_event) {
                            $_event->height += $num;
                        }
                    }
                }

                $num += $astreintes_by_step[$type][$_category] + 1;
            }
        }

        $events_merged = [];
        foreach ($events_sorted as $_events) {
            $events_merged = array_merge_recursive($events_merged, $_events);
        }

        return $events_merged;
    }

    /**
     * Because of generic system/calendar/inc_events_planning/event->title
     * TODO Refactor
     */
    private function getLibellePlageAstreinte(CPlageAstreinte $astreinte): string
    {
        $smarty = new CSmartyDP();

        $smarty->assign("astreinte", $astreinte);

        return $smarty->fetch("inc_libelle_plage");
    }
}
