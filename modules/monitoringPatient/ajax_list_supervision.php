<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\MonitoringPatient\CSupervisionGraph;
use Ox\Mediboard\MonitoringPatient\CSupervisionGraphPack;
use Ox\Mediboard\MonitoringPatient\CSupervisionInstantData;
use Ox\Mediboard\MonitoringPatient\CSupervisionTable;
use Ox\Mediboard\MonitoringPatient\CSupervisionTimedData;
use Ox\Mediboard\MonitoringPatient\CSupervisionTimedPicture;

CCanDo::checkAdmin();

$group = CGroups::loadCurrent();

$graphs         = CSupervisionGraph::getAllFor($group);
$tables         = CSupervisionTable::getAllFor($group);
$timed_data     = CSupervisionTimedData::getAllFor($group);
$timed_pictures = CSupervisionTimedPicture::getAllFor($group);
$instant_data   = CSupervisionInstantData::getAllFor($group);
$packs          = CSupervisionGraphPack::getAllFor($group, true);

foreach ($graphs as $_graph) {
  $_axes = $_graph->loadRefsAxes();

  foreach ($_axes as $_axis) {
    $_axis->loadBackRefs("series");
  }
}

foreach ($tables as $table) {
  $table->loadRefsRows();
}

$smarty = new CSmartyDP();
$smarty->assign("graphs"        , $graphs);
$smarty->assign("tables"        , $tables);
$smarty->assign("packs"         , $packs);
$smarty->assign("timed_data"    , $timed_data);
$smarty->assign("timed_pictures", $timed_pictures);
$smarty->assign("instant_data"  , $instant_data);
$smarty->display("inc_list_supervision_graph");
