<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\Handlers\Facades\HandlerManager;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Maternite\CNaissance;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;

CCanDo::checkRead();
$sejour_date_min    = CView::get("_date_min", "dateTime default|now", true);
$sejour_date_max    = CView::get("_date_max", "dateTime default|now", true);
$naissance_date_min = CView::get("_datetime_min", "dateTime", true);
$naissance_date_max = CView::get("_datetime_max", "dateTime", true);
$guthrie_date_min   = CView::get("date_guthrie_min", "date");
$guthrie_date_max   = CView::get("date_guthrie_max", "date");
$pediatre_id        = CView::get("pediatre_id", "ref class|CMediusers", true);
$services_ids       = CView::get("services_ids", "str", true);
$etat               = CView::get("state", "enum list|present|consult_pediatre|none default|present", true);
$order_col          = CView::get("order_col", "enum list|patient_id|naissance|nom default|nom");
$order_way          = CView::get("order_way", "enum list|ASC|DESC default|ASC");
$page               = CView::get("page", "num default|0");
$print              = CView::get("print", "bool default|0");

$services_ids = CService::getServicesIdsPref($services_ids);
CView::checkin();

if ($etat != 'consult_pediatre' && !$sejour_date_min && !$sejour_date_max) {
    $sejour_date_min = CMbDT::dateTime();
    $sejour_date_max = CMbDT::dateTime();
}

$group = CGroups::loadCurrent();

$ljoin = [
    "sejour"   => "sejour.sejour_id = naissance.sejour_enfant_id",
    "patients" => "patients.patient_id = sejour.patient_id",
    "examen_nouveau_ne" => "examen_nouveau_ne.naissance_id = naissance.naissance_id",
];

$order   = "naissance.num_naissance, patients.nom";
$groupby = "naissance.naissance_id";

// Bed sort
if ($order_col == "nom") {
    $ljoin["affectation"] = "affectation.sejour_id = sejour.sejour_id";
    $ljoin["lit"]         = "lit.lit_id = affectation.lit_id";
    $ljoin["chambre"]     = "chambre.chambre_id = lit.chambre_id";
}

// Birthdate sort
if ($order_col == "naissance") {
    $order = "naissance.date_time $order_way";
}

if ($order_col == "patient_id") {
    $order = "patients.nom $order_way, patients.prenom $order_way";
}

if (($sejour_date_min && $sejour_date_max) && (!$naissance_date_min && !$naissance_date_max)) {
    $where = [
        "sejour.entree" => "<= '$sejour_date_max'",
        "sejour.sortie" => ">= '$sejour_date_min'",
    ];
}

if ($naissance_date_min && $naissance_date_max) {
    $where["naissance.date_time"] = "BETWEEN '$naissance_date_min' AND '$naissance_date_max'";
}

if ($pediatre_id) {
    $where[] = "sejour.praticien_id = '$pediatre_id'";
}

$where["sejour.group_id"] = "= '$group->_id'";
$where["sejour.annule"]   = " = '0'";

$naissance = new CNaissance();
$ds = $naissance->getDS();

if ($etat == 'present') {
    $where["sejour.sortie_reelle"] = "IS NULL";
} elseif ($etat == 'consult_pediatre') {
    // Vidage des dates
    $sejour_date_min    = "";
    $sejour_date_max    = "";
    $naissance_date_min = "";
    $naissance_date_max = "";


    // J0: Jour de leur naissance entre 8 heures la veille à 8 le jour actuel.
    $date_min = CMbDT::date("- 1 day") . " 08:00:00";
    $date_max = CMbDT::date() . " 08:00:00";

    $where                        = [];
    $where["sejour.group_id"]     = " = '$group->_id'";
    $where["naissance.date_time"] = "BETWEEN '$date_min' AND '$date_max'";

    $naissances_j0 = $naissance->loadIds($where, $order, null, $groupby, $ljoin);

    // J3: Pour les bébés nés par voie basse.
    $where                           = [];
    $where["sejour.group_id"]        = " = '$group->_id'";
    $where["naissance.by_caesarean"] = " = '0' ";
    $where[]                         = "DATE_FORMAT(DATE_ADD(DATE(naissance.date_time), INTERVAL 3 DAY),'%d/%m/%Y') = DATE_FORMAT(DATE(NOW()),'%d/%m/%Y')";

    $naissances_j3 = $naissance->loadIds($where, $order, null, $groupby, $ljoin);

    // J4: Pour les bébés nés par césarienne.
    $where                           = [];
    $where["sejour.group_id"]        = " = '$group->_id'";
    $where["naissance.by_caesarean"] = " = '1' ";
    $where[]                         = "DATE_FORMAT(DATE_ADD(DATE(naissance.date_time), INTERVAL 4 DAY),'%d/%m/%Y') = DATE_FORMAT(DATE(NOW()),'%d/%m/%Y')";

    $naissances_j4 = $naissance->loadIds($where, $order, null, $groupby, $ljoin);

    $naissance_ids = array_merge($naissances_j0, $naissances_j3);
    $naissance_ids = array_merge($naissance_ids, $naissances_j4);
    $naissance_ids = array_unique($naissance_ids);

    $where = [
        "naissance.naissance_id" => $ds->prepareIn($naissance_ids),
    ];
}

