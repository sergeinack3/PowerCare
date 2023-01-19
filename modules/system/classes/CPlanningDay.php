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
class CPlanningDay extends CPlanning {


  /**
   * constructor
   *
   * @param string $date current date in the planning
   */
  function __construct($date) {
    parent::__construct($date);
    $this->type = "day";
    $this->date = $date;
    $this->height = "auto";
    $this->nb_days = 1;

    $this->maximum_load = 1;

    $this->no_dates = true;
    $this->date_min = $this->date_min_active = $this->_date_min_planning = $date;
    $this->date_max = $this->date_max_active = $this->_date_max_planning = $date;

    $this->days[$date] = array();
    $this->load_data = array("");

    $this->_hours = array(
      "00", "01", "02", "03", "04", "05",
      "06", "07", "08", "09", "10", "11",
      "12", "13", "14", "15", "16", "17",
      "18", "19", "20", "21", "22", "23",
    );

    $this->date_min_active = $this->date_min_active = $this->date_min = $this->date_max_active = $this->date_max_active = $this->date_max = $date;
  }

  /**
   * add an event to the present planning
   *
   * @param CPlanningEvent $event an event
   *
   * @return null
   */
  function addEvent(CPlanningEvent $event) {
    //start plage out of borne
    $date_start = CMbDT::date($event->start);
    if ($date_start != $this->date) {
      $event->day= $this->date;
      $event->start= $this->date." 00:00:00";
      $event->hour = "00";
      $event->minutes = "00";
      $event->length = CMbDT::minutesRelative($event->start, $event->end);
    }

    //end of plage is out of borne
    $date_end = CMbDT::date($event->end);
    if ($date_end != $this->date) {
      $event->length = CMbDT::minutesRelative($event->start, $this->date." 23:59:59");
    }

    parent::addEvent($event);
  }
}
