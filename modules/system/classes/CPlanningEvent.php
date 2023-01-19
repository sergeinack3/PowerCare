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
use Ox\Core\CMbObject;

/**
 * Class CPlanningEvent
 */
class CPlanningEvent implements IShortNameAutoloadable {
  public $guid;
  public $internal_id;
  
  public $title;
  public $icon;
  public $icon_desc;
  public $status;

  public $type;
  public $plage     = array();
  public $menu      = array();
  public $mb_object = array();
  public $datas     = array();      // used for data-xxx="test" for the event div
  
  public $start;
  public $end;
  public $length;
  public $day;
  public $below       = false;
  public $draggable   = false;
  public $resizable   = false;
  public $disabled    = false;

  public $hour;
  public $minutes;
  public $hour_divider;
  public $width;
  public $display_hours = false;  //show start time and end time in the event display (hover)

  public $offset;               //minutes
  public $offset_top;           //minutes
  public $offset_top_text;      //string
  public $offset_bottom;        //minutes
  public $offset_bottom_text;   //string
  public $color;                //aaa
  public $height;
  public $useHeight;
  public $important;
  public $css_class;
  public $onmousover = false;
  public $right_toolbar = false;
  public $highlight;

  public $_ref_object;
  public $_disponibilities = [];
  public $_mode_tooltip;

  // Border for schedules for example (e.g. "Consultation - Semainier")
  public $border_color;
  public $border_title;

  /**
   * constructor
   *
   * @param string      $guid           guid
   * @param string      $date           [day h:m:s]
   * @param int         $length         length of the event (minutes)
   * @param string      $title          title displayed of the event
   * @param null        $color          background color of the event
   * @param bool        $important      is the event important
   * @param null|string $css_class      css class
   * @param null        $draggable_guid is the guid dragable
   * @param bool        $html_escape    do I escape the html from title
   */
  function __construct ($guid, $date, $length = 0, $title = "", $color = null, $important = true, $css_class = null, $draggable_guid = null, $html_escape = true, $icon = null, $icon_desc = null) {
    $this->guid = $guid;
    $this->draggable_guid = $draggable_guid;

    $this->internal_id = "CPlanningEvent-".md5(uniqid('', true));
    
    $this->start = $date;
    $this->length = $length;
    $this->title = $title;

    if (preg_match('/(#){2}/', $color ?? '')) {
      $color = substr($color, 1);
    }

    $this->color = $color;
    $this->icon = $icon;
    $this->icon_desc = $icon_desc;
    $this->important = $important;
    $this->css_class = is_array($css_class) ? implode(" ", $css_class) : $css_class;

    $this->mb_object = array("id" => "", "guid" => "", "view" => "");
    
    if (preg_match("/[0-9]+ /", $this->start)) {
      $parts = explode(" ", $this->start);
      $this->end = "{$parts[0]} ".CMbDT::time("+{$this->length} MINUTES", $parts[1]);
      $this->day = $parts[0];
      $this->hour = CMbDT::format($parts[1], "%H");
      $this->minutes = CMbDT::format($parts[1], "%M");
    }
    else {
      $this->day = CMbDT::date($date);
      $this->end = CMbDT::dateTime("+{$this->length} MINUTES", $date);
      $this->hour = CMbDT::format($date, "%H");
      $this->minutes = CMbDT::format($date, "%M");
    }
  }

  /**
   * assign an object to the event
   *
   * @param CMbObject $mbObject mediboard object
   *
   * @return void
   */
  function setObject($mbObject) {
    $this->mb_object["id"] = $mbObject->_id;
    $this->mb_object["class"] = $mbObject->_class;
    $this->mb_object["guid"] = $mbObject->_guid;
    $this->mb_object["view"] = $mbObject->_view;
  }

  /**
   * check if an event collid with another
   *
   * @param CPlanningEvent $event the event to check
   *
   * @return bool
   */
  function collides(self $event) {
    if ($event == $this || $this->length == 0 || $event->length == 0) {
      return false;
    }

    return ($event->start < $this->end && $event->end > $this->start);
  }

  /**
   * Add a menu to this
   *
   * @param string $type  class of the menu (css class)
   * @param string $title title of the menu (displayed on hover event)
   *
   * @return null
   */
  function addMenuItem($type, $title){
    $this->menu[] = array(
      "class" => $type, 
      "title" => $title,
    );
  }
}