$naissances = $naissance->loadList($where, $order, null, $groupby, $ljoin);

if ($guthrie_date_min && $guthrie_date_max) {
    $naissances = $naissance->loadNaissancesByGuthrieDateFilter($naissances, $guthrie_date_min, $guthrie_date_max);
}

$sejours = CStoredObject::massLoadFwdRef($naissances, "sejour_enfant_id");
$affectations = CStoredObject::massLoadBackRefs($sejours, "affectations", "sortie DESC");
foreach ($naissances as $key => $_naissance) {
    $sejour = $_naissance->loadRefSejourEnfant();
    $sejour->loadRefsAffectations();
    if (
        !in_array(
            $sejour->_ref_last_affectation->service_id,
            $services_ids
        )
        && $sejour->_ref_last_affectation->service_id !== null
    ) {
        unset($naissances[$key]);
        unset($sejours[$sejour->_id]);
    }
    $sejour->_ref_last_affectation;
}

$total = count($naissances);

CStoredObject::massLoadBackRefs($naissances, "exams_bebe");
CStoredObject::massLoadFwdRef($sejours, "patient_id");
CSejour::massLoadNDA($sejours);
CStoredObject::massLoadFwdRef($affectations, "service_id");
$lits     = CStoredObject::massLoadFwdRef($affectations, "lit_id");
$chambres = CStoredObject::massLoadFwdRef($lits, "chambre_id");
$services = CStoredObject::massLoadFwdRef($chambres, "service_id");

$sejours_maman = CStoredObject::massLoadFwdRef($naissances, "sejour_maman_id");
CStoredObject::massLoadFwdRef($sejours_maman, "grossesse_id");

CStoredObject::massLoadBackRefs($sejours, "prescriptions", null, ["type" => "= 'sejour'"]);

$alert_handler = HandlerManager::isObjectHandlerActive('CPrescriptionAlerteHandler');

if ($order_col == "nom") {
    foreach ($naissances as $_naissance) {
        $sejour       = $_naissance->loadRefSejourEnfant();
        $affectations = $sejour->loadRefsAffectations();
        $sejour->_ref_last_affectation->loadRefLit();
        $sejour->_ref_last_affectation->_ref_lit->loadCompleteView();
    }

    $lits       = CMbArray::pluck($naissances, "_ref_sejour_enfant", "_ref_last_affectation", "_ref_lit");
    $sorter_lit = CMbArray::pluck($lits, "_view");

    array_multisort(
        $sorter_lit,
        constant("SORT_$order_way"),
        $naissances
    );
}

$services_selected = [];
$sejours_np        = [];

