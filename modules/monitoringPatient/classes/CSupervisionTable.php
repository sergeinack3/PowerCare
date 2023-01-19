<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\MonitoringPatient;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\PlanningOp\COperation;

/**
 * Represents a table of observation values displayed in the supervisions views
 */
class CSupervisionTable extends CSupervisionTimedEntity implements ISupervisionTimelineItem {
  /** @var integer Primary key */
  public $supervision_table_id;

  /** @var string The sampling frequency */
  public $sampling_frequency;

  /** @var string Indicate if the data are sent by an automatic protocol (like the concentrator) */
  public $automatic_protocol;

  /** @var CSupervisionTableRow[] The rows */
  public $_ref_rows;

  /** @var array The timeline groups (for the display with Vis.js) */
  public $_timeline_groups = array();

  /** @var array The timeline items (for the display with Vis.js) */
  public $_timeline_items = array();

  /** @var array The timeline options (for the display with Vis.js) */
  public $_timeline_options = array();

  /** @var array The timeline timings (for the display in the constants table) */
  public $_timings = array();

  /**
   * Initialize the class specifications
   *
   * @return CMbObjectSpec
   */
  public function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "supervision_tables";
    $spec->key   = "supervision_table_id";

    return $spec;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  public function getProps() {
    $props = parent::getProps();

    $props['owner_id']          .= ' back|supervision_tables';
    $props['sampling_frequency'] = 'enum list|1|3|5|10|15';
    $props['automatic_protocol'] = 'enum list|Kheops-Concentrator|MD-Stream';

    return $props;
  }

  /**
   * Load the rows
   *
   * @return CSupervisionTableRow[]
   */
  public function loadRefsRows() {
    return $this->_ref_rows = $this->loadBackRefs('rows');
  }

  /**
   * Build the data for a timeline display from the given observation results
   *
   * @param array      $times     The timings
   * @param array      $results   The results
   * @param string     $start     Start datetime
   * @param string     $end       End datetime
   * @param COperation $operation The operation
   * @param string     $type      The type of surpervision view
   * @param int        $pack_id   Graph pack ID
   *
   * @return void
   */
  public function buildTimeline($times, $results, $start, $end, $operation = null, $type = 'perop', $pack_id = null) {
    if (is_string($start)) {
      $start = CMbDT::toTimestamp($start);
    }
    if (is_string($end)) {
      $end = CMbDT::toTimestamp($end);
    }

    $this->build($times, $results, $start, $end);

    $this->_timeline_options = array(
      "showMajorLabels" => true,
      "showMinorLabels" => true,
      'stack'           => true
    );

    $this->_timeline_items[] = array(
      'id'    => 'background',
      'type'  => 'background',
      'start' => $start,
      'end'   => $end
    );

    $group_index = 1;
    $item_index  = 1;
    foreach ($this->_ref_rows as $row) {
      if ($row->active) {
        $group = array(
          'content' => $row->_view ? $row->_view : '',
          'id'      => $group_index,
          'title'   => $row->_view ? $row->_view : '',
          'type'    => $row->value_type_id,
          'unit'    => $row->value_unit_id
        );

        if ($row->color) {
          $group['style'] = "color: #{$row->color}";
        }

        $this->_timeline_groups[] = $group;

          foreach ($row->_data as $time => $values) {
              $content_data = '';
              if ($values['value']) {
                  $content_data = "<span onclick=\"SurveillancePerop.editObservationResultSet('{$values['set_id']}', '{$pack_id}', {$values['result_id']}, null, true, '{$type}', '{$operation->_id}');\" 
                              style=\"float: right;\">{$values['value']}</span>";
              }

              $item = [
                  'id'      => $item_index++,
                  'group'   => $group_index,
                  'start'   => $time,
                  'content' => $content_data,
                  'title'   => date('H:i', $time / 1000),
              ];

              if ($row->color) {
                  $item['style'] = "color: #{$row->color};";
              }

              $this->_timeline_items[] = $item;
          }
      }

        $group_index++;
    }

      $content = $this->_view;
    if ($operation && $operation->_id && !$this->automatic_protocol) {
      $content = "<button type=\"button\" class=\"fas fa-table notext me-tertiary\"
                          onclick=\"SurveillancePerop.displaySurveillanceTable('{$operation->_id}', '{$type}', '{$this->_id}');\" 
                          style=\"float: right;\">" . CAppUI::tr('CSupervisionTable-action-view_table') . "</button>{$content}";
    }

    $group = array(
      'content'      => $content,
      'id'           => 0,
      'title'        => $this->_view,
      'nestedGroups' => array_keys($this->_timeline_groups)
    );

    $this->_timeline_groups[] = $group;
  }

  /**
   * Build the data from the given observation results
   *
   * @param array  $times   The timings
   * @param array  $results The results
   * @param string $start   Start datetime
   * @param string $end     End datetime
   *
   * @return void
   */
  public function build($times, $results, $start, $end) {
    $this->loadRefsRows();

    $this->_timings = $times;

    foreach ($this->_ref_rows as $row) {
      $row->build($results, $start, $end);
    }
  }

  /**
   * Get all the tables from an object
   *
   * @param CMbObject $object The object to get the graphs from
   *
   * @return self[]
   */
  static function getAllFor(CMbObject $object) {
    $graph = new self;

    $where = array(
      'owner_class' => "= '$object->_class'",
      'owner_id'    => "= '$object->_id'",
    );

    return $graph->loadList($where, 'title');
  }

  /**
   * @return string
   */
  public function getIdentifier() {
    return $this->_guid;
  }

  /**
   * @return array
   */
  public function getData() {
    return array(
      "groups"  => $this->_timeline_groups,
      "items"   => $this->_timeline_items,
      "options" => $this->_timeline_options,
    );
  }
}
