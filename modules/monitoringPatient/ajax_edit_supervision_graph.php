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
$graph->loadRefsNotes();

if (!$graph->_id) {
  $graph->height = 200;
}

$smarty = new CSmartyDP();
$smarty->assign("graph", $graph);
$smarty->display("inc_edit_supervision_graph");
