<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// @todo bloc n'est pas forcément actif
global $can;

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Bloc\CPosteSSPI;
use Ox\Mediboard\MonitoringPatient\SupervisionGraph;
use Ox\Mediboard\PatientMonitoring\CMonitoringConcentrator;
use Ox\Mediboard\PatientMonitoring\CMonitoringSession;
use Ox\Mediboard\MonitoringPatient\CSupervisionTimeline;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();
$operation_id = CView::get("operation_id", 'ref class|COperation', true);
$completed_view = CView::get("completed_view", 'bool default|1');
CView::checkin();

$print = true;

if ($completed_view) {
  $can->read |= CModule::getActive("dPbloc")->_can->read;
  $can->needsRead();
}

$operation = new COperation();
$operation->load($operation_id);

CAccessMedicalData::logAccess($operation);

$operation->loadRefsFwd();
$operation->loadRefsActesCCAM();
foreach ($operation->_ref_actes_ccam as $keyActe => $acte) {
  $acte =& $operation->_ref_actes_ccam[$keyActe];
  $acte->loadRefsFwd();
}

$operation->guessActesAssociation();
$salle = $operation->loadRefSalle();
$bloc = $salle->loadRefBloc();

$sejour =& $operation->_ref_sejour;
$sejour->loadRefsFwd();
$sejour->loadRefPrescriptionSejour();
$sejour->loadRefGrossesse();

if (CAppUI::gconf('dPsalleOp timings use_garrot') && CAppUI::gconf('dPsalleOp COperation garrots_multiples')) {
  $operation->loadGarrots();
}

$where_anesth_preop = $where_anesth_perop = $where_anesth_sspi = $surveillance_data = array();

$pack = null;

// Preop
if ($operation->graph_pack_preop_id) {
  [$perop_datetime_min, $perop_datetime_max] = SupervisionGraph::getTimingsByType($operation, "preop", $print);

  $where_anesth_preop["datetime"] = "BETWEEN '$perop_datetime_min' AND '$perop_datetime_max'";

  $operation->loadRefsAnesthPerops($where_anesth_preop);

  $pack = $operation->loadRefGraphPackPreop();

  [
    $preop_graphs, $preop_yaxes_count,
    $preop_time_min, $preop_time_max,
    $preop_time_debut_op_iso, $preop_time_fin_op_iso,
    $preop_evenement_groups, $preop_evenement_items, $preop_timeline_options, $display_current_time
    ] = CSupervisionTimeline::makeTimeline($operation, $pack, true, "preop", null, null, $print);

  $preop_time_debut_op = CMbDT::toTimestamp($preop_time_debut_op_iso);
  $preop_time_fin_op   = CMbDT::toTimestamp($preop_time_fin_op_iso);

  // Don't move the timeline
  $preop_timeline_options["zoomKey"] = '';
  $preop_timeline_options["zoomable"] = false;
  $preop_timeline_options["moveable"] = false;

  $surveillance_data["preop"] = array(
    "graphs"               => $preop_graphs,
    "yaxes_count"          => $preop_yaxes_count,
    "time_min"             => $preop_time_min,
    "time_max"             => $preop_time_max,
    "time_debut_op"        => $preop_time_debut_op,
    "time_fin_op"          => $preop_time_fin_op,
    "timeline_options"     => $preop_timeline_options,
    "display_current_time" => $display_current_time,
  );
}

