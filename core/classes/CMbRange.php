<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

/**
 * Generic range calculation.
 * A range is a given pair with a lower and upper bound
 * A null bound is infinite (minus for lower, plus for upper)
 */
abstract class CMbRange {
  /**
   * Tell whether range is void (empty)
   *
   * @param mixed $lower The lower bound
   * @param mixed $upper The upper bound
   *
   * @return boolean 
   */
  static function void($lower, $upper, $permissive = true) {
    return $permissive
      ? ($upper < $lower && $lower !== null && $upper !== null)
      : ($upper <= $lower && $lower !== null && $upper !== null);
  }

  /**
   * Tell whether range is finite
   *
   * @param mixed $lower The lower bound
   * @param mixed $upper The upper bound
   *
   * @return boolean 
   */
  static function finite($lower, $upper) {
    return ($lower !== null && $upper !== null);
  }

  /**
   * Tell whether given value is in range (permissive)
   *
   * @param mixed $value The value to check
   * @param mixed $lower The lower bound
   * @param mixed $upper The upper bound
   *
   * @return boolean 
   */
  static function in($value, $lower, $upper) {
    return 
      ($value <= $upper || $upper === null) && 
      ($value >= $lower || $lower === null);
  }
  
  /**
   * Tell whether two ranges collide (permissive)
   *
   * @param mixed   $lower1
   * @param mixed   $upper1
   * @param mixed   $lower2
   * @param mixed   $upper2
   * @param boolean $permissive
   *
   * @return boolean
   */
  static function collides($lower1, $upper1, $lower2, $upper2, $permissive = true) {
    return 
      $permissive ?
        ($lower1 <  $upper2 || $lower1 === null || $upper2 === null) &&
        ($upper1 >  $lower2 || $upper1 === null || $lower2 === null) :
        ($lower1 <= $upper2 || $lower1 === null || $upper2 === null) && 
        ($upper1 >= $lower2 || $upper1 === null || $lower2 === null);
  }

  /**
   * Get the intersection of two ranges (permissive)
   * Result intersection might be empty, that is with upper < lower bound
   *
   * @param mixed $lower1
   * @param mixed $upper1
   * @param mixed $lower2
   * @param mixed $upper2
   *
   * @return array($lower, $upper)
   */
  static function intersection($lower1, $upper1, $lower2, $upper2) {
    return array (
      ($lower1 !== null && $lower2 !== null ) ? max($lower1, $lower2) : null,
      ($upper1 !== null && $upper2 !== null ) ? min($upper1, $upper2) : null,
    );
  }
  
  /**
   * Tell whether range1 is inside range2 (permissive)
   *
   * @param mixed $lower1
   * @param mixed $upper1
   * @param mixed $lower2
   * @param mixed $upper2
   *
   * @return boolean
   */
  static function inside($lower1, $upper1, $lower2, $upper2) {
    list($lower, $upper) = self::intersection($lower1, $upper1, $lower2, $upper2);
    return $lower == $lower1 && $upper == $upper1;
  }
  
  /**
   * Crop a range with another, resulting in 0 to 2 range fragments
   * Limitation: cropper has to be finite
   *
   * @param mixed $lower1     Cropped range
   * @param mixed $upper1     Cropped range
   * @param mixed $lower2     Cropper range
   * @param mixed $upper2     Cropper range
   * @param bool  $permissive permissive mode
   *
   * @return array Array of range fragments, false on infinite cropper
   */
  static function crop($lower1, $upper1, $lower2, $upper2, $permissive = true) {
    if (!self::finite($lower2, $upper2)) {
      return false;
    }
    
    $fragments = array();

    // No collision: cropped intact
    if (!self::collides($lower1, $upper1, $lower2, $upper2, $permissive)) {
      $fragments[] = array("lower" => $lower1, "upper" => $upper1);
      return $fragments;
    }


    // Right fragment
    if ($lower2 <= $upper1 || $upper1 === null) {
      if (!self::void($lower1, $lower2, $permissive)) {
        $fragments[] = array("lower" => $lower1, "upper" => $lower2);
      }
    }

    // Left fragment
    if ($upper2 >= $lower1 || $lower1 === null) {
      if (!self::void($upper2, $upper1, $permissive)) {
        $fragments[] = array("lower" => $upper2, "upper" => $upper1);
      }
    }
    
    return $fragments;
  }

  /**
   * gather all ranges into englobling one
   *
   * @param array $ranges Array of ranges
   *
   * @return array
   */
  static function englobe($ranges) {
    $fragment = array();
    foreach ($ranges as $_range) {
      if (!array_key_exists("lower", $fragment)) {
        $fragment["lower"] = $_range["lower"];
      }
      if (!array_key_exists("upper", $fragment)) {
        $fragment["upper"] = $_range["upper"];
      }

      $fragment["lower"] = min($_range["lower"], $fragment["lower"]);
      $fragment["upper"] = max($_range["upper"], $fragment["upper"]);

    }

    return $fragment;
  }

  /**
   * return the ranges Union a range
   *
   * @param array $ranges array of ranges
   * @param array $range  range to add with key lower => data, upper => data
   *
   * @return array
   */
  static function union(&$ranges, $range) {
    foreach ($ranges as $key => $_range) {
      if (self::collide($range, $_range)) {
        $range = self::englobe(array($range, $_range));
        unset($ranges[$key]);
      }
    }
    $ranges[] = $range;
    return $ranges;
  }

