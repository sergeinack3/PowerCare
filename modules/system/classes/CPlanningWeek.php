<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
namespace Ox\Mediboard\System;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbDT;
use Ox\Core\CMbRange;

/**
 * Class CPlanningWeek
 * @Deprecated use CPlanningWeekNew instead
 */
class CPlanningWeek implements IShortNameAutoloadable {
  public $guid;
  public $title;
  public $nb_week;

  public $date;
  public $selectable;
  public $height;
  public $large;
  public $adapt_range;
  
  public $date_min; // Monday
  public $date_max; // Sunday
  
  public $date_min_active;
  public $date_max_active;

  public $allow_superposition = false;
  
  public $hour_min = "09";
  public $hour_max = "16";
  public $hour_divider = 6;
  public $maximum_load = 6;
  public $has_load  = false;
  public $has_range = false;
  public $show_half = false;
  public $dragndrop = 0;
  public $resizable = 0;
  public $no_dates  = 0;
  public $reduce_empty_lines = 0;
  public $see_nb_week = true;

  public $events = array();
  public $events_sorted = array();
  public $ranges = array();
  public $ranges_sorted = array();

  public $_nb_collisions_ranges_sorted = array();
  
  public $pauses = array("08", "12", "16");
  public $unavailabilities = array();
  public $day_labels = array();
  public $load_data = array();
  
  public $_date_min_planning;
  public $_date_max_planning;
  
  // Periods
  public $hours = array(
    "00", "01", "02", "03", "04", "05", 
    "06", "07", "08", "09", "10", "11", 
    "12", "13", "14", "15", "16", "17", 
    "18", "19", "20", "21", "22", "23", 
  );

  public $days = array();

  private $completion = [];

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
  function __construct(
      $date,
      $date_min = null,
      $date_max = null,
      $nb_days = 7,
      $selectable = false,
      $height = "auto",
      $large = false,
      $adapt_range = false,
      $current_day = false
  ) {
    $this->date = $date;
    $this->selectable = $selectable;
    $this->height = $height ? $height : "auto";
    $this->large = $large;
    $this->nb_days = $nb_days;
    $this->adapt_range = $adapt_range;
    
    if (is_int($date) || is_int($date_min) || is_int($date_max)) {
      $this->no_dates = true;
      $this->date_min = $this->date_min_active = $this->_date_min_planning = $date_min;
      $this->date_max = $this->date_max_active = $this->_date_max_planning = $date_max;

      for ($i = 0 ; $i < $this->nb_days ; $i++) {
        $this->days[$i] = array();
        $this->load_data[$i] = array();
        $this->_nb_collisions_ranges_sorted[$i] = 0;
      }
    }
    else {
      $days = array("monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday");

      $day_used = $current_day ? CMbDT::format($current_day, "%w") : $this->nb_days - 1;
      $last_day = $days[$day_used];

      $monday = $current_day ? $current_day : CMbDT::date("last monday", CMbDT::date("+1 day", $this->date));
      $sunday = $current_day ? $current_day : CMbDT::date("next $last_day", CMbDT::date("-1 DAY", $this->date));

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
        $this->_nb_collisions_ranges_sorted[$_day] = 0;
      }
      
      $keys = array_keys($this->days);
      $this->_date_min_planning = reset($keys);
      $this->_date_max_planning = end($keys);
    }

