<?php

/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\MonitoringPatient\SupervisionGraph;
use Ox\Mediboard\Mpm\CPrescriptionLineMix;
use Ox\Mediboard\ObservationResult\CObservationResultSet;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanSoins\CAdministration;
use Ox\Mediboard\Prescription\CAdministrationDM;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\SalleOp\CAnesthPerop;
use Ox\Mediboard\SalleOp\CDailyCheckItem;
use Ox\Mediboard\SalleOp\CDailyCheckList;

global $can;
$can->read |= CModule::getActive("dPbloc")->_can->read;
$can->needsRead();

$operation_id = CView::get('operation_id', 'ref class|COperation', true);
$see_unit     = CView::get('see_unit', 'bool default|1');
$surveillance = CView::get('surveillance', 'enum list|preop|perop|sspi');
CView::checkin();

$operation = new COperation();
$operation->load($operation_id);

CAccessMedicalData::logAccess($operation);

$operation->loadRefsAnesthPerops();
$operation->loadRefsFwd();
$operation->loadRefsActesCCAM();

foreach ($operation->_ref_actes_ccam as $acte) {
    $acte->loadRefsFwd();
}
$operation->loadAffectationsPersonnel();
$operation->guessActesAssociation();

$operation->loadRefSortieLocker()->loadRefFunction();

$operation->loadRefsMaterielsOperatoires(true, false, true);

foreach ($operation->_refs_materiels_operatoires as $_materiel_operatoire) {
    $_materiel_operatoire->loadRelatedProduct();
    $_materiel_operatoire->loadRefsConsommations();

    foreach ($_materiel_operatoire->_ref_consommations as $_consommation) {
        $_consommation->loadRefUser();
        $_consommation->loadRefLot();
        $_consommation->_ref_lot->loadRefOrderItem();
        $_consommation->_ref_lot->_ref_order_item->loadReference();
    }
}

$sejour = $operation->_ref_sejour;
$sejour->loadRefsFwd();
$sejour->loadRefPrescriptionSejour();

$dmi_active = CModule::getActive("dmi") && CAppUI::gconf("dmi CDM active");

switch ($surveillance) {
    case 'preop':
        $datetime_min = $operation->entree_bloc;
        $datetime_max = $operation->entree_salle;
        break;
    case 'perop':
        $datetime_min = $operation->entree_salle;
        $datetime_max = $operation->sortie_salle;
        break;
    case 'sspi':
        $datetime_min = $operation->entree_reveil;
        $datetime_max = $operation->sortie_reveil_reel ?: $operation->sortie_reveil_possible;
        break;
    default:
        $datetime_max = null;
        $datetime_min = null;
}

/** @var CAdministration[] $administrations */
$administrations    = [];
$administrations_dm = [];
$prescription_id    = null;
if (CModule::getActive("dPprescription")) {
    $prescription_id = $sejour->_ref_prescription_sejour->_id;
    if ($prescription_id) {
        $administrations = CAdministration::getPerop(
            $prescription_id,
            false,
            $operation_id,
            $datetime_min,
            $datetime_max
        );

        if ($dmi_active) {
            $administrations_dm = CAdministrationDM::getPerop(
                $prescription_id,
                false,
                intval($operation_id),
                $datetime_min,
                $datetime_max
            );
        }
    }
}

// Chargement des constantes saisies durant l'intervention
$whereConst             = [];
$whereConst["datetime"] = "BETWEEN '$operation->_datetime_reel' AND '$operation->_datetime_reel_fin'";

$sejour->loadListConstantesMedicales($whereConst);

// Tri des gestes et administrations perop par ordre chronologique
$perops = [];
foreach ($administrations as $_administration) {
    $_administration->loadRefsFwd();
    $perops[$_administration->dateTime][$_administration->_guid] = $_administration;
}

$praticiens_dm = CStoredObject::massLoadFwdRef($administrations_dm, "praticien_id");
CStoredObject::massLoadFwdRef($praticiens_dm, "function_id");

foreach ($administrations_dm as $_administration_dm) {
    $_administration_dm->loadRefPraticien();
    $_administration_dm->loadRefProduct();
    $_administration_dm->loadRefProductOrderItemReception();
    $perops[$_administration_dm->date][$_administration_dm->_guid] = $_administration_dm;
}

// filtrer
$praticiens_dm = CStoredObject::massLoadFwdRef($operation->_ref_anesth_perops, "user_id");
/** @var CAnesthPerop */
foreach ($operation->_ref_anesth_perops as $_perop) {
    if (!$surveillance || ($_perop->datetime >= $datetime_min && $_perop->datetime >= $datetime_max)) {
        $_perop->loadRefUser();
        $perops[$_perop->datetime][$_perop->_guid] = $_perop;
    }
}

if ($prescription_id && CPrescription::isMPMActive()) {
    // Chargements des perfusions pour afficher les poses et les retraits
    $prescription_line_mix                  = new CPrescriptionLineMix();
    $prescription_line_mix->prescription_id = $prescription_id;
    $prescription_line_mix->operation_id    = $operation_id;
    $prescription_line_mix->perop           = 1;
    /** @var CPrescriptionLineMix[] $mixes */
    $mixes = $prescription_line_mix->loadMatchingList();

    CStoredObject::massLoadFwdRef($mixes, "praticien_id");

    // filtrer
    foreach ($mixes as $_mix) {
        $_mix->loadRefPraticien();
        $_mix->loadRefsLines();
        if (
            $_mix->date_pose
            && $_mix->time_pose
            && (!$surveillance || ($_mix->_pose >= $datetime_min && $_mix->_pose <= $datetime_max))
        ) {
            $perops[$_mix->_pose][$_mix->_guid] = $_mix;
        }
        if (
            $_mix->date_retrait && $_mix->time_retrait
            && (!$surveillance || ($_mix->_retrait >= $datetime_min && $_mix->_retrait <= $datetime_max))
        ) {
            $perops[$_mix->_retrait][$_mix->_guid] = $_mix;
        }
    }
}
ksort($perops);

