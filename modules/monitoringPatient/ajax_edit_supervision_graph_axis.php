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

CCanDo::checkAdmin();

$supervision_graph_axis_id = CView::get("supervision_graph_axis_id", "ref class|CSupervisionGraphAxis");
$supervision_graph_id      = CView::get("supervision_graph_id", "ref class|CSupervisionGraph");

CView::checkin();

$axis = new CSupervisionGraphAxis();
if (!$axis->load($supervision_graph_axis_id)) {
  $axis->supervision_graph_id = $supervision_graph_id;
}
$axis->loadRefsNotes();

$smarty = new CSmartyDP();
$smarty->assign("axis", $axis);
$smarty->display("inc_edit_supervision_graph_axis");