// Perop
if ($operation->graph_pack_id) {
  [$perop_datetime_min, $perop_datetime_max] = SupervisionGraph::getTimingsByType($operation, "perop" , $print);

  $where_anesth_perop["datetime"] = "BETWEEN '$perop_datetime_min' AND '$perop_datetime_max'";

  $operation->loadRefsAnesthPerops($where_anesth_perop);

  $pack = $operation->loadRefGraphPack();

  [
    $perop_graphs, $perop_yaxes_count,
    $perop_time_min, $perop_time_max,
    $perop_time_debut_op_iso, $perop_time_fin_op_iso,
    $perop_evenement_groups, $perop_evenement_items, $perop_timeline_options, $display_current_time
    ] = CSupervisionTimeline::makeTimeline($operation, $pack, true, "perop", null, null, $print);

  $perop_time_debut_op = CMbDT::toTimestamp($perop_time_debut_op_iso);
  $perop_time_fin_op   = CMbDT::toTimestamp($perop_time_fin_op_iso);

  // Don't move the timeline
  $perop_timeline_options["zoomKey"] = '';
  $perop_timeline_options["zoomable"] = false;
  $perop_timeline_options["moveable"] = false;

  $surveillance_data["perop"] = array(
    "graphs"               => $perop_graphs,
    "yaxes_count"          => $perop_yaxes_count,
    "time_min"             => $perop_time_min,
    "time_max"             => $perop_time_max,
    "time_debut_op"        => $perop_time_debut_op,
    "time_fin_op"          => $perop_time_fin_op,
    "timeline_options"     => $perop_timeline_options,
    "display_current_time" => $display_current_time,
  );
}

// SSPI
if ($operation->graph_pack_sspi_id) {
  [$sspi_datetime_min, $sspi_datetime_max] = SupervisionGraph::getTimingsByType($operation, "sspi", $print);

  $where_anesth_sspi["datetime"] = "BETWEEN '$sspi_datetime_min' AND '$sspi_datetime_max'";

  $operation->loadRefsAnesthPerops($where_anesth_sspi);

  $pack = $operation->loadRefGraphPackSSPI();

  [
    $sspi_graphs, $sspi_yaxes_count,
    $sspi_time_min, $sspi_time_max,
    $sspi_time_debut_op_iso, $sspi_time_fin_op_iso,
    $sspi_evenement_groups, $sspi_evenement_items, $sspi_timeline_options, $display_current_time
    ] = CSupervisionTimeline::makeTimeline($operation, $pack, true, "sspi", null, null, $print);

  $sspi_time_debut_op = CMbDT::toTimestamp($sspi_time_debut_op_iso);
  $sspi_time_fin_op   = CMbDT::toTimestamp($sspi_time_fin_op_iso);

  // Don't move the timeline
  $sspi_timeline_options["zoomKey"] = '';
  $sspi_timeline_options["zoomable"] = false;
  $sspi_timeline_options["moveable"] = false;

  $surveillance_data["sspi"] = array(
    "graphs"               => $sspi_graphs,
    "yaxes_count"          => $sspi_yaxes_count,
    "time_min"             => $sspi_time_min,
    "time_max"             => $sspi_time_max,
    "time_debut_op"        => $sspi_time_debut_op,
    "time_fin_op"          => $sspi_time_fin_op,
    "timeline_options"     => $sspi_timeline_options,
    "display_current_time" => $display_current_time,
  );
}

if ($pack) {
  $pack->getTimingValues($operation);
}

$concentrators = null;
$all_concentrators = null;
$session = null;
/* Initialize the data for the concentrator */
if (CModule::getActive("patientMonitoring")) {
  $postes = array();

  $poste_load = new CPosteSSPI();
  $postes = $poste_load->loadMatchingListEsc();

  $where_concentrator           = array();
  $where_concentrator["active"] = " = '1'";

  $concentrators = CStoredObject::massLoadBackRefs($postes, "monitoring_concentrators", null, $where_concentrator);
  $all_concentrators = CMonitoringConcentrator::getForBloc($bloc);
  $session = CMonitoringSession::getCurrentSession($operation);
  $operation->_active_session = $session;
}

$patient = $operation->_ref_sejour->_ref_patient;
$patient_insnir = $patient->loadRefPatientINSNIR();
$patient_insnir->createDatamatrix($patient_insnir->createDataForDatamatrix());

// Création du template
$smarty = new CSmartyDP("modules/dPsalleOp");
$smarty->assign("patient"          , $patient);
$smarty->assign("operation"        , $operation);
$smarty->assign("surveillance_data", $surveillance_data);
$smarty->assign("pack"             , $pack);
$smarty->assign("concentrators"    , $concentrators);
$smarty->assign("all_concentrators", $all_concentrators);
$smarty->assign("session"          , $session);
$smarty->assign("completed_view"   , $completed_view);
$smarty->display("vw_partogramme");
