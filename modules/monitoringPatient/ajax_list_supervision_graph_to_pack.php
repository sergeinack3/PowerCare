<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\MonitoringPatient\CSupervisionGraphPack;

CCanDo::checkAdmin();
$supervision_graph_pack_id = CView::get("supervision_graph_pack_id", "ref class|CSupervisionGraphPack");
CView::checkin();

$pack = new CSupervisionGraphPack();
$pack->load($supervision_graph_pack_id);
$links = $pack->loadRefsGraphLinks();

foreach ($links as $_link) {
  $_link->loadRefGraph();
}

$smarty = new CSmartyDP();
$smarty->assign("pack", $pack);
$smarty->display("inc_list_supervision_graph_to_pack");

