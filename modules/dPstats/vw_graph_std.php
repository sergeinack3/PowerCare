<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;

CCanDo::checkRead();

// map Graph Hospi
CAppUI::requireModuleFile("dPstats", "graph_patparservice");
CAppUI::requireModuleFile("dPstats", "graph_patpartypehospi");
CAppUI::requireModuleFile("dPstats", "graph_joursparservice");
CAppUI::requireModuleFile("dPstats", "graph_jourspatients");

// map Graph Bloc
CAppUI::requireModuleFile("dPstats", "graph_activite");
CAppUI::requireModuleFile("dPstats", "graph_activite_zoom");
CAppUI::requireModuleFile("dPstats", "graph_pratdiscipline");
CAppUI::requireModuleFile("dPstats", "graph_pratdiscipline_for_month");
CAppUI::requireModuleFile("dPstats", "graph_patjoursalle");
CAppUI::requireModuleFile("dPstats", "graph_op_annulees");
CAppUI::requireModuleFile("dPstats", "graph_workflow_operation");
CAppUI::requireModuleFile("dPstats", "graph_occupation_salle_total");
CAppUI::requireModuleFile("dPstats", "graph_temps_salle");

// map Graph SSPI
CAppUI::requireModuleFile("dPstats", "graph_patparheure_reveil");
CAppUI::requireModuleFile("dPstats", "graph_patrepartjour");

// Bornes de date des statistiques
$date_min = CValue::get("_date_min", CMbDT::date("-1 YEAR"));

$date_max = CValue::get("_date_max", CMbDT::date());

if (CValue::get('_complete_months', 0)) {
  $date_min = CMbDT::date("FIRST DAY OF THIS MONTH", $date_min);
  $date_max = CMbDT::date("FIRST DAY OF NEXT MONTH", $date_max);
  $date_max = CMbDT::date("-1 DAY", $date_max);
}

$date_zoom  = CValue::get("date_zoom", CMbDT::transform("+0 DAY", CMbDT::date(), "%m/%Y"));
$debut_zoom = substr($date_zoom, 3, 7) . "-" . substr($date_zoom, 0, 2) . "-01";
$fin_zoom   = CMbDT::date("+1 MONTH", $debut_zoom);
$fin_zoom   = CMbDT::date("-1 DAY", $fin_zoom);

// Autres éléments de filtrage
$service_id    = CValue::get("service_id");
$type          = CValue::get("type");
$prat_id       = CValue::get("prat_id");
$func_id       = CValue::get("func_id");
$discipline_id = CValue::get("discipline_id");
$septique      = CValue::get("septique");
$type_data     = CValue::get("type_data", "prevue");

$salle_id   = CValue::get("salle_id");
$bloc_id    = CValue::get("bloc_id");
$codes_ccam = strtoupper(CValue::get("codes_ccam", ""));
$hors_plage = CValue::get("hors_plage", 1);

// Possibilité de zoom
$can_zoom = CValue::get("can_zoom");

// Nom du graphique à afficher
$type_graph = CValue::get("type_graph");

CView::enforceSlave();