    $this->nb_week = CMbDT::weekNumber($this->date_min);
  }

  /**
   * @return array filling rates by day
   */
  public function getCompletion(): array {
    return $this->completion;
  }

  /**
   * add an event to the present planning
   *
   * @param CPlanningEvent $event an event
   */
  function addEvent(CPlanningEvent $event): void {
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
    foreach ($this->days[$event->day] as $_event) {
      /** @var CPlanningEvent $_event */
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

    $this->updateDaysCompletion($event);
  }

  /**
   * rearrange the current list of events in a optimized way
   *
   * @param bool $new_mode           use the new mode
   * @param bool $print_all_planning print all planning
   *
   * @return null
   */
  function rearrange($new_mode = false, $print_all_planning = false) {
    $events = array();
    //days
    foreach ($this->events_sorted as $_events_by_day) {
      $intervals = array();
      // tab
      foreach ($_events_by_day as $_events_by_hour) {
        foreach ($_events_by_hour as $_event) {

          //used as background, skip the rearrange
          if ($_event->below) {
            $_event->width = .9;
            $_event->offset = .1;
            continue;
          }

          $intervals[$_event->internal_id] = array(
            "lower" => $_event->start,
            "upper" => $_event->end
          );
          $events[$_event->internal_id] = $_event;
        }
      }

      if ($new_mode) {
        $lines = CMbRange::rearrange2($intervals);

        foreach ($lines as $guid => $_line) {
          /** @var CPlanningEvent $event */
          $event = $events[$guid];
          $event->offset =  ($_line["start"] / $_line["total"])+.1;
          $event->width = ( ($_line["end"] - $_line["start"]) / $_line["total"] ) - 0.1;
        }
      }
      else {
        $lines = CMbRange::rearrange($intervals);

        $lines_count = count($lines);
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
              $event->offset = ($_line_number / $lines_count)-.1;
            }
          }
        }
      }
    }
    if ($print_all_planning) {
      for ($date = $this->date_min; $date < $this->date_max; $date = CMbDT::date("+1 day", $date)) {
        foreach ($this->hours as $hour) {
          $this->events_sorted[$date][$hour] = array();
        }
      }
    }
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

    foreach ($this->ranges as $_range) {
      if (!isset($this->_nb_collisions_ranges_sorted[$range->day])) {
        $this->_nb_collisions_ranges_sorted[$range->day] = 0;
      }
      if ($_range->collides($range)) {
        $this->_nb_collisions_ranges_sorted[$_range->day]++;
      }
    }

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
   * @param string $day       The label's day
   * @param string $text      The label
   * @param string $detail    [optional] Details about the label
   * @param string $color     [optional] The label's color
   * @param string $onclick   [optional] a function for the onclick event
   * @param bool   $draggable [optional] is the label draggable ?
   * @param array  $datas     [optional] html data-elements data-$key => $value
   *
   * @return void
   */
  function addDayLabel($day, $text, $detail = null, $color = null, $onclick = null, $draggable=false, $datas= array()) {
    $this->day_labels[$this->no_dates ? $day : CMbDT::date($day)][] = array(
      "text"        => $text,
      "detail"      => $detail,
      "color"       => $color,
      "onclick"     => $onclick,
      "draggable"   => $draggable,
      "datas"       => $datas
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
      
      // FIXME: ameliorer ce calcul
      if ($div_min >= $start && $div_min < $end) {
        $hour = CMbDT::format($div_min, "%H");
        $min = CMbDT::format($div_min, "%M");
        
        if (!isset($this->load_data[$day][$hour][$min])) {
          $this->load_data[$day][$hour][$min] = 0;
        }
        
        $this->load_data[$day][$hour][$min]++;
      }
    }
  }

  /**
   * Highlight common free events for each day
   *
   * @return void
   */
  function highlight() {
    $nb_days = count($this->day_labels);

    $temp_time = "23:55:00";

    $common_planning = array();
    do {
      $temp_time = CMbDT::time("+5 minutes", $temp_time);
      $common_planning[$temp_time] = 0;
    } while ($temp_time < "23:55:00");

    foreach ($this->events as $_event) {
      list($day, $hour_start) = explode(" ", $_event->start);
      list($day, $hour_end) = explode(" ", $_event->end);
      switch ($_event->type) {
        default:
          $temp_time = $hour_start;
          while ($temp_time < $hour_end) {
            $common_planning[$temp_time]++;
            $temp_time = CMbDT::time("+5 minutes", $temp_time);
          }
          break;
        case "rdvfull":
        case "resfull":
          // Cas d'une consultation immédiate
          if (!isset($common_planning[$hour_start])) {
            $temp_hour = "00:00:00";
            $temp_start = null;
            $temp_end   = null;

            foreach ($common_planning as $_hour => $_common_planning) {
              if ($hour_start > $temp_hour && $hour_start < $_hour) {
                $temp_start = $temp_hour;
              }
              if ($hour_end > $temp_hour && $hour_end < $_hour) {
                $temp_end = $_hour;
              }

              $temp_hour = $_hour;
            }

            if ($temp_start) {
              $hour_start = $temp_start;
            }
            if ($temp_end) {
              $hour_end = $temp_end;
            }
          }
          foreach ($common_planning as $_hour => $_common_planning) {
            if ($_hour >= $hour_start && $_hour < $hour_end) {
              $common_planning[$_hour]--;
            }
          }
      }
    }

    foreach ($common_planning as $_hour => $_nb_planning) {
      if ($_nb_planning < $nb_days) {
        unset($common_planning[$_hour]);
      }
    }

    // Ajout d'une dernière entrée pour gérer la fin du dernier intervalle
    $keys_common = array_keys($common_planning);
    $last_key = end($keys_common);
    $common_planning[CMbDT::time("+5 minutes", $last_key)] = $nb_days;

    foreach ($this->events_sorted as $_events_by_prat) {
      foreach ($_events_by_prat as $hour => $_events_by_hour) {
        foreach ($_events_by_hour as $_event) {
          list($day, $hour_start) = explode(" ", $_event->start);
          list($day, $hour_end) = explode(" ", $_event->end);

          $highlight = true;

          $temp_start = $hour_start;

          while ($temp_start <= $hour_end) {
            if (!isset($common_planning[$temp_start])) {
              $highlight = false;
              break;
            }

            $temp_start = CMbDT::time("+5 minutes", $temp_start);
          }

          $_event->highlight = $highlight;
        }
      }
    }

    // Correction des événements mis en surbrillance à tort
    $common_planning = array();

    foreach ($this->events_sorted as $_events_by_prat) {
      foreach ($_events_by_prat as $hour => $_events_by_hour) {
        foreach ($_events_by_hour as $_event) {
          if (!$_event->highlight || !in_array($_event->type, array("rdvfree", "resfree"))) {
            continue;
          }

          list($day, $hour_start) = explode(" ", $_event->start);
          list($day, $hour_end)   = explode(" ", $_event->end);

          $temp_start = $hour_start;

          while ($temp_start < $hour_end) {
            @$common_planning[$temp_start]++;
            $temp_start = CMbDT::time("+5 minutes", $temp_start);
          }
        }
      }
    }

    foreach ($this->events_sorted as $_events_by_prat) {
      foreach ($_events_by_prat as $hour => $_events_by_hour) {
        foreach ($_events_by_hour as $_event) {
          if (!$_event->highlight || !in_array($_event->type, array("rdvfree", "resfree"))) {
            continue;
          }

          list($day, $hour_start) = explode(" ", $_event->start);

          if ($common_planning[$hour_start] != $nb_days) {
            $_event->highlight = 0;
          }
        }
      }
    }
  }

  /**
   * Update filling rate of days
   *
   * @param CPlanningEvent $event added event
   */
  private function updateDaysCompletion(CPlanningEvent $event): void {
    if (!array_key_exists($event->day, $this->completion)) {
      $this->completion[$event->day] = [
        "total" => 0,
        "full" => 0
      ];
    }

    $this->completion[$event->day]["total"]++;
    if (strpos($event->type, "rdvfree") === false) {
      $this->completion[$event->day]["full"]++;
    }
  }
}