/** @var CNaissance $_naissance */
foreach ($naissances as $_naissance) {
    $sejour       = $_naissance->loadRefSejourEnfant();
    $affectations = $sejour->loadRefsAffectations();
    $sejour->_ref_last_affectation->loadRefLit();
    $sejour->_ref_last_affectation->_ref_lit->loadCompleteView();
    $prescription = $sejour->loadRefPrescriptionSejour();
    $sejour->countTasks();

    $examens     = $_naissance->loadRefsExamenNouveauNe();
    $last_examen = $_naissance->loadRefLastExamenNouveauNe();
    $last_examen->getOEAExam();
    $last_examen->checkGuthrieExam($sejour->_id);
    $last_examen->loadRefGuthrieUser();

    // Passage dans un service Néonatalogie
    $_naissance->_service_neonatalogie = false;
    foreach ($affectations as $_affectation) {
        $service = $_affectation->loadRefService();
        if ($service->neonatalogie) {
            $_naissance->_service_neonatalogie = true;
        }
    }

    $sejour->_count_tasks_not_created = 0;

    if ($prescription->_id) {
        CPrescription::massAlertConfirmation($prescription);

        if (!$alert_handler) {
            $prescription->countFastRecentModif();
            $prescription->countUrgence(CMbDT::date($sejour_date_min));
        }

        // Chargement des lignes non associées à des taches
        $where                                         = [];
        $ljoin                                         = [];
        $ljoin["element_prescription"]                 = "prescription_line_element.element_prescription_id = element_prescription.element_prescription_id";
        $ljoin["sejour_task"]                          = "sejour_task.prescription_line_element_id = prescription_line_element.prescription_line_element_id";
        $where["prescription_id"]                      = " = '$prescription->_id'";
        $where["element_prescription.rdv"]             = " = '1'";
        $where["prescription_line_element.date_arret"] = " IS NULL";
        $where["active"]                               = " = '1'";
        $where[]                                       = "sejour_task.sejour_task_id IS NULL";
        $where["child_id"]                             = " IS NULL";

        $line_element                     = new CPrescriptionLineElement();
        $sejour->_count_tasks_not_created = $line_element->countList($where, null, $ljoin);
    }

    $patient = $sejour->loadRefPatient();
    $patient->getFirstConstantes();

    // Mère
    $sejour_mere = $_naissance->loadRefSejourMaman();
    $grossesse   = $sejour_mere->loadRefGrossesse();
    $grossesse->loadRefDossierPerinat();

    if (!$sejour->_ref_last_affectation->service_id) {
        if ($etat == 'consult_pediatre') {
            if (in_array($_naissance->_id, $naissances_j0)) {
                $_naissance->_consult_pediatre = "J0";
            } elseif (in_array($_naissance->_id, $naissances_j3)) {
                $_naissance->_consult_pediatre = "J3";
            } elseif (in_array($_naissance->_id, $naissances_j4)) {
                $_naissance->_consult_pediatre = "J4";
            }
        }

        $sejours_np[$sejour->_id] = $_naissance;
    } elseif ($sejour->_ref_last_affectation->service_id) {
        if ($etat == 'consult_pediatre') {
            if (in_array($_naissance->_id, $naissances_j0)) {
                $_naissance->_consult_pediatre = "J0";
            } elseif (in_array($_naissance->_id, $naissances_j3)) {
                $_naissance->_consult_pediatre = "J3";
            } elseif (in_array($_naissance->_id, $naissances_j4)) {
                $_naissance->_consult_pediatre = "J4";
            }
        }

        $services_selected[$sejour->_ref_last_affectation->_ref_service->nom][$_naissance->_id] = $_naissance;
    }

    if (count($sejours_np)) {
        $services_selected["NP"] = $sejours_np;
    }
}

if ($alert_handler) {
    CPrescription::massCountAlertsNotHandled(CMbArray::pluck($sejours, "_ref_prescription_sejour"));
    CPrescription::massCountAlertsNotHandled(CMbArray::pluck($sejours, "_ref_prescription_sejour"), "high");
}

ksort($services_selected);
//Non placés en fin de liste
if (array_key_exists("NP", $services_selected)) {
    $np = $services_selected['NP'];
    unset($services_selected['NP']);
    $services_selected['NP'] = $np;
}

// Récupération de la liste des services
$where              = [];
$where["externe"]   = "= '0'";
$where["cancelled"] = "= '0'";

$service  = new CService();
$services = $service->loadGroupList($where);

$smarty = new CSmartyDP();
$smarty->assign("total", $total);
$smarty->assign("etat", $etat);
$smarty->assign("order_col", $order_col);
$smarty->assign("order_way", $order_way);
$smarty->assign("services_selected", $services_selected);
$smarty->assign("services", $services);
if (!$print) {
    $smarty->display("vw_list_naissances");
} else {
    $smarty->display("vw_print_naissances");
}

