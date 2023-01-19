<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Api;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Patients\Constants\CAbstractConstant;
use Ox\Mediboard\Patients\Constants\CConstantException;
use Ox\Mediboard\Patients\Constants\CConstantReleve;
use Ox\Mediboard\Patients\Constants\CConstantSpec;
use Ox\Mediboard\Patients\Constants\CDateTimeInterval;
use Ox\Mediboard\Patients\Constants\CStateInterval;
use Ox\Mediboard\Patients\Constants\CValueInt;
use Ox\Mediboard\Patients\CPatient;


/**
 * Description
 */
class CGraphConstant implements IShortNameAutoloadable {

  const GRAPH_PER_DAYS = "DAYS";
  const GRAPH_PER_MONTH = "MONTH";
  const GRAPH_PER_WEEKS = "WEEKS";
  const GRAPH_ALL = "ALL";
  const GRAPH_DASHBOARD = "DASHBOARD";
  const GRAPH_MIN = "MIN";
  const GRAPH_RESUME = "RESUME";

  public $multi_sources = false;
  public $multi_data = false;
  public $since_days;
  public $dt_min;
  public $dt_max;
  public $source = null;
  /** @var CUserAPI */
  public $user_api;
  /** @var CUserAPI[] */
  public $users_apis;

  /** @var CConstantSpec $_spec */
  public $_spec;
  public $_spec_code;
  public $_choice_name_source;
  public $_context;
  public $_no_data;
  public $_data_lenght;
  public $_sleep_state_index;
  public $_params;
  public $_params_graph;
  public $_params_options;
  public $_xaxis_categorie = false;
  public $_keep_category_order_value = false;
  public $_no_data_count = 0;
  public $_add_curve_bezier = false;
  public $_value_state_sleep;
  public $_wakeup_time = 0;


  /**
   * Get object graph for specific sources
   *
   * @param string $source source of api object
   *
   * @return CGraphConstantFitbit|CGraphConstantWithings|CGraphConstant
   */
  public static function getGraphConstructor($source) {
    switch ($source) {
      case "CFitbitAPI":
        return new CGraphConstantFitbit();
      case "CWithingsAPI":
        return new CGraphConstantWithings();
      default:
        return new CGraphConstant();
    }
  }

  /**
   * Get filters for constant spec
   *
   * @param string $constant_code constant spec
   * @param string $filter_active filter active
   *
   * @return array
   */
  public static function getFilterForConstant($constant_code, $filter_active = null) {
    $filters = array();
    switch ($constant_code) {
      case "weight":
        $filters = array("context" => "all", "filters" => "days weeks", "show" => false);
        break;

      case CAPITiers::REQUEST_ACTIVITY:
      case CAPITiers::REQUEST_SLEEP:
      case "hourlyactivity":
      case "hourlysleep":
      case "heartrate":
      case "dailysleep":
      case "dailyactivity":
        $filters = array("filters" => "days weeks", "show" => true);
        break;

      case "temperature":
        $filters = array("filters" => "weeks", "show" => false);
      default:
    }
    return $filters;
  }

  /**
   * Get sources for graphs
   *
   * @param CPatient $patient if patient is specified, return api synchronized
   *
   * @return array
   */
  public static function getSources($patient = null) {
    $apis    = CAPITiers::getAPIList();
    $sources = array();
    foreach ($apis as $_api_name) {
      if ($patient) {
        if (!CPatientUserAPI::checkPatientNotSynchronized($patient, $_api_name)) {
          $sources[] = $_api_name;
        }
      }
      else {
        $sources[] = $_api_name;
      }
    }
    $sources[] = "self";

    return $sources;
  }


  /**
   * Generate resume chart for sleep
   *
   * @param int               $patient_id       patient_id
   * @param CAbstractConstant $constants_daily  object value for one day
   * @param int               $api_id           source api id
   * @param array             $constant_sources source constant
   *
   * @return array
   */
  public function generateResumeSleep($patient_id, $constants_daily, $api_id, $constant_sources) {
    $resume_days = CConstantSpec::getSpecsByCodes(["wakeupduration", "lightsleepduration", "deepsleepduration", "remduration"]);
    $resume      = array();
    foreach ($resume_days as $_spec) {
     // if ($value = CConstantReleve::getConstantValueObject(
     //   $patient_id, $_spec->code, $constants_daily->datetime, $api_id, $constant_sources
     // )
     // ) {
     //   $resume[$value->getCodeId()] = $value;
     // }
    }
    $resume["dailysleep"] = $constants_daily;

    return $this->generateGraphResume("sleep", CGraphConstant::GRAPH_RESUME, $resume);
  }

  /**
   * Generate graph with multiple specs
   *
   * @param String              $scope     scope for treat data
   * @param String              $context   context of graph in constant in this graph
   * @param CAbstractConstant[] $constants values constants
   *
   * @return array
   */
  public function generateGraphResume($scope, $context, $constants) {
    $this->_sleep_state_index = array();
    $this->_params            = array();
    $this->_context           = $context;
    $graph                    = array();
    $series                   = $this->treatSpecificResume($graph, $scope, $constants);

    $graph["options"] = $this->getOptions();
    $graph["series"]  = $series;
    $graph["source"]  = $this->source;

    return $graph;
  }

