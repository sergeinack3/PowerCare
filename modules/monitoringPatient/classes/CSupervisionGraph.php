<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\MonitoringPatient;

use Ox\Core\CMbArray;
use Ox\Core\CMbMath;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;

/**
 * A supervision graph
 */
class CSupervisionGraph extends CSupervisionTimedEntity {
    public const PROTOCOL_MD_STREAM = "MD-Stream";

  public $supervision_graph_id;

  public $height;
  public $display_legend;
  public $automatic_protocol;

  /** @var CSupervisionGraphAxis[] */
  public $_ref_axes;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "supervision_graph";
    $spec->key   = "supervision_graph_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                       = parent::getProps();
    $props["owner_id"]          .= " back|supervision_graphs";
    $props["height"]             = "num notNull min|20 default|200";
    $props["display_legend"]     = "bool notNull default|1";
    $props["automatic_protocol"] = "enum list|Kheops-Concentrator|MD-Stream";

    return $props;
  }

  /**
   * Load axes
   *
   * @param array $where Optional conditions
   *
   * @return CSupervisionGraphAxis[]
   */
  function loadRefsAxes($where = array()) {
    return $this->_ref_axes = $this->loadBackRefs("axes", null, null, null, null, null, "", $where);
  }

  /**
   * Get minimal value from a results set
   *
   * @param array $results The results as array of two values
   *
   * @return float
   */
  static protected function _getMin($results) {
    return min(CMbArray::pluck($results, 1));
  }

  /**
   * Get maximal value from a results set
   *
   * @param array $results The results as array of two values
   *
   * @return float
   */
  static protected function _getMax($results) {
    return max(CMbArray::pluck($results, 1));
  }

  /**
   * Build the graph data to be used by jQuery Flot
   *
   * @param array  $results  Results
   * @param string $time_min Minimal datetime
   * @param string $time_max Maximal datetime
   *
   * @return array
   */
  function buildGraph($results, $time_min, $time_max) {
    $graph = array(
      "yaxes"  => array(),
      "xaxes"  => array(array(
        "mode"         => "time",
        "timezone"     => "browser",
        "position"     => "bottom",
        "min"          => $time_min,
        "max"          => $time_max,
        "ticks"        => 30, // FIXME
        "labelWidth"   => 0.1,
        "reserveSpace" => false,
        //"minTickSize" => array(10, "minute")
      )),
      "series" => array(),
      "title"  => $this->title,
      "scale"  => null,
    );

    $_axes = $this->loadRefsAxes();

    $yaxis_i = 1;

    foreach ($_axes as $_axis) {
      $graph_yaxis = $_axis->getAxisForFlot(count($graph["yaxes"]));

      $_series = $_axis->loadRefsSeries();

      // Remove disabled axis
      if (!$_axis->actif) {
        $can_unset = true;
        foreach (CMbArray::pluck($_series, "value_type_id") as $_value_type_id) {
          if (isset($results[$_value_type_id])) {
            $can_unset = false;
            break;
          }
        }

        if ($can_unset) {
          unset($_axes[$_axis->_id]);
          continue;
        }
      }

      $_min = null;
      $_max = null;

      if ($_axis->display === "bandwidth") {
        // first series is the base
        $first_data   = null;
        $_value_types = array();
        $_value_units = array();

        foreach ($_series as $_serie) {

          $_series_data = $_serie->initSeriesData($yaxis_i);

          $_value_types[] = $_serie->value_type_id;
          $_value_units[] = $_serie->value_unit_id;

          $_unit_id = $_serie->value_unit_id ?: "none";

          if ($_serie->integer_values) {
            $graph_yaxis["tickDecimals"] = 0;
          }

          if (!$graph_yaxis["color"]) {
            $graph_yaxis["color"] = "#$_serie->color";
          }

          $_serie_data = array();
          if (isset($results[$_serie->value_type_id][$_unit_id])) {
            $_serie_data = $results[$_serie->value_type_id][$_unit_id];
          }

          if (!$first_data) {
            $first_data              = $_series_data;
            $first_data['bandwidth'] = array(
              'show' => true,
            );
            $first_data["label"]     = $_axis->title;
            $first_data["axis_id"]   = $_axis->_id;
            $first_data["data"]      = $_serie_data;

            $_data = $first_data["data"];
          }
          else {
            $new_data = $_serie_data;
            foreach ($new_data as $_i => $_d) {
              $first_data["data"][$_i][] = $_d[1];
            }

            $_data = $new_data;
          }

          $_values = array($_min, $graph_yaxis["min"]);
          if (!empty($_data)) {
            $_values[] = self::_getMin($_data);
          }
          $_min               = CMbMath::min($_values);
          $graph_yaxis["min"] = $_min;

          $_values = array($_max, $graph_yaxis["max"]);
          if (!empty($_data)) {
            $_values[] = self::_getMax($_data);
          }
          $_max               = CMbMath::max($_values);
          $graph_yaxis["max"] = $_max;
        }

        if ($first_data) {
          $first_data["key"] = implode("/", $_value_types) . "-" . implode("/", $_value_units);
          $graph["series"][] = $first_data;
        }
      }
      else {
        foreach ($_series as $_serie) {
          $_series_data = $_serie->initSeriesData($yaxis_i);
          $_unit_id     = ($_serie->value_unit_id ? $_serie->value_unit_id : "none");

          $_series_data["key"] = "$_serie->value_type_id-$_serie->value_unit_id";

          if ($_serie->integer_values) {
            $graph_yaxis["tickDecimals"] = 0;
          }

          if (!$graph_yaxis["color"]) {
            $graph_yaxis["color"] = "#$_serie->color";
          }

          if (!isset($results[$_serie->value_type_id][$_unit_id])) {
            $_series_data["data"] = array();
            $graph["series"][]    = $_series_data;

            $_values            = array($_min, $graph_yaxis["min"]);
            $_min               = CMbMath::min($_values);
            $graph_yaxis["min"] = $_min;

            $_values            = array($_max, $graph_yaxis["max"]);
            $_max               = CMbMath::max($_values);
            $graph_yaxis["max"] = $_max;

            continue;
          }

          $_series_data["data"] = $results[$_serie->value_type_id][$_unit_id];

          $_data = $_series_data["data"];

          $_values = array($_min, $graph_yaxis["min"]);
          if (!empty($_data)) {
            $_values[] = self::_getMin($_data);
          }
          $_min               = CMbMath::min($_values);
          $graph_yaxis["min"] = $_min;

          $_values = array($_max, $graph_yaxis["max"]);
          if (!empty($_data)) {
            $_values[] = self::_getMax($_data);
          }
          $_max               = CMbMath::max($_values);
          $graph_yaxis["max"] = $_max;

          $graph["series"][] = $_series_data;
        }
      }

      foreach ($_series as $_serie) {
        if ($_serie->display_ratio_value && $_serie->display_ratio_time) {
          $graph["scale"] = array(
            "value"       => (float)$_serie->display_ratio_value,
            "time"        => (float)$_serie->display_ratio_time,
            "yaxis_range" => $_max - $_min,
          );
        }
      }

      $graph["yaxes"][] = $graph_yaxis;

      $yaxis_i++;
    }

    return $this->_graph_data = $graph;
  }

  function getTypeUnitList() {
    $list = array();

    $_axes = $this->loadRefsAxes();

    foreach ($_axes as $_axis) {
      $_series = $_axis->loadRefsSeries();

      foreach ($_series as $_serie) {
        $_unit_id = ($_serie->value_unit_id ? $_serie->value_unit_id : "none");

        $list["$_serie->value_type_id-$_unit_id"] = array(
          "type" => $_serie->value_type_id,
          "unit" => $_unit_id,
        );
      }
    }

    return $list;
  }

  /**
   * Get all the graphs from an object
   *
   * @param CMbObject $object The object to get the graphs from
   *
   * @return self[]
   */
  static function getAllFor(CMbObject $object) {
    $graph = new self;

    $where = array(
      "owner_class" => "= '$object->_class'",
      "owner_id"    => "= '$object->_id'",
    );

    return $graph->loadList($where, "title");
  }

  /**
   * Ajoute les données des graphiques de supervision
   *
   * @param CTemplateManager $template The template manager
   * @param CMbObject        $object   The host object
   * @param string           $name     The field name
   *
   * @return void
   */
  static function addObservationDataToTemplate(CTemplateManager $template, CMbObject $object, $name) {
    $prefix = "Supervision";

    $group_id = CGroups::loadCurrent()->_id;

    $results = array();
    $times   = array();
    if ($object->_id) {
      [$results, $times] = SupervisionGraph::getResultsFor($object);
      if (count($times)) {
        $times = array_combine($times, $times);
      }
    }

    // CSupervisionGraphAxis
    $axis = new CSupervisionGraphAxis();
    $ds   = $axis->getDS();

    $where = array(
      "supervision_graph_axis.in_doc_template" => "= '1'",
      "supervision_graph.owner_class"          => "= 'CGroups'",
      "supervision_graph.owner_id"             => $ds->prepare("= ?", $group_id),
    );
    $ljoin = array(
      "supervision_graph" => "supervision_graph.supervision_graph_id = supervision_graph_axis.supervision_graph_id",
    );
    $order = array(
      "supervision_graph.title",
      "supervision_graph_axis.title",
    );

    /** @var CSupervisionGraphAxis[] $axes */
    $axes = $axis->loadList($where, $order, null, null, $ljoin);

    if (is_array($axes)) {
      CStoredObject::massLoadFwdRef($axes, "supervision_graph_id", null, true);

      foreach ($axes as $_axis) {
        $_graph  = $_axis->loadRefGraph();
        $_series = $_axis->loadRefsSeries();
        $_axis->loadRefsLabels();

        $_data = array_fill_keys($times, array());

        foreach ($_series as $_serie) {
          $_unit_id    = $_serie->value_unit_id ?: "none";
          $_unit_label = $_serie->loadRefValueUnit();

          if (!isset($results[$_serie->value_type_id][$_unit_id])) {
            continue;
          }

          foreach ($results[$_serie->value_type_id][$_unit_id] as $_value) {
            foreach ($times as $_time) {
              if ($_value["datetime"] != $_time) {
                continue;
              }

              $_value["unit"]                = $_unit_label->label;
              $_data["$_time"][$_serie->_id] = $_value;
              break;
            }
          }
        }

        $view = "";

        if (count($_data)) {
          $smarty = new CSmartyDP("modules/dPsalleOp");
          $smarty->assign("data", $_data);
          $smarty->assign("series", $_series);
          $smarty->assign("times", $times);
          $view = $smarty->fetch("inc_print_observation_result_set.tpl", '', '', 0);
          $view = preg_replace('`([\\n\\r])`', '', $view);
        }

        $template->addProperty("$name - $prefix - $_graph->title - $_axis->title", trim($view), "", false);
      }

      // CSupervisionTimedPicture
      // CSupervisionTimedData
      $data = array(
        "CSupervisionTimedPicture",
        "CSupervisionTimedData",
      );

      foreach ($data as $_class) {
        /** @var CSupervisionTimedPicture|CSupervisionTimedData $_object */
        $_object = new $_class();
        $_table  = $_object->_spec->table;
        $_ds     = $_object->getDS();

        $where = array(
          "$_table.in_doc_template" => "= '1'",
          "$_table.owner_class"     => "= 'CGroups'",
          "$_table.owner_id"        => $_ds->prepare("= ?", $group_id),
        );
        $order = "title";

        /** @var CSupervisionTimedPicture[]|CSupervisionTimedData[] $_objects */
        $_objects = $_object->loadList($where, $order);

        foreach ($_objects as $_timed) {
          $_data = array_fill_keys($times, null);

          if (!isset($results[$_timed->value_type_id])) {
            $template->addProperty("$name - $prefix - $_timed->title", "", "", false);
            continue;
          }

          foreach ($results[$_timed->value_type_id]["none"] as $_value) {
            foreach ($times as $_time) {
              if ($_value["datetime"] != $_time) {
                continue;
              }

              if ($_value["file_id"]) {
                $_file = new CFile();
                $_file->load($_value["file_id"]);

                $_value["datauri"] = $_file->getDataURI();
                $_value["file"]    = $_file;
              }

              $_data["$_time"] = $_value;
              break;
            }
          }

          $view = "";

          if (count($_data)) {
            $smarty = new CSmartyDP("modules/dPsalleOp");
            $smarty->assign("data", $_data);
            $smarty->assign("times", $times);
            $smarty->assign("timed_data", true);
            $view = $smarty->fetch("inc_print_observation_result_set.tpl", '', '', 0);
            $view = preg_replace('`([\\n\\r])`', '', $view);
          }

          $template->addProperty("$name - $prefix - $_timed->title", trim($view), "", false);
        }
      }
    }
  }
}
