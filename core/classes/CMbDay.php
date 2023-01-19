<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */


namespace Ox\Core;

/**
 * Class CMbDay
 * used to manage a day of the year
 */
class CMbDay {

  public $date;         // YYYY-MM-DD
  public $number;       // day nb of the year
  public $name;         // local name of the day. ex: Fête nationale
  public $ferie;        // null|string (if set, the day is an holiday one.

  public $_nbDaysYear;

  public $days_left;

  /**
   * _constructor
   *
   * @param string $date date chosen
   */
  public function __construct($date = null) {
    if (!$date) {
      $date = CMbDT::date();
    }

    $this->date = $date;
    $this->number = (int) CMbDT::transform("", $date, "%j");
    $dateTmp = explode("-", $date);
    $this->name = CMbDT::$days_name[(int) $dateTmp[1]][((int)$dateTmp[2]-1)];

    $this->_nbDaysYear = (CMbDT::format($date, "L")) ? 366 : 365;
    $this->days_left = $this->_nbDaysYear - $this->number;


    //jour férie ?
    $holidays = CMbDT::getHolidays($this->date);
    if (array_key_exists($this->date, $holidays)) {
      $this->ferie = $holidays[$this->date];
    }
  }
}