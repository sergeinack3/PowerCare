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

CCanDo::checkAdmin();

$supervision_graph_id = CView::get("supervision_graph_id", "ref class|CSupervisionGraph");

CView::checkin();

$smarty = new CSmartyDP();
$smarty->assign("supervision_graph_id", $supervision_graph_id);
$smarty->display("vw_supervision_graph");
