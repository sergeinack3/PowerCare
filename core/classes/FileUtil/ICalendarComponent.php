<?php
/**
 * @package Mediboard\Core\FileUtil
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FileUtil;

/**
 * iCalendar component
 */
class ICalendarComponent {
  /**
   * @var array[]
   */
  public $properties = array();

  /**
   * @var ICalendarComponent[]
   */
  public $components = array();

  /**
   * @var ICalendar
   */
  private $calendar;

  /**
   * @var string
   */
  protected $type;

  static $types = array(
    "VALARM",
    "VEVENT",
    "VFREEBUSY",
    "VJOURNAL",
    "VTIMEZONE",
    "VTODO",
  );

  /**
   * Component constructor
   *
   * @param ICalendar $calendar Parent calendar
   * @param string    $type     Type
   */
  public function __construct(ICalendar $calendar, $type) {
    $this->calendar = $calendar;
    $this->type = $type;
  }

  /**
   * Get component type
   *
   * @return string
   */
  public function getType(){
    return $this->type;
  }

  /**
   * Get a property by its name
   *
   * @param string $name Property name
   *
   * @return array
   */
  public function getProperty($name){
    if (!isset($this->properties[$name])) {
      return null;
    }

    return $this->properties[$name];
  }

  /**
   * Get a property scalar value by its name
   *
   * @param string $name Property name
   *
   * @return string The propery value
   */
  public function getPropertyValue($name) {
    if (!isset($this->properties[$name])) {
      return null;
    }

    return $this->properties[$name]["value"];
  }

  /**
   * Get the components by their type
   *
   * @param string $type The components' type
   *
   * @return ICalendarComponent[]
   */
  public function getComponents($type){
    if (!isset($this->components[$type])) {
      return array();
    }

    return $this->components[$type];
  }

  /**
   * __toString magic method
   *
   * @return string The string value of the object
   */
  public function __toString(){
    $str = "<ul>";

    // Properties
    $count = count($this->properties);
    if ($count) {
      $str .= "<li>Properties ($count)<ul>";

      foreach ($this->properties as $_name => $_value) {
        if (isset($_value["value"])) {
          $_val = $_value['value'];
          $str .= "<li><strong>$_name</strong>: $_val</li>\n";
        }
        else {
          $count = count($_value);
          $str .= "<li><strong>$_name</strong> ($count)<ul>";

          foreach ($_value as $_val) {
            $_val = $_val['value'];
            $str .= "<li>$_val</li>\n";
          }
          $str .= "</ul></li>";
        }
      }
      $str .= "</ul>";
    }

    // Components
    $count = count($this->components);
    if ($count) {
      $str .= "<li>Components ($count)<ul>";

      foreach ($this->components as $_type => $_components) {
        $str .= "<li><strong>$_type</strong><ul>";

        foreach ($_components as $_component) {
          $str .= $_component;
        }
        $str .= "</ul></li>";
      }
      $str .= "</ul></li>";
    }

    return "$str</ul>";
  }
}