  /**
   * Generate graph with multiple specs
   *
   * @param array               $graph     Graph
   * @param String              $scope     scope of treat data
   * @param CAbstractConstant[] $constants values constants
   *
   * @return array
   * @throws CConstantException
   */
  public function treatSpecificResume(&$graph, $scope, $constants) {
    $series = array();
    switch ($scope) {
      case "sleep":
        $dailysleep                       = CMbArray::extract($constants, "dailysleep");
        $this->_params["series_length"][] = 1;
        $this->_spec_code                 = "dailysleep";
        $this->specificData($dailysleep);
        $this->addSpecificOptions($graph);
        foreach ($constants as $_codeId => $_constant) {
          $spec        = $_constant->getRefSpec();
          $this->_spec = $spec;
          $value       = self::parseConstant($_constant);
          if ($value || $value === 0) {
            $series[] = array(
              "data" => array($value),
              "name" => CAppUI::tr($spec->code)
            );
          }
          $this->_sleep_state_index[CAPITiers::getStateFromSpec($spec->code)] = $spec->code;
        }
        $this->_spec_code = "sleep";
        break;

      case "sleep_week":
        $this->_spec_code = "dailysleep";
        $this->addSpecificOptions($graph);
        $timestamps = array();
        foreach ($constants as $_codeId => $_constant) {
          $spec        = $_constant->getRefSpec();
          $this->_spec = $spec;
          $value       = self::parseConstant($_constant);
          if ($value || $value === 0) {
            $day                              = CMbDT::daysIs($_constant->datetime);
            $series[$this->_spec->code][$day] = $value;
            $timestamps[$day]                 = CMbDT::format($_constant->datetime, CMbDT::TIMESTAMPS);
          }
          $this->_sleep_state_index[CAPITiers::getStateFromSpec($spec->code)] = $spec->code;
        }
        $days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', "Sunday");
        foreach ($series as $_spec_code => $_values) {
          $values = array();
          foreach ($days as $_day) {
            $values[]                     = CMbArray::get($_values, $_day);
            $this->_params["dt_series"][] = CMbArray::get($timestamps, $_day);
          }
          $series[] = array(
            "data" => $values,
            "name" => CAppUI::tr($_spec_code)
          );
          unset($series[$_spec_code]);
        }
        break;

      case "activity":
      case "activity_week":
        if (!$this->user_api || $this->user_api->obj_steps == 0 || count($constants) === 0) {
          $this->_no_data   = true;
          $graph["no_data"] = true;

          return $series;
        }
        $this->_spec_code = "dailyactivity";

        $value = 0;
        /** @var CValueInt $_constant */
        foreach ($constants as $_constant) {
          $value += $_constant->getValue();
        }
        $obj_steps = $scope === "activity_week" ? $this->user_api->obj_steps * 7 : $this->user_api->obj_steps;
        $series[]  = intval(($value / $obj_steps) * 100);
        break;
      default:
    }

    return $series;
  }

  /**
   * Treatment for specific data based on CAbstractConstant
   *
   * @param CAbstractConstant $constant Constant
   *
   * @return void
   * @throws CConstantException
   */
  private function specificData($constant) {
    // aggregate && dt_goal_achieved && cumul_sleep
    $this->addIntervalleValues($constant);
    $spec_code    = $constant->_ref_spec->code;
    $index_series = CMbArray::get($this->_params, "index_series", 0);
    try {
      switch ($spec_code) {
        case "hourlyactivity":
        case "dailyactivity":
          $aggregate = CMbArray::getRecursive($this->_params, "aggregate $index_series", 0);
          if ($this->user_api && $this->user_api->obj_steps && !CMbArray::get($this->_params, "dt_goal_achieved")) {
            if (($aggregate + $constant->getValue()) >= $this->user_api->obj_steps) {
              $this->_params["dt_goal_achieved"]    = CMbDT::format($constant->datetime, CMbDT::TIMESTAMPS);
              $this->_params["index_goal_achieved"] = CMbArray::get($this->_params, "index_value");
            }
          }
        //no break;
        case "heartrate":
        case "dailyheartrate":

          $aggregate                                 = CMbArray::getRecursive($this->_params, "aggregate $index_series", 0);
          $this->_params["aggregate"][$index_series] = $aggregate + $constant->getValue();
          break;

        case "dailysleep":
          $value                                         = $constant->getValue();
          $this->_params["sleep_value"][$index_series][] = $value;
          break;

        default:
      }
    }
    catch (CConstantException $cce) {
      throw $cce;
    }
  }

  /**
   * Add max value of series on options graph
   *
   * @param CAbstractConstant $constant constant value
   *
   * @return void
   * @throws CConstantException
   */
  private function addIntervalleValues($constant) {
    // value max_value && min_value && index_min_value && index_max_value

    $value_max = CMbArray::get($this->_params, "max_value");
    $value_min = CMbArray::get($this->_params, "min_value");
    switch ($constant->_ref_spec->value_class) {
      case "CValueInt":
        if (!$value_max || $constant->getValue() > $value_max) {
          $this->_params["max_value"]       = $constant->getValue();
          $this->_params["index_max_value"] = CMbArray::get($this->_params, "index_value");
        }
        if (!$value_min || $constant->getValue() < $value_min) {
          $this->_params["min_value"]       = $constant->getValue();
          $this->_params["index_min_value"] = CMbArray::get($this->_params, "index_value");
        }
        break;
      default:
    }
  }

  /**
   * Add specific options on graphs
   *
   * @param array $graph data which represent graph
   *
   * @return void
   */
  private function addSpecificOptions(&$graph) {
    $aggregate = CMbArray::get($this->_params, "aggregate");
    if ($aggregate && count($aggregate) > 0) {
      $graph["aggregate"] = $aggregate;
      foreach (CMbArray::get($graph, "aggregate") as $_index => $_agg) {
        $graph["average"][] = intval($_agg / CMbArray::getRecursive($this->_params, "series_length $_index", 1));
      }
    }

    if ($this->_spec_code === "dailysleep") {
      foreach (CMbArray::get($this->_params, "sleep_value", array()) as $_index => $_data) {
        $sleep_data = self::calculateSleepData($_data, $this->_wakeup_time);
        foreach ($sleep_data as $_key => $_val) {
          $graph[$_key][$_index] = $_val;
        }
      }
    }

    switch ($this->_context) {
      case self::GRAPH_MIN:
        $graph["title"] = CAppUI::tr("CGraphConstant-title-" . $this->_spec_code);
        break;

      case self::GRAPH_PER_DAYS;
        $comp           = $this->formatDateLocal($this->dt_min);
        $graph["title"] = CAppUI::tr("CGraphConstant-title-day " . $this->_spec_code . " of", $comp);
        break;

      case self::GRAPH_PER_WEEKS:
        $graph["title"] = CAppUI::tr(
          "CGraphConstant-title-week " . $this->_spec_code . " of",
          array(CMbDT::format($this->dt_min, "%d/%m/%Y"), CMbDT::format($this->dt_max, "%d/%m/%Y"))
        );
        break;

      case self::GRAPH_DASHBOARD:
        $graph["title"] = CAppUI::tr("CGraphConstant-title-dashboard " . $this->_spec_code . " from monday to sunday");
        break;

      case self::GRAPH_ALL:
        $graph['title'] = CAppUI::tr("CGraphConstant-title-all", CAppUI::tr($this->_spec_code));
        break;
      default:
    }
  }

