<?php
/**
 * @package Mediboard\Core\FileUtil
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FileUtil;

use Exception;

/**
 * iCalendar file parser
 */
class ICalendar extends ICalendarComponent {
  /**
   * @var ICalendarComponent
   */
  private $current_component;

  /**
   * @var resource
   */
  private $fp;

  /**
   * @var string
   */
  protected $type = "VCALENDAR";

  /**
   * Creates the iCalendar Object
   *
   * @param string $filename The path to the iCalendar file
   *
   * @throws Exception
   * @return self
   */
  public function __construct($filename) {
    $this->fp = fopen($filename, "r");

    if (!$this->fp) {
      throw new Exception("Unable to open '$filename'");
    }

    $first_line = trim(fgets($this->fp));
    if ($first_line !== "BEGIN:VCALENDAR") {
      throw new Exception("Unexpected start of file '$first_line'");
    }

    $this->current_component = $this;
  }

  /**
   * Closes file pointer handle
   */
  public function __destruct(){
    if($this->fp){
      fclose($this->fp);
    }
  }

  /**
   * Parses the iCalendar file
   *
   * @param int $count The number of lines to read from the file, -1 is infinite
   *
   * @throws Exception
   * @return void
   */
  public function parse($count = -1){
    while ($count-- != 0 && ($line = fgets($this->fp))) {
      $line = trim($line);
      $line = utf8_decode($line);

      if (strpos($line, "BEGIN:") === 0) {
        $type = substr($line, 6);
        $component = new ICalendarComponent($this, $type);
        $this->current_component = $component;

        if (!isset($this->components[$type])) {
          $this->components[$type] = array();
        }

        $this->components[$type][] = $component;
        continue;
      }

      if (strpos($line, "END:") === 0) {
        $type = substr($line, 4);
        if ($this->current_component->getType() !== $type) {
          throw new Exception("Unexpected end of component '$type'");
        }

        $this->current_component = $this;
        continue;
      }

      $matches = array();
      if (preg_match("/([^:]+)[:]([\w\W]*)/", $line, $matches)) {
        list(, $name, $value) = $matches;


        $parts = explode(";", $name);
        $name = $parts[0];

        $params = array();
        if (count($parts) > 1) {
          array_shift($parts);
          foreach ($parts as $_part) {
            $key_val = explode("=", $_part);
            $params[$key_val[0]] = $key_val[1];
          }
        }

        switch ($name) {
          case "CREATED":
          case "LAST-MODIFIED":
          case "DTSTAMP":
          case "DTSTART":
          case "DTEND":
            $value = $this->toISO($value);
            break;
        }

        $replace = array(
          '\\\\n'   => "\n",
          '\\\\\\,' => ",",
          '\\\\\\;' => ";",
          '\\\\"'   => '"',
          '\\\\'    => '',
        );

        $value = strtr($value, $replace);

        $struct = array(
          "value" => $value,
          "params" => $params,
        );

        switch ($name) {
          case "CALSCALE":
          case "VERSION":
          case "PRODID":
          case "UID":
          case "CREATED":
          case "LAST-MODIFIED":
          case "DTSTAMP":
          case "DTSTART":
          case "DTEND":
          case "SUMMARY":
          case "DESCRIPTION":
            $this->current_component->properties[$name] = $struct;
            break;

          default:
            $this->current_component->properties[$name][] = $struct;
        }
      }
    }
  }

  /**
   * Converts an iCalendar date to ISO
   *
   * @param string $date The iCalendar date
   *
   * @return string The ISO date
   */
  public function toISO($date) {
    $pattern = '/(\d{4})(\d{2})(\d{2})T?(\d{0,2})(\d{0,2})(\d{0,2})Z?/';

    $matches = array();
    preg_match($pattern, $date, $matches);

    $iso = "$matches[1]-$matches[2]-$matches[3]";

    if ($matches[4] && $matches[5] && $matches[6]) {
      $iso .= " $matches[4]:$matches[5]:$matches[6]";
    }

    return $iso;
  }
}