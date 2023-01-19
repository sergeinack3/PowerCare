<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
namespace Ox\Mediboard\System;
use Ox\Core\CMbDT;

/**
 * Class CPlanning
 */
class CPlanningMonth extends CPlanning {

  public $first_day_of_first_week;
  public $last_day_of_last_week;
  public $today;

  public $days_by_week;
  public $classes_for_days;

  public $next_month;
  public $previous_month;

  /**
   * constructor
   *
   * @param string $date current date in the planning
   */
  /**
   * constructor
   *
   * @param string $date        current date in the planning
   * @param null   $date_min    min date of the planning
   * @param null   $date_max    max
   * @param bool   $selectable  is the planning selectable
   * @param string $height      [optional] height of the planning, default : auto
   * @param bool   $large       [optional] is the planning a large one
   * @param bool   $adapt_range [optional] can the planning adapt the range
   */
  function __construct($date, $date_min = null, $date_max = null, $selectable = false, $height = "auto", $large = false, $adapt_range = false) {
    parent::__construct($date);
    $this->today = CMbDT::date();
    $this->type = "month";
    $this->selectable = $selectable;
    $this->height = $height ? $height : "auto";
    $this->large = $large;
    $this->adapt_range = $adapt_range;

    $this->no_dates = true;

    if (is_int($date) || is_int($date_min) || is_int($date_max)) {
      $this->no_dates = true;
      $this->date_min = $this->date_min_active = $this->_date_min_planning = $date_min;
      $this->date_max = $this->date_max_active = $this->_date_max_planning = $date_max;
      $this->nb_days = (CMbDT::transform(null, $this->date_max, "%d") - CMbDT::transform(null, $this->date_min, "%d"));

      for ($i = 0 ; $i < $this->nb_days ; $i++) {
        $this->days[$i] = array();
        $this->load_data[$i] = array();
      }
    }
    else {
      $this->date_min = $this->date_min_active = $this->_date_min_planning = CMbDT::date("first day of this month"   , $date);
      $this->date_max = $this->date_max_active = $this->_date_max_planning = CMbDT::date("last day of this month", $this->date_min);

      // add the last days of previous month
      $min_day_number = CMbDT::format($this->date_min, "%w");
      $this->first_day_of_first_week = $first_day = CMbDT::date("this week", ($min_day_number == 0) ? CMbDT::date("-1 DAY", $this->date_min) :  $this->date_min) ;
      while ($first_day != $this->date_min) {
        $this->days[$first_day] = array();
        $first_day = CMbDT::date("+1 DAY", $first_day);
      }

      $this->nb_days = CMbDT::transform(null, $this->date_max, "%d");
      for ($i = 0; $i < $this->nb_days; $i++) {
        $_day = CMbDT::date("+$i day", $this->date_min);
        $this->days[$_day] = array();
        $this->load_data[$_day] = array();
      }

      //fill the rest of the last week
      $max_day_number = CMbDT::format($this->date_max, "%w");
      if ($max_day_number != 0) {
        $last_day_of_week = CMbDT::date("this week +6 days", $this->date_max);
        $last_day_of_month = $this->date_max;
        while ($last_day_of_month <= $last_day_of_week ) {
          $this->days[$last_day_of_month] = array();
          $last_day_of_month = CMbDT::date("+1 DAY", $last_day_of_month);
        }
      }

      $this->classes_for_days = $this->days;
    }

    $this->previous_month = CMbDT::date("-1 DAY", $this->date_min);
    $this->next_month     = CMbDT::date("+1 DAY", $this->date_max);

    $keys_days = array_keys($this->days);
    $this->_date_min_planning = reset($keys_days);
    $this->_date_max_planning = end($keys_days);

    $this->_hours = array();
  }

  /**
   * assign a class string to a day for calendar
   *
   * @param string $class class to add
   * @param string $day   day targeted
   *
   * @return void
   */
  function addClassesForDay($class, $day) {
    $this->classes_for_days[$day][] = $class;
  }

}