  public static function calculateSleepData($constants, $wakeup_time = 0) {
    $sleep_before = array();
    $sleep_after  = array();
    $cumul_sleep  = 0;
    foreach ($constants as $_value) {
      $min_value   = is_array($_value) ? CMbArray::get($_value, "min_value") : $_value->min_value;
      $max_value   = is_array($_value) ? CMbArray::get($_value, "max_value") : $_value->max_value;
      $cumul_sleep += CMbDT::durationSecond($min_value, $max_value);
      if (($min_time = self::getTimeToSecond($min_value)) < 43200) {
        $min                 = CMbArray::get($sleep_before, "min", 0);
        $sleep_before["min"] = $min === 0 ? $min_time : intval(($min + $min_time) / 2);
      }
      else {
        $min                = CMbArray::get($sleep_after, "min", 0);
        $sleep_after["min"] = $min === 0 ? $min_time : intval(($min + $min_time) / 2);
      }

      if (($max_time = self::getTimeToSecond($max_value)) < 43200) {
        $max                 = CMbArray::get($sleep_before, "max", 0);
        $sleep_before["max"] = $max === 0 ? $max_time : intval(($max + $max_time) / 2);
      }
      else {
        $max                = CMbArray::get($sleep_after, "max", 0);
        $sleep_after["max"] = $max === 0 ? $max_time : intval(($max + $max_time) / 2);
      }
    }

    $avg_aft_min = CMbArray::get($sleep_after, "min");
    $avg_bef_min = CMbArray::get($sleep_before, "min");
    if ($avg_aft_min && $avg_bef_min) {
      $avg_min = intval(((86400 + $avg_bef_min) + $avg_aft_min) / 2) % 86400;
    }
    else {
      $avg_min = $avg_bef_min ? $avg_bef_min : $avg_aft_min;
    }
    $avg_aft_max = CMbArray::get($sleep_after, "max");
    $avg_bef_max = CMbArray::get($sleep_before, "max");
    if ($avg_bef_max && $avg_aft_max) {
      $avg_max = intval(((86400 - $avg_aft_max) + $avg_bef_max) / 2);
    }
    else {
      $avg_max = $avg_bef_max ? $avg_bef_max : $avg_aft_max;
    }

    return array(
      "duration_sleep" => self::secondToTimes(intval($cumul_sleep / count($constants) - ($wakeup_time / 1000)), true),
      "sleep_wakeup"   => self::secondToTimes($avg_max),
      "sleep_bedtime"  => self::secondToTimes($avg_min)
    );
  }

  /**
   * Transform time to second
   *
   * @param string $datetime datetime
   *
   * @return int
   */
  private static function getTimeToSecond($datetime) {
    $time    = explode(":", CMbArray::get(explode(" ", $datetime), 1));
    $hours   = intval(CMbArray::get($time, 0) * 3600);
    $minutes = intval(CMbArray::get($time, 1) * 60);

    return $hours + $minutes + intval(CMbArray::get($time, 2));
  }

  /**
   * Transform second to time
   *
   * @param int  $second     seconds
   * @param bool $isDuration duration
   *
   * @return string
   */
  private static function secondToTimes($second, $isDuration = false) {
    $hours   = intval($second / 3600);
    $minutes = intval(($second - ($hours * 3600)) / 60);
    $hours   = $hours > 9 ? $hours : +"0" + $hours;
    $minutes = $minutes < 10 ? "0" . $minutes : $minutes;

    return ($hours > 0 || !$isDuration) ? $hours . "h" . $minutes : $minutes . "min";
  }

  /**
   * Create format date
   *
   * @param string $datetime datetime
   * @param string $format   format
   *
   * @return string
   */
  private function formatDateLocal($datetime, $format = "dd d m y") {
    $exp_dt = explode("-", CMbArray::get(explode(" ", $datetime), 0));
    $local  = array(
      "dd" => CMBString::lower(CAppUI::tr(CMbDT::daysIs($datetime))),
      "d"  => CMbArray::get($exp_dt, 2),
      "m"  => CMBString::lower(CAppUI::tr("CGraphConstant-" . intval(CMbArray::get($exp_dt, 1)) . "-month")),
      "y"  => CMbArray::get($exp_dt, 0)
    );

    $result  = "";
    $explode = explode(" ", $format);
    foreach ($explode as $_format) {
      $result .= $result ? CMbArray::get($local, $_format) . " " : CMbArray::get($local, $_format) . " ";
    }

    return $result;
  }

  /**
   * Parse constants values
   *
   * @param CAbstractConstant $constant constant to parse
   *
   * @return mixed
   * @throws CConstantException
   */
  public function parseConstant($constant) {
    $spec  = $constant->getRefSpec();
    $value = $constant->getValue();
    if ($value || $value === "0") {
      switch ($spec->code) {
        case "dailysleep":
          /** @var CDateTimeInterval $constant */
          $dt       = CMbDT::toTimestamp(CMbDT::format($constant->datetime, CMbDT::ISO_DATE));
          $duration = CMbDT::durationSecond(CMbArray::get($value, "min_value"), CMbArray::get($value, "max_value"));
          if ($this->_xaxis_categorie) {
            return array("day" => CMbDT::daysIs($constant->datetime), "value" => $duration, "timestamps" => $dt);
          }

          return array(
            "x" => $dt, "y" => $duration
          );

        case "weight":
        case "temperature":
        case "heartrate":
        case "dailyheartrate":
        case "hourlyactivity":
        case "dailyactivity":
          $timestamps = CMbDT::format($constant->datetime, CMbDT::TIMESTAMPS);
          if ($this->_xaxis_categorie) {
            return array("day" => CMbDT::daysIs($constant->datetime), "value" => $value, "timestamps" => $timestamps);
          }

          return array($timestamps, $value);
        case "hourlysleep":
          /** @var CStateInterval $constant */
          $duration        = CMbDT::durationSecond($constant->min_value, $constant->max_value) / 60;
          $duration_parsed = intval($duration);
          // 1 minute de décallage a cause de l'arroundi
          if ($duration - $duration_parsed > 0.5) {
            $duration_parsed += 1;
          }
          $dt_min = CMbDT::format($constant->datetime, CMbDT::TIMESTAMPS);
          $dt_max = CMbDT::transform("+$duration_parsed MINUTES", $constant->datetime, CMbDT::TIMESTAMPS);

          $this->_params["states_sleep"][$constant->state] = $constant->state;

          return array(
            "min_value" => $dt_min,
            "max_value" => $dt_max,
            "state"     => $constant->state
          );

        case "remduration":
        case "lightsleepduration":
        case "deepsleepduration":
        case "wakeupduration":
          return $value;
        default:
          return null;
      }
    }

    return null;
  }

