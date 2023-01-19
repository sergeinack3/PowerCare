<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
namespace Ox\Mediboard\System;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbDay;
use Ox\Core\CMbDT;
use Ox\Core\CMbRange;

/**
 * Class CPlanning
 */
class CPlanning implements IShortNameAutoloadable {
  public $guid;
  public $title;

  public $type;

  public $date;     //YYYY-MM-DD
  public $date_min; // Monday
  public $date_max; //sunday
  public $max_height_event = 0; //max event heights (use rearrange)

  //behaviour
  public $selectable  = null;           // for js displacement
  public $dragndrop = 0;                // allow drag & drop
  public $resizable = 0;                // resizable by drag & drop
  public $allow_superposition = false;  // allow superposition of Events
  public $adapt_range = null;           // adapt range
  public $explode_multidays = false;    //if a plage is composed by more than day, explode it in sub_days


  //events
  public $events = array();             // list of events
  public $events_sorted = array();      // events sorted
  public $ranges = array();             // list of ranges
  public $ranges_sorted = array();      // ranges sorted
  public $days = array();               // list of days
  public $nb_days;                      // number of days

  public $activities = array();       //start of the planning activity


  //min max
  public $date_min_active;
  public $date_max_active;
  public $_date_min_planning;
  public $_date_max_planning;
  public $weekend_days = array(6, 7);    //used to colorize the week end

  // Periods
  public $pauses = array("08", "12", "16");
  public $_ref_holidays = array();
  //hours
  public $hour_divider = 6;
  public $hours = array(
    "00", "01", "02", "03", "04", "05",
    "06", "07", "08", "09", "10", "11",
    "12", "13", "14", "15", "16", "17",
    "18", "19", "20", "21", "22", "23",
  );
  public $_hours;         //hours displayed
  public $hour_min = "09";
  public $hour_max = "16";

  public $year_day_list = array();

  public $height;
  public $large;

  public $maximum_load;
  public $has_load;
  public $has_range;
  public $show_half;

  public $no_dates;

  public $unavailabilities = array();
  public $day_labels = array();
  public $load_data = array();


  public $_ref_day;

  /**
   * constructor
   *
   * @param string $date current date in the planning
   */
  function __construct($date) {
    $this->date = $date;
    $this->_hours = $this->hours;

    //load nonworking days
    $this->loadHolidays();

    //the current day is loaded
    $this->_ref_day = new CMbDay($date);

    //list of days in the current year
    $year = CMbDT::transform("", $date, "%Y");
    $day = CMbDT::date("last Monday", "$year-01-01");
    while ($day != CMbDT::date("next Monday", "$year-12-31")) {
      $this->year_day_list[$day] = new CMbDay($day);
      $day = CMbDT::date("+1 DAY", $day);
    }

    $this->no_dates = 0;
  }

  /**
   * add an event to the present planning
   *
   * @param CPlanningEvent $event an event
   *
   * @return null
   */
  function addEvent(CPlanningEvent $event) {

    //@TODO: fix problem in other way if the date start != date end (chevauchement de plusieurs jours) (before every check)
    if (($this->explode_multidays) && (CMbDT::date($event->start) != CMbDT::date($event->end))) {
      $this->explodeEvent($event);
      return;
    }

    if ($event->day < $this->date_min || $event->day > $this->date_max) {
      return;
    }

    if ( $event->day < $this->date_min_active || $event->day > $this->date_max_active) {
      $event->disabled = true;
    }

    $this->events[] = $event;
    $this->days[$event->day][] = $event;
    $this->events_sorted[$event->day][$event->hour][] = $event;

    $colliding = array($event);
    /** @var $_event CPlanningEvent */
    foreach ($this->days[$event->day] as $_event) {
      if ($_event->collides($event)) {
        $colliding[] = $_event;
        if (count($this->events_sorted[$_event->day][$_event->hour])) {
          foreach ($this->events_sorted[$_event->day][$_event->hour] as $__event) {
            if ($__event === $_event || $__event === $event) {
              continue;
            }
            $min = min($event->start, $_event->start);
            $max = max($event->end  , $_event->end);

            if (($__event->start < $min && $__event->end <= $min) || ($__event->start >= $max && $__event->end > $max)) {
              continue;
            }

            $colliding[] = $__event;
          }
        }
      }
    }

    $event->offset = 0.0;
    $event->width = 1.0;

    $count = count($colliding);

    if ($count) {
      foreach ($colliding as $_key => $_event) {
        $_event->width = 1 / $count;
        $_event->offset = $_key * $_event->width;
      }
    }
  }

