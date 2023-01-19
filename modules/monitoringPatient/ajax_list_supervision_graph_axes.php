<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\MonitoringPatient\CSupervisionGraph;

CCanDo::checkAdmin();

$supervision_graph_id = CView::get("supervision_graph_id", "ref class|CSupervisionGraph");

CView::checkin();

$graph = new CSupervisionGraph();
$graph->load($supervision_graph_id);

$axes                = $graph->loadRefsAxes();
$counter_axes_active = 0;

foreach ($axes as $_axe) {
  if ($_axe->actif) {
    $counter_axes_active++;
  }
}

CStoredObject::massLoadBackRefs($axes, "series");

$smarty = new CSmartyDP();
$smarty->assign("axes"               , $axes);
$smarty->assign("graph"              , $graph);
$smarty->assign("counter_axes_active", $counter_axes_active);
$smarty->display("inc_list_supervision_graph_axes");