  /**
   * Get options for graph
   *
   * @return array
   */
  public function getOptions() {
    switch ($this->_context) {
      case self::GRAPH_MIN:
        $options["chart_height"]          = 240;
        $options["xaxis_tooltip_enabled"] = false;
        break;

      case self::GRAPH_PER_DAYS:
        $options["chart_height"] = 240;
        break;
      case self::GRAPH_DASHBOARD:
      case self::GRAPH_PER_WEEKS:
        $options["chart_height"]          = 240;
        $options["xaxis_type"]            = "category";
        $options["xaxis_tickPlacement"]   = "on";
        $options["xaxis_tooltip_enabled"] = false;
        $options["xaxis_categories"]      = $this->getDaysAsCategory();
        $options["pltop_bar_columnWidth"] = "10px";
        $options["pltop_bar_endingShape"] = "rounded";
        break;
      default:
    }

    $options["spec_code"] = $this->_spec_code;
    if ($this->user_api) {
      if ($this->user_api->obj_steps > 0) {
        $options["goal_step"] = $this->user_api->obj_steps;
      }

      if ($this->user_api->obj_weight > 0) {
        $options["goal_weight"] = $this->user_api->obj_weight;
      }
    }

    if (CMbArray::get($this->_params, "curve_bezier_on")) {
      $options["curve_bezier_on"] = true;
    }

    if ($value_max = CMbArray::get($this->_params, "max_value")) {
      $options["max_value"] = $value_max;
    }

    if ($value_min = CMbArray::get($this->_params, "min_value")) {
      $options["min_value"] = $value_min;
    }
    if ($dt_goal_achived = CMbArray::get($this->_params, "dt_goal_achieved")) {
      $options["dt_goal_achieved"] = $dt_goal_achived;
    }
    if (($index_goal_achieved = CMbArray::get($this->_params, "index_goal_achieved")) !== null) {
      $options["index_goal_achieved"] = $index_goal_achieved;
    }

    if ($dt_series = CMbArray::get($this->_params, "dt_series")) {
      $options["dt_series"] = $dt_series;
    }

    if (($fix_min = CMbArray::get($this->_params, "fix_min")) !== null) {
      $options["yaxis_min"] = $fix_min;
    }

    if ($series_length = CMbArray::get($this->_params, "series_length")) {
      $options["series_length"] = $series_length;
    }

    self::getSpecificOptions($this->_spec_code, $options);

    if ($this->_no_data) {
      if ($this->since_days) {
        $options["nodata_text"] = CAppUI::tr("CAbstractConstant-msg-none since %s days", array($this->since_days));
      }
      else {
        $options["nodata_text"] = CAppUI::tr("CAbstractConstant-msg-none show");
      }
      $options["chart_height"]     = "100";
      $options["yaxis_label_show"] = false;
      $options["xaxis_label_show"] = false;
      $options["grid_show"]        = false;
    }

    return $options;
  }

  /**
   * Get days with court trad
   *
   * @return array
   */
  public function getDaysAsCategory() {
    $days   = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', "Sunday");
    $result = array();
    if ($this->_keep_category_order_value) {
      foreach ($this->_params["dt_series"] as $_dt) {
        $day      = CMbDT::daysIs($_dt, true);
        $result[] = CAppUI::tr("CGraphConstant-$day-court");
      }
    }
    else {
      foreach ($days as $_day) {
        $result[] = CAppUI::tr("CGraphConstant-$_day-court");
      }
    }

    return $result;
  }

  /**
   * Get options for graphs
   *
   * @param string $constant_name constant code
   * @param array  $options       options for graphs
   *
   * @return void
   */
  public function getSpecificOptions($constant_name, &$options) {
    self::_getSpecificOptions($this->_context . "|" . $constant_name, $options);
  }

