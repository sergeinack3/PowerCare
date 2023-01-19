<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Mediboard\System\AccessLog\CAccessLog;

/**
 * Génération des données des graphiques du palmarès ressources
 *
 * @param string $module   Module concerné
 * @param string $date     Date de référence
 * @param string $element  Type de données à afficher
 * @param string $interval Interval de temps à analyser
 * @param int    $numelem  Nombre d'éléments maximum
 *
 * @return array Les données de palmarès
 */
function graphRessourceLog($module, $date, $element = 'duration', $interval = 'day', $numelem = 4) {
  if (!$date) {
    $date = CMbDT::date();
  }

  switch ($interval) {
    default:
    case "day":
      $startx = "$date 00:00:00";
      $endx   = "$date 23:59:59";
      break;
    case "month":
      $startx = CMbDT::dateTime("-1 MONTH", "$date 00:00:00");
      $endx   = "$date 23:59:59";
      break;
    case "year":
      $startx = CMbDT::dateTime("-27 WEEKS", "$date 00:00:00");
      $endx   = "$date 23:59:59";
      break;
  }

  if ($module == "total") {
    $groupmod    = 0;
    $module_name = null;
  }
  elseif ($module == "modules") {
    $groupmod    = 1;
    $module_name = null;
  }
  else {
    $groupmod    = 0;
    $module_name = $module;
  }

  $logs = CAccessLog::loadAggregation($startx, $endx, $groupmod, $module_name);

  $series = array();
  $i      = 0;
  foreach ($logs as $data) {
    $series[$i]["data"]   = array(array(0, $data->$element));
    $series[$i]["label"]  = $module != 'modules' ? $data->_action : $data->_module;
    $series[$i]["module"] = $data->_module;
    $i++;
  }

  usort(
    $series,
    function ($a, $b) {
      return $a["data"][0][1] < $b["data"][0][1];
    }
  );

  if ($numelem > 0) {
    $seriesNew = array_slice($series, 0, $numelem);
    if (count($series) > $numelem) {
      $other                        = array_slice($series, $numelem);
      $seriesNew[$numelem]["data"]  = array(array(0, 0));
      $seriesNew[$numelem]["label"] = "Autres";
      $n                            = 0;
      foreach ($other as $_other) {
        $seriesNew[$numelem]["data"][0][1] += $_other["data"][0][1];
        $n++;
      }
      $seriesNew[$numelem]["label"] .= " ($n)";
    }
    $series = $seriesNew;
  }

  // Set up the title for the graph
  $title = CMbDT::format($date, CAppUI::conf("longdate"));
  if ($module) {
    $title .= " : " . CAppUI::tr($module);
  }

  $options = array(
    'title'    => $title,
    'name'     => $module,
    'HtmlText' => false,
    'grid'     => array(
      'verticalLines'   => false,
      'horizontalLines' => false,
      'outlineWidth'    => 0
    ),
    'xaxis'    => array('showLabels' => false),
    'yaxis'    => array('showLabels' => false),
    'pie'      => array(
      'show'      => true,
      'sizeRatio' => 0.5
    ),
    'legend'   => array(
      'backgroundOpacity' => 0.3
    )
  );

  return array('series' => $series, 'options' => $options);
}
