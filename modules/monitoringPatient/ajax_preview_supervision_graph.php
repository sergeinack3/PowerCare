<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\MonitoringPatient\CSupervisionGraph;

CCanDo::checkAdmin();
$supervision_graph_id = CView::get("supervision_graph_id", "ref class|CSupervisionGraph");
CView::checkin();

$graph = new CSupervisionGraph();
$graph->load($supervision_graph_id);
$axes = $graph->loadRefsAxes();

$sample = array();

$minute = 60000;
$start  = 1291196760000;
$end    = $start + $minute * 45;
$times  = range($start, $end, $minute);

foreach ($axes as $_axis) {
  if (!$_axis->actif) {
    continue;
  }

  $_series = $_axis->loadRefsSeries();

  foreach ($_series as $_serie) {
    $sample[$_serie->value_type_id][$_serie->value_unit_id ?: "none"] = $_serie->getSampleData($times);
  }
}

$data = $graph->buildGraph($sample, $start - 2 * $minute, $end + 2 * $minute);

$smarty = new CSmartyDP();
$smarty->assign("data"                , $data);
$smarty->assign("times"               , $times);
$smarty->assign("supervision_graph_id", $supervision_graph_id);
$smarty->assign("graph"               , $graph);
$smarty->display("inc_preview_supervision_graph");