  /**
   * Generate options specific at context and constant code
   *
   * @param String $ctx_cst_name context concatenated to constant code
   * @param array  $options      options for graphs
   *
   * @return void
   */
  private function _getSpecificOptions($ctx_cst_name, &$options) {
    switch ($ctx_cst_name) {
      case self::GRAPH_MIN . "|dailyactivity":
        $options["chart_type"]            = "bar";
        $options["tooltip_shared"]        = true;
        $options["tooltip_unit_label"]    = CAppUI::tr("CConstantSpec.unit.step");
        $options["pltop_bar_columnWidth"] = "0";
        $options["yaxis_title"]           = CAppUI::tr("CAbstractConstant-msg-unit step|pl");
        $options["xaxis_type"]            = "category";
        $options["xaxis_categories"]      = $this->getDaysAsCategory();
        if ($this->user_api && $this->user_api->obj_steps > 0) {
          $options["annotations_value"] = $this->user_api->obj_steps;
          $options["color_goal"]        = "#61a23d";
        }
        break;
      case self::GRAPH_MIN . "|dailysleep":
        $options["chart_type"]            = "bar";
        $options["tooltip_shared"]        = true;
        $options["pltop_bar_columnWidth"] = "0";
        $options["yaxis_title"]           = CAppUI::tr("CAbstractConstant-msg-unit hour");
        $options["xaxis_type"]            = "category";
        $options["xaxis_categories"]      = $this->getDaysAsCategory();
        break;

      case self::GRAPH_MIN . "|weight":
        $options["yaxis_title"] = CAppUI::tr("CAbstractConstant-msg-unit kilogram");
        $options["color_goal"]  = "#61a23d";
        break;

      case self::GRAPH_MIN . "|heartrate":
        $options["yaxis_title"]              = CAppUI::tr("CAbstractConstant-msg-unit heartrate");
        $options["tooltip_unit_label"]       = CAppUI::tr("CAbstractConstant-msg-unit heartrate");
        $options["stroke_width"]             = 2;
        $options["markers_size"]             = 0;
        $options["markers_hover_size"]       = 0;
        $options["legend_show"]              = false;
        $options["stroke_dasharray"]         = [0, 3];
        $options["chart_animations_enabled"] = false;
        break;

      case self::GRAPH_RESUME . "|dailyactivity":
        $options["chart_type"]                     = "radialBar";
        $options["chart_height"]                   = 150;
        $options["pltop_radial_hollow_size"]       = "62%";
        $options["pltop_radial_hollow_background"] = "#eeeeee";
        $options["pltop_radial_value_color"]       = "#000";
        $options["pltop_radial_hollow_margin"]     = "0";
        $options["pltop_radial_value_show"]        = true;
        $options["pltop_radial_fontSize"]          = "1.25rem";
        if ($this->user_api && $this->user_api->obj_steps) {
          $options["pltop_radial_value"] = $this->user_api->obj_steps;
        }
        $options["stroke_lineCap"]            = "round";
        $options["labels"]                    = [CAppUI::tr("CGraphConstant-title-goal")];
        $options["colors"]                    = ["#20EE47"];
        $options["legend_show"]               = true;
        $options["legend_position"]           = "top";
        $options["tooltip_enabled"]           = false;
        $options["legend_onitemclick_toggle"] = false;
        $options["legend_onitemHover_toggle"] = false;
        $options["legend_markers_size"]       = 0;
        $options["legend_show"]               = false;
        $options["fill_gradient"]             = true;
        break;

      case self::GRAPH_RESUME . "|sleep":
        $options["colors"]       = $this->getColors(null, "sleep");
        $options["chart_type"]   = "bar";
        $options["chart_height"] = 120;
        if (count(CMbArray::getRecursive($options, "colors")) > 4) {
          $options["chart_height"] = 125;
        }
        $options["responsive_chart_height"] = 100;
        $options["responsive_breakpoint"]   = 767;
        $options["responsive_legend_width"] = null;
        $options["chart_stacked"]           = true;
        $options["chart_stackType"]         = "100%";
        $options["yaxis_label_show"]        = false;
        $options["xaxis_label_show"]        = false;
        $options["xaxis_border_show"]       = false;
        $options["xaxis_ticks_show"]        = false;
        $options["pltop_bar_horizontal"]    = true;
        $options["dataLabels_enabled"]      = false;
        $options["grid_show"]               = false;
        $options["xaxis_type"]              = "category";
        $options["yaxis_forceNiceScale"]    = false;
        $options["legend_horizontAlign"]    = "left";
        $options["legend_width"]            = 200;
        $options["stroke_colors"]           = ["#fff"];
        $options["stroke_width"]            = "1";
        $options["tooltip_shared"]          = false;
        $options["date"]                    = CMbDT::format($this->dt_min, CMbDT::TIMESTAMPS);
        break;

      case self::GRAPH_PER_DAYS . "|hourlyactivity":
        $options["chart_type"]            = "bar";
        $options["tooltip_shared"]        = true;
        $options["legend_position"]       = "bottom";
        $options["xaxis_tooltip_enabled"] = false;
        $options["pltop_bar_columnWidth"] = "0%";
        $options["tooltip_unit_label"]    = CAppUI::tr("CConstantSpec.unit.step");
        $options["yaxis_title"]           = CAppUI::tr("CAbstractConstant-msg-unit step|pl");
        $options["color_goal"]            = "#61a23d";
        break;

      case self::GRAPH_PER_DAYS . "|hourlysleep":
        $options["chart_animations_enabled"] = false;
        $options["chart_type"]               = "line";
        $options["tooltip_shared"]           = false;
        //$options["yaxis_label_show"]         = false;
        //$options["stroke_show"]               = false;
        $options["xaxis_tooltip_enabled"]     = false;
        $options["yaxis_min"]                 = 1;
        $options["yaxis_max"]                 = 5.1;
        $options["yaxis_label_minWidth"]      = 55;
        $options["markers_size"]              = 0;
        $options["markers_hover_size"]        = 0;
        $options["dataLabels_enabled"]        = false;
        $options["legend_onitemclick_toggle"] = false;
        $options["legend_show"]               = false;
        $options["grid_yaxis_line_show"]      = false;
        $options["stroke_curve"]              = "smooth";
        $options["colors"]                    = $this->getColors();
        $options["labels_sleep"]              = $this->getLabelsSleep();
        for ($i = 0; $i < count($options["colors"]) - 1; $i++) {
          $options["stroke_width"][]     = 5;
          $options["stroke_dasharray"][] = 0;
        }
        $options["stroke_width"][]     = 2;
        $options["stroke_dasharray"][] = 2;
        break;

      case self::GRAPH_PER_DAYS . "|heartrate":
        $options["markers_size"]             = 0;
        $options["markers_hover_size"]       = 0;
        $options["legend_show"]              = false;
        $options["stroke_curve"]             = "smooth";
        $options["stroke_dasharray"]         = [0, 3];
        $options["chart_animations_enabled"] = false;
        $options["tooltip_unit_label"]       = CAppUI::tr("CAbstractConstant-msg-unit heartrate");
        $options["yaxis_title"]              = CAppUI::tr("CAbstractConstant-msg-unit heartrate");
        if (CMbArray::get($this->_params, "index_min_value") == CMbArray::get($this->_params, "index_last_value")) {
          $this->_params["fix_min"] = 0;
        }
        break;

      case self::GRAPH_PER_WEEKS . "|dailyactivity":
        $options["chart_type"]         = "bar";
        $options["tooltip_shared"]     = true;
        $options["tooltip_unit_label"] = CAppUI::tr("CConstantSpec.unit.step");
        if ($this->user_api && $this->user_api->obj_steps > 0) {
          $options["annotations_value"] = $this->user_api->obj_steps;
          $options["color_goal"]        = "#61a23d";
          $options["yaxis_title"]       = CAppUI::tr("CAbstractConstant-msg-unit step|pl");
        }
        break;

      case self::GRAPH_MIN . "|temperature":
      case self::GRAPH_PER_WEEKS . "|temperature":
        $options["chart_type"]              = "line";
        $options["tooltip_shared"]          = true;
        $options["yaxis_title"]             = CAppUI::tr("CConstantSpec.unit.°C");
        $options["tooltip_unit_label"]      = CAppUI::tr("CConstantSpec.unit.°C");
        $options["yaxis_min"]               = 35;
        $options["yaxis_max"]               = 42;
        $options["annotations_temperature"] = true;
        break;

      case self::GRAPH_PER_WEEKS . "|dailysleep":
        $options["chart_type"]               = "bar";
        $options["chart_stacked"]            = true;
        $options["chart_stackType"]          = "100%";
        $options["yaxis_label_show"]         = false;
        $options["colors"]                   = $this->getColors();
        $options["chart_animations_enabled"] = false;
        $options["pltop_bar_columnWidth"]    = "8%";
        $options["tooltip_shared"]           = true;
        break;

      case self::GRAPH_PER_WEEKS . "|dailyheartrate":
        $options["markers_size"]             = 0;
        $options["markers_hover_size"]       = 0;
        $options["chart_animations_enabled"] = false;
        $options["yaxis_title"]              = CAppUI::tr("CAbstractConstant-msg-unit heartrate");
        break;

      case self::GRAPH_DASHBOARD . "|dailysleep":
        $options["chart_height"]          = 250;
        $options["chart_type"]            = "bar";
        $options["xaxis_tooltip_enabled"] = false;
        $options["yaxis_label_show"]      = false;
        $options["tooltip_shared"]        = true;
        break;

      case self::GRAPH_DASHBOARD . "|dailyactivity":
        $options["chart_type"]         = "bar";
        $options["tooltip_shared"]     = true;
        $options["chart_height"]       = 250;
        $options["yaxis_label_show"]   = false;
        $options["color_goal"]         = "#61a23d";
        $options["tooltip_unit_label"] = CAppUI::tr("CConstantSpec.unit.step");
        break;

      case self::GRAPH_ALL . "|weight":
        $options["chart_type"]   = "line";
        $options["chart_height"] = 400;
        $options["stroke_curve"] = ["smooth", "straight"];
        if (CMbArray::get($this->_params, "curve_bezier_on")) {
          $options["stroke_width"]   = [4, 2];
          $options["markers_size"]   = [0, 3];
          $options["markers_colors"] = "#000";
        }
        else {
          $options["stroke_width"] = 2;
          $options["markers_size"] = 3;
        }
        $options["markers_hover_sizeOffset"] = 2;
        $options["colors"]                   = ["#008ffb", "#CECECE"];
        $options['chart_zoom_enabled']       = true;
        $options['toolbar_show']             = true;
        $options["yaxis_title"]              = CAppUI::tr("CAbstractConstant-msg-unit kilogram");
        if ($this->user_api && $this->user_api->obj_weight > 0) {
          $options["annotations_value"] = $this->user_api->obj_weight;
        }
        break;
      default:
    }
  }

