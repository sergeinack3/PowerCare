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
use Ox\Mediboard\MonitoringPatient\CSupervisionGraphAxis;
use Ox\Mediboard\MonitoringPatient\CSupervisionGraphSeries;

CCanDo::checkAdmin();
$supervision_graph_series_id = CView::get("supervision_graph_series_id", "ref class|CSupervisionGraphSeries");
$supervision_graph_axis_id   = CView::get("supervision_graph_axis_id", "ref class|CSupervisionGraphAxis");
CView::checkin();

$axis = new CSupervisionGraphAxis();
$axis->load($supervision_graph_axis_id);

$series = new CSupervisionGraphSeries();
if (!$series->load($supervision_graph_series_id)) {
  $series->supervision_graph_axis_id = $supervision_graph_axis_id;
}
$series->loadRefsNotes();

$smarty = new CSmartyDP();
$smarty->assign("series", $series);
$smarty->assign("axis", $axis);
$smarty->display("inc_edit_supervision_graph_series");
