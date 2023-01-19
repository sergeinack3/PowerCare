<?php

/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Bloc\CPosteSSPI;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\MonitoringPatient\SupervisionGraph;
use Ox\Mediboard\PatientMonitoring\CMonitoringConcentrator;
use Ox\Mediboard\PatientMonitoring\CMonitoringSession;
use Ox\Mediboard\MonitoringPatient\CSupervisionGraphPack;
use Ox\Mediboard\MonitoringPatient\CSupervisionTimeline;
use Ox\Mediboard\Personnel\CPersonnel;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CTypeAnesth;
use Ox\Mediboard\Prescription\CPrescription;

CCanDo::checkRead();
$operation_id       = CView::get("operation_id", "ref class|COperation");
$type               = CView::get("type", "str default|perop");
$show_cormack       = CView::get("show_cormack", "num default|1");
$force              = CView::get("force", "bool default|0");
$date               = CView::get("date", "date default|" . CMbDT::date(), true);
$modif_operation    = CCanDo::edit() || $date >= CMbDT::date();
$isDossierPerinatal = CView::get("isDossierPerinatal", "bool default|0");
$print              = CView::get("print", "bool default|0");
$hide_infos         = CView::get("hide_infos", "bool default|0", true);
CView::checkin();

$interv = new COperation();
$interv->load($operation_id);

CAccessMedicalData::logAccess($interv);

$interv->loadRefSejour()->loadRefPatient()->loadRefLatestConstantes();
$interv->loadRefPlageOp();

$salle = $interv->loadRefSalle();
$bloc  = $salle->loadRefBloc();

// Poste SSPI
$interv->loadRefPoste();
$interv->loadRefPostePreop();

// Affectation de personnel
$interv->loadAffectationsPersonnel();
$affectations_personnel = $interv->_ref_affectations_personnel;

$affectations_operation = array_merge(
    $affectations_personnel["sagefemme"],
    $affectations_personnel["aux_puericulture"],
    $affectations_personnel["aide_soignant"]
);

$interv->_ref_sejour->_ref_patient->loadRefDossierMedical();

$sejour       = $interv->_ref_sejour;
$prescription = $sejour->loadRefPrescriptionSejour();

$consult_anesth = $interv->loadRefsConsultAnesth();

$readonly = false;

$group = CGroups::loadCurrent();

$parto = false;
if ($sejour->grossesse_id && CModule::getActive("maternite")) {
    $_grossesse = $sejour->loadRefGrossesse();
    $parto      = true;
    if ($_grossesse->datetime_accouchement && CAppUI::conf("maternite CGrossesse lock_partogramme", $group)) {
        $readonly = true;
    }
}

if (CAppUI::conf("dPsalleOp COperation garrots_multiples", $group)) {
    $interv->loadGarrots();
}

$use_context      = $type;
$current_datetime = CMbDT::dateTime();

switch ($type) {
    default:
        $type = "perop";
    case "preop":
        $pack = $interv->loadRefGraphPackPreop();

        if ($interv->graph_pack_preop_locked_user_id && ($interv->datetime_lock_graph_preop < $current_datetime)) {
            $readonly = true;
        }

        if ($parto) {
            $use_context = "parto";
        }
        break;
    case 'partogramme':
    case "perop":
        $pack = $interv->loadRefGraphPack();

        if ($interv->graph_pack_locked_user_id && ($interv->datetime_lock_graph_perop < $current_datetime)) {
            $readonly = true;
        }

        if ($parto) {
            $use_context = "parto";
        }
        break;
    case "sspi":
        $pack = $interv->loadRefGraphPackSSPI();

        if ($interv->graph_pack_sspi_locked_user_id && ($interv->datetime_lock_graph_sspi < $current_datetime)) {
            $readonly = true;
        }

        if ($parto) {
            $use_context = "post_partum";
        }
        break;
}

$pack->getTimingValues($interv);

$graph_packs      = CSupervisionGraphPack::getAllFor($group, false, $use_context);
$select_main_pack = null;

if (!$pack->_id && ($type == 'sspi' || $type == 'preop' || $type == 'partogramme')) {
    foreach ($graph_packs as $_pack) {
        if (($type == 'sspi' && $_pack->main_pack) || (($use_context == "post_partum") && (count($graph_packs) == 1))) {
            $select_main_pack = $_pack->_id;
        } elseif ($type == 'preop' && $_pack->main_pack) {
            $select_main_pack = $_pack->_id;
        } elseif (($type == 'partogramme') && (count($graph_packs) == 1)) {
            $select_main_pack = $_pack->_id;
        }
    }

    if ($select_main_pack && $type == 'preop') {
        $interv->graph_pack_preop_id = $select_main_pack;
        $interv->store();
        $pack = $interv->loadRefGraphPackPreop();
    } elseif ($select_main_pack && $type == 'sspi') {
        $interv->graph_pack_sspi_id = $select_main_pack;
        $interv->store();
        $pack = $interv->loadRefGraphPackSSPI();
    } elseif ($select_main_pack && $type == 'partogramme') {
        $interv->graph_pack_id = $select_main_pack;
        $interv->store();
        $pack = $interv->loadRefGraphPack();
    }
}

