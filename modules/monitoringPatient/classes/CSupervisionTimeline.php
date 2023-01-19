<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\MonitoringPatient;

use JsonSerializable;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use ReturnTypeWillChange;

/**
 * Supervision timeline utility class
 */
class CSupervisionTimeline implements ISupervisionTimelineItem, JsonSerializable, IShortNameAutoloadable {
  public $identifier;

  /** @var CStoredObject */
  public $object;

  public $options;
  public $groups;
  public $items;

  /**
   * CSupervisionTimeline constructor.
   *
   * @param CStoredObject|null $object
   */
  public function __construct(CStoredObject $object = null) {
    $this->object = $object;
  }

  /**
   * @return string
   */
  public function getIdentifier() {
    if ($obj = $this->object) {
      return $obj->_guid;
    }

    return $this->identifier;
  }

  /**
   * @return array
   */
  public function getData() {
    return array(
      "groups"  => $this->groups,
      "items"   => $this->items,
      "options" => $this->options,
    );
  }

  static $custom_data_fields = array(
    "user",
    "user_id",
    "file_id",
    "result_id",
    "label_id",
    "set_id",
  );

  static $timeline_options = array(
    "dataAttributes"  => "all",
    "locale"          => 'fr',
    "margin"          => 0,
    "zoomKey"         => 'ctrlKey',
    "showCurrentTime" => false,
    "zoomable"        => true,

    "start" => null,
    "end"   => null,
  );

  static $background_item = array(
    "id"   => "background",
    "type" => "background",

    "start" => null,
    "end"   => null,
  );

  /**
   * Build the whole timeline, with the graphs and the timeline.js objects
   *
   * @param COperation            $interv       Operation
   * @param CSupervisionGraphPack $pack         Graph pack
   * @param bool                  $readonly     Make it readonly
   * @param string                $type         Type: perop, sspi, preop, etc
   * @param array                 $items        The list of item identifiers to load
   * @param string                $element_main Element main to display
   * @param bool                  $print        Print timeline
   *
   * @return array
   * @throws \Exception
   */
  static function makeTimeline(COperation $interv, CSupervisionGraphPack $pack, $readonly = false, $type = "perop", $items = null, $element_main = null, $print = false) {
    [
      $graphs, $yaxes_count,
      $time_min, $time_max,
      $time_debut_op_iso, $time_fin_op_iso
      ] = SupervisionGraph::buildGraphs($interv, $pack, $type, $items, $print);

    self::$background_item["start"] = CMbDT::toTimestamp($time_debut_op_iso);
    self::$background_item["end"]   = CMbDT::toTimestamp($time_fin_op_iso);
    //self::$background_item["timeAxis"] = array("scale" => "minute", "step" => 10);

    self::$timeline_options["start"] = $time_min;
    self::$timeline_options["end"]   = $time_max;
    //self::$timeline_options["timeAxis"] = array("scale" => "minute", "step" => 10);
    /*self::$timeline_options["zoomMin"] = array("scale" => "minute", "step" => 10);
    self::$timeline_options["zoomMax"] = array("scale" => "minute", "step" => 10);*/

    $sejour       = $interv->loadRefSejour();
    $grossesse    = $sejour->loadRefGrossesse();
    $current_user = CMediusers::get();

    $display_current_time = array("sspi" => 1, "perop" => 1, "preop" => 1, "current_user_id" => $current_user->_id);

    if ($interv->fin_prepa_preop || $interv->entree_salle) {
      $display_current_time["preop"] = 0;
    }

    if ($interv->fin_op) {
      $display_current_time["perop"] = 0;
    }

    if ($interv->sortie_reveil_reel) {
      $display_current_time["sspi"] = 0;
    }

    // TimedItems and Pictures to Timeline
    $_i = null;
    foreach ($graphs as $_i => $_graph) {
      if (!$_graph instanceof CSupervisionTimeline) {
        continue;
      }

      $_groups = array();
      $_items  = array(
        self::$background_item,
      );

      $_object = $_graph->object;

      $_id       = count($_groups) + 1;
      $_groups[] = array(
        "id"      => $_id,
        "content" => $_object->_view,
      );

      foreach ($_object->_graph_data as $_data) {
        $_item_id = count($_items) + 1;

        $_title = sprintf(
          "%s\n -- %s",
          CMbDT::format($_data["datetime"], CAppUI::conf("datetime")),
          $_data["user"]
        );

        $_item = array(
          "id"          => $_item_id,
          "group"       => $_id,
          "pack_id"     => $pack->_id,
          "content"     => wordwrap(nl2br($_data["value"]), 30, "<br />"),
          "start"       => CMbDT::toTimestamp($_data["datetime"]),
          "type"        => "point",
          "title"       => $_title,
          "editable"    => (($current_user->_id == $_data["user_id"]) && ($_object instanceof CSupervisionTimedData || $_object instanceof CSupervisionTimedPicture) && !$readonly) ? true : false,
          "class_group" => $_object->_class,
          "className"   => "planif_success",
          "user_id"     => $_data["user_id"]
        );

        foreach (self::$custom_data_fields as $_name) {
          $_item[$_name] = $_data[$_name];
        }

        if ($_object instanceof CSupervisionTimedPicture) {
          $_item["content"] = sprintf(
            '<img style="height: 50px; width=100px;" src="?m=files&raw=thumbnail&document_guid=CFile-%d&' .
            'profile=medium"><br />%s',
            $_data["file_id"],
            $_data["file"]->_no_extension
          );
        }

        $_items[] = $_item;
      }

      $graphs[$_i]->items  = $_items;
      $graphs[$_i]->groups = $_groups;
    }

    $evenement_groups = array();
    $evenement_items  = array();

    if (!$element_main || $element_main === "supervision-timeline-geste") {
      // Gestes Perop
      $element_main_geste = "supervision-timeline-geste";

      [$evenement_groups, $evenement_items] =
        SupervisionGraph::buildEventsGrid($interv, $readonly, $type, $pack, $element_main_geste, $print);
      $evenement_items[] = self::$background_item;

      $geste_timeline             = new CSupervisionTimeline();
      $geste_timeline->items      = $evenement_items;
      $geste_timeline->groups     = $evenement_groups;
      $geste_timeline->identifier = "supervision-timeline-geste";
      $graphs[]                   = $geste_timeline;
    }

    if (!$element_main || $element_main === "supervision-timeline") {
      // The last timeline
      $element_main_admin = "supervision-timeline";

      [$evenement_groups, $evenement_items] =
          SupervisionGraph::buildEventsGrid($interv, $readonly, $type, $pack, $element_main_admin, $print);

      $evenement_items[]         = self::$background_item;
      $last_timeline             = new CSupervisionTimeline();
      $last_timeline->items      = $evenement_items;
      $last_timeline->groups     = $evenement_groups;
      $last_timeline->identifier = "supervision-timeline";
      $graphs[]                  = $last_timeline;
    }

    return array(
      $graphs, $yaxes_count,
      $time_min, $time_max,
      $time_debut_op_iso, $time_fin_op_iso,
      $evenement_groups, $evenement_items,
      self::$timeline_options,
      $display_current_time
    );
  }

    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return (array)$this;
    }
}