  /**
   * Get colors for graph sleep
   *
   * @param string $context_alt alternative context
   * @param string $prop_alt    alternative props
   *
   * @return array
   */
  private function getColors($context_alt = null, $prop_alt = null) {
    $context = $context_alt ? $context_alt : $this->_context;
    $prop    = $prop_alt ? $prop_alt : $this->_spec_code;
    $colors  = array(
      self::GRAPH_RESUME    => array(
        "sleep" => $this->_colorForSleep()
      ),
      self::GRAPH_PER_DAYS  => array(
        "hourlysleep" => $this->_colorForSleep()
      ),
      self::GRAPH_MIN       => array(),
      self::GRAPH_ALL       => array(),
      self::GRAPH_PER_WEEKS => array(
        "dailysleep" => $this->_colorForSleep()
      ),
      self::GRAPH_DASHBOARD => array(),

    );


    return CMbArray::getRecursive($colors, "$context $prop");
  }

  /**
   * Return array with corresponding colors for differents sleep state
   *
   * @return array
   */
  private function _colorForSleep() {
    $colors_for_sleep = array(
      "wakeupduration"     => "#04ff99",
      "lightsleepduration" => "#0b93ff",
      "deepsleepduration"  => "#2140ff",
      "remduration"        => "#FFBE65",
    );
    $colors           = array();
    foreach ($this->_sleep_state_index as $_state => $_spec_code) {
      $colors[] = CMbArray::get($colors_for_sleep, "$_spec_code");
    }
    $colors[] = "#cecece"; // color continued curve sleep

    return $colors;
  }

  /**
   * Get labels for sleep
   *
   * @return array
   */
  private function getLabelsSleep() {
    $labels = array();
    foreach ($this->_sleep_state_index as $_state => $_spec_code) {
      $value        = CMbArray::get($this->_value_state_sleep, $_state);
      $key          = CMbArray::get(array_keys($value), 0);
      $labels[$key] = CMbArray::get($value, $key);
    }

    return $labels;
  }

  /**
   * Generate array for gauge Css
   *
   * @param float $percent percentage
   *
   * @return array
   */
  function calculateGaugeCss($percent) {
    $degree = ($percent * 180) / 0.5;

    if ($degree > 180) {
      if ($degree >= 360) {
        $color = "#009587";
      }
      else {
        $color = "#ef6c00";
      }

      $loading1    = 180;
      $loading2    = $degree - 180;
      $color_gauge = $color;
    }
    else {
      if ($degree <= 72) {
        $color = "red";
      }
      else {
        $color = "#ef6c00";
      }

      $loading1    = $degree;
      $loading2    = 0;
      $color_gauge = $color;
    }

    return array("progress" => intval($percent * 100), "loading1" => $loading1, "loading2" => $loading2, "color" => $color_gauge);
  }

  /**
   * Generate graph
   *
   * @param CConstantSpec|CConstantSpec[] $spec      specifications of constant
   * @param String                        $context   context of graph in constant in this graph
   * @param CAbstractConstant[]           $constants values constants
   *
   * @return array
   */
  public function generateGraph($spec, $context, $constants) {
    $this->_spec      = $spec;
    $this->_spec_code = $spec->code;
    $this->_context   = $context;

    $this->init();
    $graph          = array();
    $series         = array();
    $this->_no_data = count($constants) <= $this->_no_data_count ? true : false;
    if ($this->_no_data || !$this->hasValuesIn($constants)) {
      $graph["no_data"] = true;
    }
    else {
      $series = $this->generateSeries($constants);
      if ($this->_add_curve_bezier && CMbArray::getRecursive($this->_params, "series_length 0") > 10) {
        $this->_params["curve_bezier_on"] = true;
        $series                           = array(
          array(
            "data" => $this->courbe_bezier(CMbArray::getRecursive($series, "0 data"), 30),
            "name" => CAppUI::tr("CGraphConstant-title-bezier curve")
          ),
          CMbArray::get($series, 0)
        );
      }
    }

    $options          = $this->getOptions();
    $graph["source"]  = $this->source;
    $graph["series"]  = array_values($series);
    $graph["options"] = $options;
    $graph["context"] = $this->_context;
    $this->addSpecificOptions($graph);

    return $graph;
  }

