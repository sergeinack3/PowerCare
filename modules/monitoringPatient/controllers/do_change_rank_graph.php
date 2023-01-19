<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\MonitoringPatient\CSupervisionGraphToPack;

CCanDo::checkAdmin();
$supervision_graph_to_pack_id = CView::post("supervision_graph_to_pack_id", "ref class|CSupervisionGraphToPack");
$rank                         = CView::post("rank", "num");
$wish_rank                    = CView::post("wish_rank", "num");
CView::checkin();

$old_rank = 0;

$graph_to_pack = new CSupervisionGraphToPack();
$graph_to_pack->load($supervision_graph_to_pack_id);

if (!$wish_rank) {
  $old_rank = $graph_to_pack->rank;
}

$graph_to_pack->rank = $wish_rank ?: $rank;

$other_graph_to_pack = $graph_to_pack->loadGraphToPackByRank($wish_rank ?: $rank);

if ($msg = $graph_to_pack->store()) {
  return $msg;
}

if ($other_graph_to_pack->_id) {
  $other_graph_to_pack->rank = $wish_rank ? $rank : $old_rank;

  if ($msg = $other_graph_to_pack->store()) {
    return $msg;
  }
}

CAppUI::displayMsg($msg, CAppUI::tr("CSupervisionGraphToPack-Modified rank"));

echo CAppUI::getMsg();
CApp::rip();
