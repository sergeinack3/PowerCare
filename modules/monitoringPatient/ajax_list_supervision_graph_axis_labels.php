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
CView::checkin();

$axis = new CSupervisionGraphAxis();
$axis->load($supervision_graph_axis_id);

$labels = $axis->loadRefsLabels();

$smarty = new CSmartyDP();
$smarty->assign("labels", $labels);
$smarty->assign("axis"  , $axis);
$smarty->display("inc_list_supervision_graph_labels");