  /**
   * if an event is more than 1 day long.
   *
   * @param CPlanningEvent $event an event to divide
   *
   * @return null
   */
  function explodeEvent(CPlanningEvent $event) {
    $daysDifference = CMbDT::daysRelative($event->start, $event->end);
    $day = CMbDT::date($event->start);

    //create 2 sub-event
    $left_rightBorne = CMbDT::date($event->start)." 23:59:59";
    $event_left = new CPlanningEvent($event->guid, $event->start, CMbDT::minutesRelative($event->start, $left_rightBorne), $event->title, $event->color, $event->important, $event->css_class, null, false);
    $event_left->mb_object = $event->mb_object;
    $event_left->plage = $event->plage;
    $event_left->end = $left_rightBorne;
    $event_left->type = $event->type;
    $event_left->display_hours = $event->display_hours;
    $event_left->menu = $event->menu;

    self::addEvent($event_left);

    if ($daysDifference > 1) {
      for ($a = 1; $a<$daysDifference; $a++) {
        $day = CMbDT::date("+1 DAY"   , $day);
        $dayBetween = new CPlanningEvent($event->guid, $day." 00:00:00", 1440, $event->title, $event->color, $event->important, $event->css_class, null, false);
        $dayBetween->end = $day." 23:59:59";
        $dayBetween->mb_object = $event->mb_object;
        $dayBetween->plage = $event->plage;
        $dayBetween->type = $event->type;
        $dayBetween->display_hours = $event->display_hours;
        $dayBetween->menu = $event->menu;
        self::addEvent($dayBetween);
      }
    }

    $right_lefttBorne = CMbDT::date($event->end)." 00:00:00";
    $event_right = new CPlanningEvent($event->guid, $right_lefttBorne, CMbDT::minutesRelative($right_lefttBorne, $event->end), $event->title, $event->color, true, $event->css_class, null, false);
    $event_right->end = $event->end;
    $event_right->mb_object = $event->mb_object;
    $event_right->plage = $event->plage;
    $event_right->type = $event->type;
    $event_right->menu = $event->menu;
    $event_right->display_hours = $event->display_hours;
    self::addEvent($event_right);
  }

  /**
   * Rearrange the current list of events in a optimized way
   *
   * @param boolean $astreinte_sort sort view calendar
   *
   * @return null
   */
  function rearrange($astreinte_sort = true) {
    $events = array();
    //days

    $intervals = array();
    foreach ($this->events_sorted as $_events_by_day) {
      // tab
      foreach ($_events_by_day as $_events_by_hour) {
        foreach ($_events_by_hour as $_event) {

          //used as background, skip the rearrange
          if ($_event->below) {
            $_event->width  = .9;
            $_event->offset = .1;
            continue;
          }

          $intervals[$_event->internal_id] = array(
            "lower" => $_event->start,
            "upper" => $_event->end
          );
          $events[$_event->internal_id]    = $_event;
        }
      }
    }

    $uncollided = array();
    $lines = CMbRange::rearrange($intervals, true, $uncollided, $astreinte_sort);

    $lines_count = count($lines);
    $this->max_height_event = ($lines_count > $this->max_height_event) ? $lines_count : $this->max_height_event;
    foreach ($lines as $_line_number => $_line) {
      foreach ($_line as $_event_id) {
        $event = $events[$_event_id]; //get the event
        $event->height = $_line_number;
        //global = first line
        $event->width = (1 / $lines_count);
        $event->offset = ($_line_number / $lines_count);
        if ($this->allow_superposition) {
          $event->offset+=.05;
        }

        if ($lines_count == 1 && $this->allow_superposition) {
          $event->width =  $event->width-.1;
        }

        //the line is not the first
        if ($_line_number >= 1 && $this->allow_superposition) {
          $event->width = (1 / ($lines_count))+0.05;
          $event->offset = abs(($_line_number / $lines_count)-.1);
        }

        // lines uncollided
        //TODO: fix collisions problems
        if ((in_array($event->internal_id, array_keys($uncollided))) && ($_line_number < ($lines_count-1)) && !$event->below) {
          //$event->width = (($lines_count - ($_line_number)) / $lines_count);
          //$event->width = ($_line_number == 0) ? $event->width-0.1 :$event->width +.05;
        }
      }
    }
  }


  /**
   * load Holidays for the current year
   *
   * @return array _ref_holidays
   */
  function loadHolidays() {
    return $this->_ref_holidays = CMbDT::getHolidays($this->date) + CMbDT::getHolidays(CMbDT::date("-1 YEAR", $this->date));
  }

}
