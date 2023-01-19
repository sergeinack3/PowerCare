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
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\MonitoringPatient\CSupervisionGraphPack;
use Ox\Mediboard\MonitoringPatient\CSupervisionGraphToPack;
use Ox\Mediboard\MonitoringPatient\CSupervisionTimedEntity;

CCanDo::checkAdmin();
$supervision_graph_pack_id    = CView::get("supervision_graph_pack_id", "ref class|CSupervisionGraphPack");
$supervision_graph_to_pack_id = CView::get("supervision_graph_to_pack_id", "ref class|CSupervisionGraphToPack");
$graph_class                  = CView::get("graph_class", "str");
CView::checkin();

$pack = new CSupervisionGraphPack();
$pack->load($supervision_graph_pack_id);

$link = new CSupervisionGraphToPack();
if ($supervision_graph_to_pack_id) {
  $link->load($supervision_graph_to_pack_id);
  $link->loadRefsNotes();
  $graph_class = $link->graph_class;
}
else {
  if (!$graph_class) {
    return;
  }

  $link->graph_class = $graph_class;
  $link->rank        = 1;
}

if ($supervision_graph_pack_id) {
  $link->pack_id = $supervision_graph_pack_id;
}

$item = new $graph_class;
if (!$item instanceof CSupervisionTimedEntity) {
  return;
}

$group             = CGroups::loadCurrent();
$item->owner_class = $group->_class;
$item->owner_id    = $group->_id;
$item->disabled    = 0;
$items             = $item->loadMatchingList();

$smarty = new CSmartyDP();
$smarty->assign("items", $items);
$smarty->assign("pack" , $pack);
$smarty->assign("link" , $link);
$smarty->display("inc_edit_supervision_graph_to_pack");