$supervision_data      = [];
$constantes            = [];
$constantes_names_list = [];

if (CModule::getActive("monitoringBloc") && CAppUI::gconf("monitoringBloc general active_graph_supervision")) {
    if (($surveillance === 'preop' || !$surveillance) && $operation->graph_pack_preop_id) {
        /** @var CObservationResultSet[] $list_obr */
        // filtrer
        [$list, $grid, $graphs, $labels, $list_obr] = SupervisionGraph::getChronological(
            $operation,
            $operation->graph_pack_preop_id,
            false,
            $datetime_min,
            $datetime_max
        );

        foreach ($list_obr as $_obr) {
            $_obr->loadFirstLog()->loadRefUser()->loadRefMediuser()->loadRefFunction();
        }

        $supervision_data["preop"] = [
            "grid"   => $grid,
            "labels" => $labels,
            "list"   => $list_obr,
        ];
    }

    if (($surveillance === 'perop' || !$surveillance) && $operation->graph_pack_id) {
        /** @var CObservationResultSet[] $list_obr */
        [$list, $grid, $graphs, $labels, $list_obr] = SupervisionGraph::getChronological(
            $operation,
            $operation->graph_pack_id,
            false,
            $datetime_min,
            $datetime_max
        );

        foreach ($list_obr as $_obr) {
            $_obr->loadFirstLog()->loadRefUser()->loadRefMediuser()->loadRefFunction();
        }

        $supervision_data["perop"] = [
            "grid"   => $grid,
            "labels" => $labels,
            "list"   => $list_obr,
        ];
    }

    if (($surveillance === 'sspi' || !$surveillance) && $operation->graph_pack_sspi_id) {
        /** @var CObservationResultSet[] $list_obr */
        [$list, $grid, $graphs, $labels, $list_obr] = SupervisionGraph::getChronological(
            $operation,
            $operation->graph_pack_sspi_id,
            false,
            $datetime_min,
            $datetime_max
        );

        foreach ($list_obr as $_obr) {
            $_obr->loadFirstLog()->loadRefUser()->loadRefMediuser()->loadRefFunction();
        }

        $supervision_data["sspi"] = [
            "grid"   => $grid,
            "labels" => $labels,
            "list"   => $list_obr,
        ];
    }

    /* Add the constants from the concentrator */
    if ($surveillance) {
        $where = [
            'patient_id'    => " = {$operation->loadRefPatient()->_id}",
            'context_class' => " = '{$operation->loadRefSejour()->_class}'",
            'context_id'    => " = {$operation->_ref_sejour->_id}",
        ];

        $where['origin'] = " = '{$surveillance}'";

        if ($datetime_min) {
            $where[] = "datetime >= '{$datetime_min}'";
        }
        if ($datetime_max) {
            $where[] = "datetime <= '{$datetime_max}'";
        }

        $constantes = (new CConstantesMedicales())->loadList($where, 'datetime ASC');

        foreach ($constantes as $constante) {
            foreach (CConstantesMedicales::$list_constantes as $_constant_name => $_constant_param) {
                if (!in_array($_constant_name, $constantes_names_list) && !is_null($constante->$_constant_name)) {
                    $constantes_names_list[] = $_constant_name;
                }
            }
        }
    }
}

if (CAppUI::gconf('dPsalleOp timings use_garrot') && CAppUI::gconf('dPsalleOp COperation garrots_multiples')) {
    $operation->loadGarrots();
}

//Chargement des checklist validées à imprimer
/** @var CDailyCheckList[] $check_lists */
$check_lists = $operation->loadBackRefs("check_lists", "date");
foreach ($check_lists as $_check_list_id => $_check_list) {
    // Remove check lists not signed
    if (!$_check_list->validator_id) {
        unset($operation->_back["check_lists"][$_check_list_id]);
        unset($check_lists[$_check_list_id]);
        continue;
    }
    $_check_list->loadItemTypes();
    $_check_list->loadRefListType();
    $_check_list->loadBackRefs('items', "daily_check_item_id");
    foreach ($_check_list->_back['items'] as $_item) {
        /* @var CDailyCheckItem $_item */
        $_item->loadRefsFwd();
    }
}

$patient        = $operation->_ref_sejour->_ref_patient;
$patient_insnir = $patient->loadRefPatientINSNIR();
$patient_insnir->createDatamatrix($patient_insnir->createDataForDatamatrix());

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("patient", $patient);
$smarty->assign("operation", $operation);
$smarty->assign("perops", $perops);
$smarty->assign("supervision_data", $supervision_data);
$smarty->assign("see_unit", $see_unit);
$smarty->assign('surveillance', $surveillance);
$smarty->assign('constantes', $constantes);
$smarty->assign('constantes_names', $constantes_names_list);
$smarty->display("print_feuille_bloc");
