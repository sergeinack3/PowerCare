<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
namespace Ox\Mediboard\System;
use Ox\Core\CMbDT;
use Ox\Core\CMbRange;

/**
 * Class CPlanningWeek
 */
class CPlanningWeekNew extends CPlanning {

  /**
   * constructor
   *
   * @param string $date        current date in the planning
   * @param null   $date_min    min date of the planning
   * @param null   $date_max    max
   * @param int    $nb_days     nb of day in the planning
   * @param bool   $selectable  is the planning selectable
   * @param string $height      [optional] height of the planning, default : auto
   * @param bool   $large       [optional] is the planning a large one
   * @param bool   $adapt_range [optional] can the planning adapt the range
   */
  function __construct($date, $date_min = null, $date_max = null, $nb_days = 7, $selectable = false, $height = "auto", $large = false, $adapt_range = false) {
    parent::__construct($date);
    $this->type = "week";
    $this->selectable = $selectable;
    $this->height = $height ? $height : "auto";
    $this->large = $large;
    $this->nb_days = $nb_days;
    $this->adapt_range = $adapt_range;
    $this->maximum_load = 6;

    if (is_int($date) || is_int($date_min) || is_int($date_max)) {
      $this->no_dates = true;
      $this->date_min = $this->date_min_active = $this->_date_min_planning = $date_min;
      $this->date_max = $this->date_max_active = $this->_date_max_planning = $date_max;

      for ($i = 0 ; $i < $this->nb_days ; $i++) {
        $this->days[$i] = array();
        $this->load_data[$i] = array();
      }
    }
    else {
      $days = array("monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday");
      
      $last_day = $days[$this->nb_days - 1];
      
      
      $monday = CMbDT::date("last monday", CMbDT::date("+1 day", $this->date));
      $sunday = CMbDT::date("next $last_day", CMbDT::date("-1 DAY", $this->date));
      
      if (CMbDT::daysRelative($monday, $sunday) > 7) {
        $sunday = CMbDT::date("-7 DAYS", $sunday);
      }
      
      $this->date_min_active = $date_min ? max($monday, CMbDT::date($date_min)) : $monday;
      $this->date_max_active = $date_max ? min($sunday, CMbDT::date($date_max)) : $sunday;

      $this->date_min = $monday;
      $this->date_max = $sunday;
      
      // Days period
      for ($i = 0; $i < $this->nb_days; $i++) {
        $_day = CMbDT::date("+$i day", $monday);
        $this->days[$_day] = array();
        $this->load_data[$_day] = array();
      }
      
      $keys = array_keys($this->days);
      $this->_date_min_planning = reset($keys);
      $this->_date_max_planning = end($keys);
    }

    $this->_hours = array(
      "00", "04", "08", "12", "16", "20"
    );
  }
  /**
   * Add a range to the planning
   *
   * @param CPlanningRange $range a range
   *
   * @return null
   */
  function addRange(CPlanningRange $range) {
    if ($range->day < $this->date_min || $range->day > $this->date_max) {
      return;
    }
    
    $this->has_range = true;
    
    $this->ranges[] = $range;
    $this->ranges_sorted[$range->day][$range->hour][] = $range;
    
    $range->offset = 0.0;
    $range->width = 1.0;
  }

  /**
   * Show the actual time in the planning
   *
   * @param string $color show the actual time
   *
   * @return null
   */
  function showNow($color = "red") {
    $this->addEvent(new CPlanningEvent(null, CMbDT::dateTime(), null, null, $color, null, "now"));
  }
  
  /**
   * Add an unavailability event to the planning
   *
   * @param object $min The min date
   * @param object $max [optional] The max date
   *
   * @return void
   */
  function addUnavailability($min, $max = null) {
    $min = CMbDT::date($min);
    
    $max = $max ? CMbDT::date($max) : $min;
    
    if ($min > $max) {
      list($min, $max) = array($max, $min);
    }
    
    while ($min <= $max) {
      $this->unavailabilities[$min] = true;
      $min = CMbDT::date("+1 DAY", $min);
    }
  }
  
  /**
   * Tell wether given day is active in planning
   *
   * @param string|object $day ISO date
   *
   * @return bool
   */
  function isDayActive($day) {
    return CMbRange::in($day, $this->date_min_active, $this->date_max_active);
  }

  /**
   * Add a label to a day
   *
   * @param object $day     The label's day
   * @param object $text    The label
   * @param object $detail  [optional] Details about the label
   * @param object $color   [optional] The label's color
   * @param string $onclick [optional] a function for the onclick event
   *
   * @return void
   */
  function addDayLabel($day, $text, $detail = null, $color = null, $onclick = null) {
    $this->day_labels[$this->no_dates ? $day : CMbDT::date($day)][] = array(
      "text"   => $text, 
      "detail" => $detail, 
      "color"  => $color,
      "onclick" => $onclick
    );
  }
  
  /**
   * Add a load event
   *
   * @param CPlanningEvent|string $start  an event
   * @param integer               $length [optional] length of the load
   *
   * @return null
   */
  function addLoad($start, $length = null) {
    $this->has_load = true;
    
    if ($start instanceof CPlanningEvent) {
      $event = $start;
      if ($this->no_dates) {
        $day = $event->day;
      }
      else {
        $day = CMbDT::date($event->day);
      }
    }
    else {
      if ($this->no_dates) {
        $day = $start;
      }
      else {
        $day = CMbDT::date($start);
      }
      $event = new CPlanningEvent(null, $start, $length);
    }
    
    $start = $event->start;
    $end   = $event->end;
    
    $div_size = 60 / $this->hour_divider;
    
    $min = round(CMbDT::minutesRelative($day, $start) / $div_size) - 1;
    $max = round(CMbDT::minutesRelative($day, $end)   / $div_size) + 1;

    for ($i = $min; $i <= $max; $i++) {
      $div_min = CMbDT::dateTime("+".($i*$div_size)." MINUTES", $day);
      $div_max = CMbDT::dateTime("+".(($i+1)*$div_size)." MINUTES", $day);
      
      // FIXME: ameliorer ce calcul
      if ($div_min >= $start && $div_min < $end) {
        $hour = CMbDT::transform(null, $div_min, "%H");
        $min = CMbDT::transform(null, $div_min, "%M");
        
        if (!isset($this->load_data[$day][$hour][$min])) {
          $this->load_data[$day][$hour][$min] = 0;
        }
        
        $this->load_data[$day][$hour][$min]++;
      }
    }
  }
}