  /**
   * Crop many ranges with many others, resulting in 0 to n range fragments
   * Limitation: cropper has to be finite
   *
   * @param array $fragments  Array of ranges
   * @param array $croppers   Array of cropper ranges
   * @param bool  $permissive permissive mode
   *
   * @return array Array of range fragments, false on infinite cropper
   */
  static function multiCrop($fragments, $croppers, $permissive = true) {

    foreach ($croppers as $_cropper) {
      $new_fragments = array();
      foreach ($fragments as $_fragment) {
        $new_fragments = array_merge($new_fragments, self::crop($_fragment["lower"], $_fragment["upper"], $_cropper["lower"], $_cropper["upper"], $permissive));
      }
      $fragments = $new_fragments;
    }

    return $fragments;
  }
  
  static function forceInside($lower, $upper, $value) {
    $value = max($value, $lower);
    $value = min($value, $upper);
    return $value;
  }
  
  /**
   * @deprecated use rearrange2
   * rearrange a list of object in an optimized list
   * 
   * @param array   $intervals      $intervals key => array(lower, upper);
   * @param boolean $permissive     [optional]
   * @param array   $uncollided    array of uncollided elements
   * @param boolean $astreinte_sort sort view calendar
   *
   * @return array $lines lignes avec les keys positionned
   */
  static function rearrange($intervals, $permissive = true, &$uncollided = array(), $astreinte_sort = true)  {
    if (!count($intervals)) {
      return array();
    }
    $lines = array();
    $uncollided = $intervals;
    // multisort ruins the keys if numeric
    $interval_keys = array_keys($intervals);
    if (!is_numeric(reset($interval_keys)) && $astreinte_sort) {
      array_multisort($intervals, SORT_ASC, CMbArray::pluck($intervals, "lower")); //order by lower elements ASC
    }
    foreach ($intervals as $_interval_id => $_interval) {
      foreach ($lines as &$_line) {
        $line_occupied = false;
        foreach ($_line as $_positioned_id) {
          $positioned = $intervals[$_positioned_id];
          if (CMbRange::collides($_interval["lower"], $_interval["upper"], $positioned["lower"], $positioned["upper"], $permissive)) {
            $line_occupied = true;
            unset($uncollided[$_positioned_id]);
            //continue 2; // Next line
          }
        }
        if ($line_occupied) {
          continue;
        }

        $_line[] = $_interval_id;
        continue 2; // Next interval
      }
      $lines[count($lines)] = array($_interval_id);
    }
    return $lines;
  }

  /**
   * rearrange intervals in an optimized way
   *
   * @param array $intervals
   *
   * @return array
   */
  static function rearrange2($intervals) {
    if (!count($intervals)) {
      return array();
    }

    // 1. Sort by min then max for each events
    //uasort($intervals, array("CMbRange", "compare"));


    // 2. Gather all collision
    $collisions = array();
    foreach ($intervals as $key1 => $event1) {
      $collisions[$key1] = array();
      foreach ($intervals as $key2 => $event2) {
        if ($key1 !== $key2 && self::collide($event1, $event2)) {
          $collisions[$key1][$key2] = $key2;
        }
      }
    }

    // 3. Builds grapes recursively
    $grapes = array();
    $grapables = array_combine(array_keys($intervals), array_keys($intervals));
    while (count($grapables)) {
      $grape_key = "grape-". count($grapes);
      $event_key = reset($grapables);
      self::engrape($grapes, $grapables, $collisions, $grape_key, $event_key);
    }

    // 4. For each grape, place events on columns
    $columns = array();
    foreach ($grapes as $_grape_key => $_grape) {
      $columns[$_grape_key] = array();
      // Place events on actual columns
      foreach ($_grape as $_event_key) {
        // Trying to place event on the first available existing column
        foreach ($columns[$_grape_key] as $_column_key => $placed_event_keys) {
          if (!count(array_intersect($collisions[$_event_key], $placed_event_keys))) {
            $columns[$_grape_key][$_column_key][$_event_key] = $_event_key;
            continue 2;
          }
        }

        // No suitable column found, create one
        $column_key = count($columns[$_grape_key]);
        $columns[$_grape_key][$column_key][$_event_key] = $_event_key;
      }
    }

    // 5. Build positions for events
    $positions = array();
    // Parse columns to prepare event positions
    foreach ($grapes as $_grape_key => $_grape) {
      foreach ($columns[$_grape_key] as $_column_key => $_event_keys) {
        foreach ($_event_keys as $_event_key) {
          $positions[$_event_key] = array(
            "total" => count($columns[$_grape_key]),
            "start" => $_column_key,
            "end"   => count($columns[$_grape_key]),
          );
        }
      }
    }

    foreach ($positions as $_event_key => &$_position) {
      foreach ($collisions[$_event_key] as $_collider_key) {
        $collider_start = $positions[$_collider_key]["start"];
        if ($_position["start"] < $collider_start && $_position["end"] > $collider_start) {
          $_position["end"] = $collider_start;
        }
      }
    }

    return $positions;
  }


  static function engrape(&$grapes, &$grapables, $collisions, $grape_key, $event_key) {
    // Event already in a grape
    if (!isset($grapables[$event_key])) {
      return;
    }

    // Put event in the current grape
    $grapes[$grape_key][$event_key] = $event_key;
    unset($grapables[$event_key]);

    // Recurse on colliding events for same grape
    foreach ($collisions[$event_key] as $_collider_key) {
      self::engrape($grapes, $grapables, $collisions, $grape_key, $_collider_key);
    }
  }

  static function collide($event1, $event2) {
    return ($event1["lower"] < $event2["upper"] && $event2["lower"] < $event1["upper"]);
  }

  /**
   * @param array $event1
   * @param array $event2
   *
   * @return mixed
   */
  static function compare($event1, $event2) {
    return $event1["lower"] != $event2["lower"] ?
      ($event1["lower"] < $event2["lower"] ? -1 : 1 ):
      ($event2["upper"] < $event1["upper"] ? -1 : 1 );
  }
}