  /**
   * Initialize options
   *
   * @return void
   */
  public function init() {
    $this->_params            = array();
    $this->_params_graph      = array();
    $this->_params_options    = array();
    $this->_sleep_state_index = array();
    $this->_xaxis_categorie   = false;
    $this->_wakeup_time       = 0;

    $this->initFromSpecCode();
    $this->initFromContext();
    $this->initFromGlobalContext();
  }

  /**
   * Initialize options in function of spec code
   *
   * @return void
   */
  public function initFromSpecCode() {
    switch ($this->_spec_code) {
      case "hourlyactivity":
      case "dailyactivity":
        $this->_params["specific_name"] = CAppUI::tr("CGraphConstant-title-dailyactivity");
        break;
      case "hourlysleep":
      case "dailysleep":
        $this->_params["specific_name"] = CAppUI::tr("CGraphConstant-title-dailysleep");
        break;
      case "dailyheartrate":
        $this->_params["specific_name"] = CAppUI::tr("heartrate");
        break;

      default:
    }
  }

  /**
   * Initialize options in function of context
   *
   * @return void
   */
  public function initFromContext() {
    switch ($this->_context) {
      case CGraphConstant::GRAPH_DASHBOARD:
        $this->_xaxis_categorie = true;
        break;
      case CGraphConstant::GRAPH_PER_WEEKS:
        $this->_xaxis_categorie = true;
        break;
      default:
    }
  }

  /**
   * Initialize options in function of spec code and context
   *
   * @return void
   */
  public function initFromGlobalContext() {
    switch ($this->_context . "|" . $this->_spec_code) {
      case CGraphConstant::GRAPH_MIN . "|dailyactivity":
        $this->_xaxis_categorie           = true;
        $this->_keep_category_order_value = true;
        break;
      case CGraphConstant::GRAPH_MIN . "|dailysleep":
        $this->_xaxis_categorie           = true;
        $this->_keep_category_order_value = true;
        break;
      case CGraphConstant::GRAPH_MIN . "|heartrate":
        $this->multi_data = true;
        break;

      case CGraphConstant::GRAPH_ALL . "|weight":
        $this->_add_curve_bezier = true;
      default:
    }
  }

  /**
   * Know if values in array are different of 0
   *
   * @param CAbstractConstant[] $constants constants objects
   *
   * @return bool
   * @throws CConstantException
   */
  public function hasValuesIn($constants) {
    $hasValue = false;
    foreach ($constants as $_constant) {
      if ($_constant && $_constant->getValue() !== "0") {
        $hasValue = true;
        break;
      }
    }

    return $hasValue;
  }

  /**
   * Manage constants to generate series data
   *
   * @param CAbstractConstant[] $constants constants
   *
   * @return array
   */
  private function generateSeries($constants) {
    $data = array();
    if ($this->multi_sources) {
      $constants = $this->findBetterGraph($this->separateData($constants));
    }
    CMbArray::ksortByProp($constants, "datetime");
    $data[$this->source] = $constants;

    return $this->treatData($data);
  }

  /**
   * Find sources where values are the most numerous
   *
   * @param array $source_data constants sort by sources
   *
   * @return array with the most numerous values
   */
  private function findBetterGraph($source_data) {
    $better_source = "";
    $better_count  = 0;
    foreach ($source_data as $_source_name => $_constants) {
      if (($count = count($_constants)) > $better_count) {
        $better_count  = $count;
        $better_source = $_source_name;
      }
    }
    $this->source = $better_source;
    if (!$this->user_api) {
      $this->user_api = CMbArray::get($this->users_apis, $better_source);
    }

    return CMbArray::get($source_data, $better_source);
  }

  /**
   * Separate data in function of her source
   *
   * @param CAbstractConstant[] $constants constants
   *
   * @return array
   */
  private function separateData(&$constants) {
    $sources           = CConstantReleve::getSources();
    $constants_sources = array();
    $this->source      = array();
    /** @var CAbstractConstant $_constant */
    CStoredObject::massLoadFwdRef($constants, "releve_id");
    foreach ($constants as $_constant_codeId => $_constant) {
      $releve = $_constant->loadRefReleve();
      $source = CMbArray::get($sources, $releve->source);
      if ($source == CConstantReleve::FROM_API || $source == CConstantReleve::FROM_DEVICE) {
        $source = CMbArray::get($sources, $releve->user_id);
      }

      $constants_sources[$source][$_constant_codeId] = $_constant;
    }

    return $constants_sources;
  }

  /**
   * Format data for heartrate constant
   *
   * @param array $data data formated
   *
   * @return array
   */
  private function treatData($data) {
    $parsed_data = array();

    foreach ($data as $_source => $_constants) {
      $parsed_data[] = $this->parseConstants($_constants);
    }
    $treatment_constants = array("hourlysleep", "heartrate");
    if ($this->multi_data && CMbArray::in($this->_spec_code, $treatment_constants)) {
      foreach ($parsed_data as $_source => $_data) {
        switch ($this->_spec_code) {
          case "hourlysleep":
            $parsed_data = $this->treatSleepData(CMbArray::get($_data, "data"));
            break;

          case "heartrate":
            $parsed_data = $this->treatHeartrateData($_data);
            break;
          default:
        }
      }
    }

    return $parsed_data;
  }

  /**
   * Parse array series for graph
   *
   * @param array $constants constants values
   *
   * @return array
   */
  private function parseConstants($constants) {
    $parsed_constant                  = array();
    $this->_params["series_length"][] = count($constants);
    $this->_params["index_series"]    = CMbArray::get($this->_params, "index_series", -1) + 1;
    $this->_params['index_value']     = 0;
    foreach ($constants as $_constant) {
      $this->specificData($_constant);
      $value = $this->parseConstant($_constant);
      if ($this->_xaxis_categorie) {
        $key                   = CMbArray::get($value, "day");
        $parsed_constant[$key] = $value;
      }
      else {
        $parsed_constant[] = $value;
      }
      $this->_params['index_value'] = CMbArray::get($this->_params, "index_value") + 1;
    }
    $this->_params["index_last_value"] = CMbArray::get($this->_params, "index_value") - 1;

    $data = $parsed_constant;
    //treatment different if xaxis is category
    if ($this->_xaxis_categorie) {
      $data   = array();
      $days   = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
      $values = $this->_keep_category_order_value ? $parsed_constant : $days;
      // $_value -> days || value_constant
      foreach ($values as $_value) {
        $_value                       = $this->_keep_category_order_value ? $_value : CMbArray::get($parsed_constant, $_value);
        $data[]                       = CMbArray::get($_value, "value");
        $this->_params["dt_series"][] = CMbArray::get($_value, "timestamps");
      }
    }

    $name = CMbArray::get($this->_params, "specific_name", CAppUI::tr($this->_spec->code));

    return array(
      "data" => $data,
      "name" => $name
    );
  }