switch ($type_graph) {
  case "patparservice":
    $graph = graphPatParService(
      $date_min, $date_max,
      $prat_id, $service_id, $type,
      $func_id, $discipline_id, $septique, $type_data
    );
    break;
  case "patpartypehospi":
    $graph = graphPatParTypeHospi(
      $date_min, $date_max,
      $prat_id, $service_id, $type,
      $func_id, $discipline_id, $septique, $type_data
    );
    break;
  case "jourparservice":
    $graph = graphJoursParService(
      $date_min, $date_max,
      $prat_id, $service_id, $type,
      $func_id, $discipline_id, $septique, $type_data
    );
    break;
  case "intervparsalle":
    $can_zoom = "intervparsallezoom";
    $graph    = graphActivite(
      $date_min, $date_max,
      $prat_id, $salle_id, $bloc_id,
      $func_id, $discipline_id, $codes_ccam, $type,
      $hors_plage
    );
    break;
  case "intervparsallezoom":
    $graph = graphActiviteZoom(
      $date_zoom,
      $prat_id, $salle_id, $bloc_id,
      $func_id, $discipline_id, $codes_ccam, $type,
      $hors_plage
    );
    break;
  case "opannulees":
    $graph = graphOpAnnulees(
      $date_min, $date_max,
      $prat_id, $salle_id, $bloc_id,
      $codes_ccam, $type, $hors_plage
    );
    break;
  case "workflowoperation":
    $graph = graphWorkflowOperation(
      $date_min, $date_max,
      $prat_id, $salle_id, $bloc_id,
      $func_id, $discipline_id, $codes_ccam, $type, $hors_plage
    );
    break;
  case "intervparprat":
    $can_zoom = "intervparpratmonth";
    $graph    = graphPraticienDiscipline(
      $date_min, $date_max,
      $prat_id, $salle_id, $bloc_id,
      $func_id, $discipline_id, $codes_ccam, $type,
      $hors_plage
    );
    break;
  case "intervparpratmonth":
    $graph = graphPraticienDisciplineForMonth(
      $date_zoom,
      $prat_id, $salle_id, $bloc_id,
      $func_id, $discipline_id, $codes_ccam, $type,
      $hors_plage
    );
    break;
  case "occupationsalletotal":
    $can_zoom       = "occupationsalletotalzoom";
    $listOccupation = graphOccupationSalle(
      $date_min, $date_max,
      $prat_id, $salle_id, $bloc_id,
      $func_id, $discipline_id, $codes_ccam, $type,
      $hors_plage, 'MONTH'
    );
    $graph          = $listOccupation["total"];
    break;
  case "occupationsalletotalzoom":
    $graph = graphOccupationSalle(
      $debut_zoom, $fin_zoom,
      $prat_id, $salle_id, $bloc_id,
      $func_id, $discipline_id, $codes_ccam, $type,
      $hors_plage, 'DAY'
    );
    break;
  case "occupationsallemoy":
    $listOccupation = graphOccupationSalle(
      $date_min, $date_max,
      $prat_id, $salle_id, $bloc_id,
      $func_id, $discipline_id, $codes_ccam, $type,
      $hors_plage, 'MONTH'
    );
    $graph          = $listOccupation["moyenne"];
    break;
  case "ressourcesbloc":
    $can_zoom = "ressourcesbloczoom";
    $graph    = graphTempsSalle(
      $date_min, $date_max,
      $prat_id, $salle_id, $bloc_id,
      $func_id, $discipline_id, $codes_ccam, $type,
      $hors_plage, 'MONTH'
    );
    break;
  case "ressourcesbloczoom":
    $graph = graphTempsSalle(
      $debut_zoom, $fin_zoom,
      $prat_id, $salle_id, $bloc_id,
      $func_id, $discipline_id, $codes_ccam, $type,
      $hors_plage, 'DAY'
    );
    break;
  case "patjoursalle":
    $graph = graphPatJourSalle(
      $date_min, $date_max,
      $prat_id, $salle_id, $bloc_id,
      $func_id, $discipline_id, $codes_ccam, $hors_plage
    );
    break;
  case "patparjoursspi":
    $graph = graphPatRepartJour(
      $date_min, $date_max,
      $prat_id, $bloc_id, $func_id, $discipline_id,
      $codes_ccam
    );
    break;
  case "patparheuresspi":
    $graph = graphPatParHeureReveil(
      $date_min, $date_max,
      $prat_id, $bloc_id, $func_id, $discipline_id,
      $codes_ccam
    );
    break;
  case "jourspatients":
    $can_zoom = "jourspatientszoom";
    $graph    = graphJoursPatients(
      $date_min, $date_max,
      $prat_id, $service_id, $type, $discipline_id, $septique
    );
    break;
  case "jourspatientszoom":
    $results = detailJoursPatient($debut_zoom, $fin_zoom, $prat_id, $service_id, $type, $discipline_id, $septique);

    // Création du template
    $smarty = new CSmartyDP();

    $smarty->assign("results", $results);
    $smarty->assign("date", $date_zoom);

    $smarty->display("vw_jours_patients.tpl");

    return;
  default:
    $graph = array();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("can_zoom", $can_zoom);
$smarty->assign("type_graph", $type_graph);
$smarty->assign("graph", $graph);

$smarty->display("vw_graph_std.tpl");