/* Renseignement automatique du pack de graphique en fonction du type d'anesthésie */
if (!$pack->_id && !$interv->graph_pack_id && $interv->type_anesth) {
    $where = [
        'anesthesia_type' => " = $interv->type_anesth",
        'use_contexts'    => "LIKE '%$use_context%'",
        'disabled'        => " = '0'",
        'owner_class'     => "= '$group->_class'",
        'owner_id'        => "= '$group->_id'",
    ];

    $pack->loadObject($where);

    if ($pack->_id) {
        if (in_array($type, ['perop', 'partogramme'])) {
            $interv->graph_pack_id = $pack->_id;
            $interv->store();
            $interv->loadRefGraphPack();
        } elseif ($type == 'sspi') {
            $interv->graph_pack_sspi_id = $pack->_id;
            $interv->store();
            $interv->loadRefGraphPackSSPI();
        } elseif ($type == 'preop') {
            $interv->graph_pack_preop_id = $pack->_id;
            $interv->store();
            $interv->loadRefGraphPackPreop();
        }
    }
}

if ($pack->_id && !isset($graph_packs[$pack->_id])) {
    $graph_packs[$pack->_id] = $pack;
}

$pack->isProtocolMDStream();

SupervisionGraph::$limited_view_datas = $hide_infos;

[
    $graphs,
    $yaxes_count,
    $time_min,
    $time_max,
    $time_debut_op_iso,
    $time_fin_op_iso,
    $evenement_groups,
    $evenement_items,
    $timeline_options,
    $display_current_time,
] = CSupervisionTimeline::makeTimeline($interv, $pack, $readonly, $type, null, null, $print);

$time_debut_op = CMbDT::toTimestamp($time_debut_op_iso);
$time_fin_op   = CMbDT::toTimestamp($time_fin_op_iso);

$concentrators        = null;
$all_concentrators    = null;
$session              = null;
$current_concentrator = null;
$full_url_api         = "";

if (CModule::getActive("patientMonitoring")) {
    $postes = [];

    if (in_array($type, ["preop", "perop", "sspi", 'partogramme'])) {
        $poste_load       = new CPosteSSPI();
        $poste_load->type = ($type != 'sspi') ? "preop" : "sspi";
        $postes           = $poste_load->loadMatchingListEsc();
    }

    $where_concentrator           = [
        "active" => " = '1'"
    ];

    $concentrators = CStoredObject::massLoadBackRefs($postes, "monitoring_concentrators", null, $where_concentrator);
    //$concentrators = CMonitoringConcentrator::getAvailable($interv, $type);
    $all_concentrators       = CMonitoringConcentrator::getForBloc($bloc);
    $session                 = CMonitoringSession::getCurrentSession($interv, $type);
    $interv->_active_session = $session;

    CMbArray::naturalSort($concentrators, ["label"]);

    if ($type == "preop" || $type == "sspi") {
        foreach ($concentrators as $_concentrator) {
            if (($_concentrator->object_id == $interv->_ref_poste_preop->_id) && ($type == "preop")) {
                $current_concentrator = $_concentrator->_id;
            }
            if (($_concentrator->object_id == $interv->_ref_poste->_id) && ($type == "sspi")) {
                $current_concentrator = $_concentrator->_id;
            }
        }
    } elseif ($type == "perop" && (count($all_concentrators) == 1)) {
        $current_concentrator = (reset($all_concentrators)->object_id == $interv->_ref_salle->_id) ? reset(
            $all_concentrators
        )->_id : null;
    }

    // build url api
    $url_api = CAppUI::gconf('patientMonitoring dumpServer url_api');
    $secured = CAppUI::gconf('patientMonitoring CMonitoringConcentrator secured_connection');

    if ($url_api) {
        $protocol     = ($secured) ? 'https' : 'http';
        $full_url_api = "{$protocol}://{$url_api}/api/skhopeServer/getBoxDataLive";
    }
}

$listAnesths = new CMediusers();
$listAnesths = $listAnesths->loadAnesthesistes(PERM_DENY);

