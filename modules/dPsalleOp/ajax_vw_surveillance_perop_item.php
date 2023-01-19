<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\MonitoringPatient\CSupervisionTimeline;
use Ox\Mediboard\MonitoringPatient\ISupervisionTimelineItem;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();

$operation_id = CView::get("operation_id", "ref class|COperation");
$type         = CView::get("type", "str default|perop");
$items        = CView::get("items", "str");
$element_main = CView::get("element_main", "str");
CView::checkin();

$items = explode("|", $items);
CMbArray::removeValue("", $items);

$interv = new COperation;
$interv->load($operation_id);

CAccessMedicalData::logAccess($interv);

$interv->loadRefSejour()->loadRefPatient()->loadRefLatestConstantes();

$sejour = $interv->_ref_sejour;
$prescription = $sejour->loadRefPrescriptionSejour();

$readonly = false;
$current_datetime = CMbDT::dateTime();

if (CModule::getActive("maternite") && $sejour->grossesse_id) {
  $_grossesse = $sejour->loadRefGrossesse();
  if ($_grossesse->datetime_accouchement && CAppUI::gconf("maternite CGrossesse lock_partogramme")) {
    $readonly = true;
  }
}

$group = CGroups::loadCurrent();
switch ($type) {
  default:
    $type = "perop";
  case "preop":
    $pack = $interv->loadRefGraphPackPreop();

    if ($interv->graph_pack_preop_locked_user_id && ($interv->datetime_lock_graph_preop < $current_datetime)) {
      $readonly = true;
    }
    break;
  case "perop":
    $pack = $interv->loadRefGraphPack();

    if ($interv->graph_pack_locked_user_id  && ($interv->datetime_lock_graph_perop < $current_datetime)) {
      $readonly = true;
    }
    break;
  case "sspi":
    $pack = $interv->loadRefGraphPackSSPI();

    if ($interv->graph_pack_sspi_locked_user_id  && ($interv->datetime_lock_graph_sspi < $current_datetime)) {
      $readonly = true;
    }
    break;
}

[
  $graphs, $yaxes_count,
  $time_min, $time_max,
  $time_debut_op_iso, $time_fin_op_iso,
  $evenement_groups, $evenement_items, $timeline_options, $display_current_time
] = CSupervisionTimeline::makeTimeline($interv, $pack, $readonly, $type, $items, $element_main);

$time_debut_op = CMbDT::toTimestamp($time_debut_op_iso);
$time_fin_op   = CMbDT::toTimestamp($time_fin_op_iso);

$data = array();

/** @var ISupervisionTimelineItem[] $graphs */

foreach ($graphs as $_graph) {
  $data[$_graph->getIdentifier()] = $_graph->getData();
}

CApp::json($data);