  /**
   * Specific treatment for sleep data
   *
   * @param array $data data
   *
   * @return array
   */
  private function treatSleepData($data) {
    $sleep_data = array();
    if (!$states = CMbArray::get($this->_params, "states_sleep")) {
      $this->_no_data = true;

      return array();
    }
    asort($states);
    //traduction du sleep state
    foreach ($states as $_state) {
      $sleep_data[$_state]["name"]               = "<strong>" . CAppUI::tr("CStateInterval.state.hourlysleep." . $_state) . "</strong>";
      $this->_sleep_state_index[intval($_state)] = CAPITiers::getSpecFromState($_state);
    }

    $continued_courbe = array();
    $next_value       = null;
    for ($index = 0; $index < (count($data)); $index++) {
      $value      = CMbArray::get($data, $index);
      $next_value = CMbArray::get($data, ($index + 1));
      $state      = CMbArray::get($value, "state");
      $val        = CMbArray::get(array_keys(CMbArray::get($this->_value_state_sleep, $state)), 0);
      $next_state = CMbArray::get($next_value, "state");
      $end_value  = $next_value;
      // si même state concecutif, on agregge
      while ($state === $next_state) {
        $index++;
        $next_value = $end_value;
        $end_value  = CMbArray::get($data, ($index + 1));
        $next_state = CMbArray::get($end_value, "state");
      }
      $dt_min = CMbArray::get($value, "min_value");
      $dt_max = CMbArray::get($value, "max_value");
      if ($state === CMbArray::get($next_value, "state")) {
        $dt_max = CMbArray::get($next_value, "max_value");
      }

      if ($state == 0) {
        $this->_wakeup_time += $dt_max - $dt_min;
      }

      $sleep_data[$state]["data"][] = array("x" => $dt_min - 1000, "y" => null);
      $sleep_data[$state]["data"][] = array("x" => $dt_min, "y" => $val); //debut du sleep state
      $sleep_data[$state]["data"][] = array("x" => intval(($dt_min + $dt_max) / 2), "y" => $val); //milieu du sleep state
      $sleep_data[$state]["data"][] = array("x" => $dt_max, "y" => $val); //fin du sleep stat
      $sleep_data[$state]["data"][] = array("x" => $dt_max + 1000, "y" => null);

      $continued_courbe["data"][] = array("x" => $dt_min, "y" => $val);
      $continued_courbe["data"][] = array("x" => $dt_max, "y" => $val);
    }

    $continued_courbe["name"] = "none";
    $sleep_data[]             = $continued_courbe;

    return $sleep_data;
  }

  /**
   * Format data for heartrate constant
   *
   * @param array $data data formated
   *
   * @return array
   */
  private function treatHeartrateData($data) {
    $serie_1 = array();
    $serie_2 = array();
    $values  = CMbArray::get($data, "data");
    for ($i = 0; $i < count($values) - 1; $i++) {
      $current_element = $values[$i];
      $next_element    = $values[$i + 1];

      $serie_1[] = $current_element;
      // tps entre current_elt et next_elt > 30 minutes
      if ((CMbArray::get($next_element, "0") - CMbArray::get($current_element, "0")) > 30 * 60000) {
        $dt        = CMbArray::get($current_element, "0");
        $serie_1[] = array($dt + 1000, null);

        $serie_2[] = array($dt, CMbArray::get($current_element, "1"));
        $serie_2[] = array(CMbArray::get($next_element, "0") - 1000, CMbArray::get($next_element, "1"));
        $serie_2[] = array(CMbArray::get($next_element, "0"), null);
      }
    }
    $serie_1[]     = $values[count($values) - 1];
    $parsed_data[] = array(
      "data" => $serie_1,
      "name" => CMbArray::get($data, "name")
    );
    if (count($serie_2) > 0) {
      $parsed_data[] = array(
        "data" => $serie_2,
        "name" => ""
      );
    }

    return $parsed_data;
  }

  private function courbe_bezier($data, $N) {
    $n             = count($data);
    $dt            = 1 / $N;
    $t             = $dt;
    $end = CMbArray::get($data, $n - 1);
    //$points_courbe = array(CMbArray::get($end, 0), CMbArray::get($end, 1));
    $points_courbe = array(array(CMbArray::get($end, 0), CMbArray::get($end, 1)-1));
    while ($t < 1) {
      $value           = $this->point_bezier($data, $t);
      $value           = array(intval(CMbArray::get($value, 0)), intval(CMbArray::get($value, 1)));
      $points_courbe[] = $value;
      $t               += $dt;
    }
    $points_courbe[] = array(CMbArray::get($data, 0));
    return $points_courbe;
  }

  private function point_bezier($points, $t) {
    $n = count($points);
    while ($n > 1) {
      $points = $this->reduction($points, $t);
      $n      = count($points);
    }

    return CMbArray::get($points, 0);
  }

  private function reduction($points, $t) {
    $points_result = array();
    $N             = count($points);
    for ($i = 0; $i < ($N - 1); $i++) {
      $points_result[] = $this->interpolation_lineaire(CMbArray::get($points, $i), CMbArray::get($points, $i + 1), $t);
    }

    return $points_result;
  }

  private function interpolation_lineaire($A, $B, $t) {
    return $this->combinaison_lineaire($A, $B, $t, 1 - $t);
  }

  private function combinaison_lineaire($A, $B, $u, $v) {
    return array(
      (CMbArray::get($A, 0) * $u) + CMbArray::get($B, 0) * $v,
      (CMbArray::get($A, 1) * $u) + CMbArray::get($B, 1) * $v
    );
  }

  /**
   * Add informations in period datetime
   *
   * @param string $dt_min datetime min, mandatory
   * @param string $dt_max dattime end, optional
   *
   * @return void
   */
  public function addDatetime($dt_min, $dt_max = "") {
    if ($dt_max == "") {
      $dt_max = CMbDT::dateTime();
    }
    $this->dt_min = $dt_min;
    $this->dt_max = $dt_max;
  }
}