$listAnesthType = new CTypeAnesth();
$listAnesthType = $listAnesthType->loadGroupList();

$listPers = [
    "sagefemme"        => CPersonnel::loadListPers("sagefemme"),
    "aux_puericulture" => CPersonnel::loadListPers("aux_puericulture"),
    "aide_soignant"    => CPersonnel::loadListPers("aide_soignant"),
];

foreach ($affectations_operation as $affectation_personnel) {
    foreach ($listPers as $personnel_type => $personnels) {
        foreach ($personnels as $personnel) {
            if ($personnel) {
                if ($personnel->_id == $affectation_personnel->personnel_id) {
                    unset($listPers[$personnel_type][$personnel->_id]);
                }
            }
        }
    }
}

// Check end timings for graph type selected
$is_current_time = true;

if ($type == 'preop' && ($interv->fin_prepa_preop || $interv->entree_salle)) {
    $is_current_time = false;
} elseif ($type == 'sspi' && ($interv->sortie_reveil_reel || $interv->sortie_reveil_possible)) {
    $is_current_time = false;
} elseif (in_array($type, ['perop', 'partogramme']) && ($interv->sortie_salle || $interv->fin_op)) {
    $is_current_time = false;
}

$prescription_installed = CModule::getActive("dPprescription");

// Lock add new or edit event
$limit_date_min = ($type == 'sspi') && $interv->entree_reveil ? $interv->entree_reveil : null;

// Frequency automatic (configuration)
$frequency_automatic_graph = CAppUI::gconf("monitoringPatient General frequency_automatic_graph");

$delai_administration_futur = CAppUI::conf(
    'planSoins Perop delai_administration_futur',
    $interv->getContextConfigMonitoring()
);

// La configuration peut être vide : on force une valeur à 0
if ($delai_administration_futur === null || $delai_administration_futur === '') {
    $delai_administration_futur = 0;
}

$date_max_adm = CMbDT::dateTime('+ ' . $delai_administration_futur . ' hours');

// Création du template
$smarty = new CSmartyDP("modules/dPsalleOp");
$smarty->assign("consult_anesth", $consult_anesth);
$smarty->assign("readonly", $readonly);
$smarty->assign("interv", $interv);
$smarty->assign("concentrators", $concentrators);
$smarty->assign("all_concentrators", $all_concentrators);
$smarty->assign("session", $session);
$smarty->assign("pack", $pack);
$smarty->assign("type", $type);
$smarty->assign("show_cormack", $show_cormack);
$smarty->assign("graphs", $graphs);
$smarty->assign("time_debut_op", $time_debut_op);
$smarty->assign("time_fin_op", $time_fin_op);
$smarty->assign("yaxes_count", $yaxes_count);
$smarty->assign("time_min", $time_min);
$smarty->assign("time_max", $time_max);
$smarty->assign("time_debut_op_iso", $time_debut_op_iso);
$smarty->assign("time_fin_op_iso", $time_fin_op_iso);
$smarty->assign("graph_packs", $graph_packs);
$smarty->assign("nb_minutes", CMbDT::minutesRelative($time_debut_op_iso, $time_fin_op_iso));
$smarty->assign("timeline_options", $timeline_options);
$smarty->assign("display_current_time", $display_current_time);
$smarty->assign("modif_operation", $modif_operation);
$smarty->assign("listAnesths", $listAnesths);
$smarty->assign("listAnesthType", $listAnesthType);
$smarty->assign("prescription_id", $prescription ? $prescription->_id : null);
$smarty->assign("force", $force);
$smarty->assign("listPers", $listPers);
$smarty->assign("affectations_operation", $affectations_operation);
$smarty->assign("is_current_time", $is_current_time);
$smarty->assign("select_main_pack", $select_main_pack);
$smarty->assign("current_concentrator", $current_concentrator);
$smarty->assign("prescription_installed", $prescription_installed);
$smarty->assign("can_prescribe", $prescription_installed ? CPrescription::canPrescribePerop() : false);
$smarty->assign("can_adm", $prescription_installed ? CPrescription::canAdmPerop() : false);
$smarty->assign("limit_date_min", $limit_date_min);
$smarty->assign("parto", $parto);
$smarty->assign("isDossierPerinatal", $isDossierPerinatal);
$smarty->assign("frequency_automatic_graph", $frequency_automatic_graph);
$smarty->assign('date_max_adm', $date_max_adm);
$smarty->assign('hide_infos', ($hide_infos == 1) ? 0 : 1);
$smarty->assign("full_url_api", $full_url_api);
$smarty->display("inc_vw_surveillance_perop");
