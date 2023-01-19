<?php
/**
 * @package Mediboard\Soins
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
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\Handlers\Facades\HandlerManager;
use Ox\Interop\Imeds\CImeds;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CAffectationUserService;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;

CCanDo::checkRead();

$date      = CView::get("date", "date default|now", true);
$list_only = CView::get("list_only", "bool default|0");
if ($list_only) {
    $service_id   = CView::get("service_id", "ref class|CService", true);
    $services_ids = [$service_id];
} else {
    $services_ids = CView::get("services_ids", "str", true);
    $services_ids = CService::getServicesIdsPref($services_ids);
}

CView::checkin();

$group = CGroups::loadCurrent();

$alert_handler = HandlerManager::isObjectHandlerActive('CPrescriptionAlerteHandler');

// Récupération de la liste des services
$service = new CService();
if (!$list_only) {
    $where              = [];
    $where["externe"]   = "= '0'";
    $where["cancelled"] = "= '0'";
    $services           = $service->loadListWithPerms(PERM_READ, $where);
}
$elts_colonne_regime = CAppUI::gconf("soins UserSejour elts_colonne_regime");
$id_elts_regime      = [];
foreach (explode("|", $elts_colonne_regime) as $_elt_regime) {
    $explode_elt_regime = explode(":", $_elt_regime);
    $id_elts_regime[]   = $explode_elt_regime[0];
}

$elts_colonne_jeun = CAppUI::gconf("soins UserSejour elts_colonne_jeun");
$id_elts_jeun      = [];
foreach (explode("|", $elts_colonne_jeun) as $_elt_jeun) {
    $explode_elt_jeun = explode(":", $_elt_jeun);
    $id_elts_jeun[]   = $explode_elt_jeun[0];
}

$sejours = [];
if ($services_ids) {
    $order = "affectation.service_id, ISNULL(chambre.rank), chambre.rank, chambre.nom, ISNULL(lit.rank), lit.rank, lit.nom";
    // Chargement des sejours pour le service selectionné
    $ljoin                           = [];
    $ljoin["lit"]                    = "affectation.lit_id = lit.lit_id";
    $ljoin["chambre"]                = "lit.chambre_id = chambre.chambre_id";
    $ljoin["sejour"]                 = "affectation.sejour_id = sejour.sejour_id";
    $where                           = [];
    $where["affectation.sejour_id"]  = "!= 0";
    $where["sejour.group_id"]        = "= '$group->_id'";
    $where["sejour.annule"]          = " = '0'";
    $where["affectation.entree"]     = "<= '$date 23:59:59'";
    $where["affectation.sortie"]     = ">= '$date 00:00:00'";
    $where["affectation.service_id"] = CSQLDataSource::prepareIn($services_ids);

    $affectation  = new CAffectation();
    $affectations = $affectation->loadList($where, $order, null, null, $ljoin, null, null, false);
    CAffectation::massUpdateView($affectations);

    /** @var CSejour[] $sejours */
    $all_sejours = CStoredObject::massLoadFwdRef($affectations, "sejour_id", null, true);

    /* @var CAffectation[] $affectations */
    foreach ($affectations as $_affectation) {
        $_affectation->loadRefsAffectations();
        $_affectation->loadRefLit()->loadCompleteView();
        $_affectation->_view = $_affectation->_ref_lit->_view;

        $sejour = $_affectation->loadRefSejour(1);

        if (isset($sejours[$sejour->_id])) {
            continue;
        }

        $sejours[$sejour->_id] = $sejour;

        $sejour->_ref_curr_affectation = $_affectation;

        if (CModule::getActive("hotellerie")) {
            $cleanup = $_affectation->_ref_lit->loadLastCleanup($date);
            $cleanup->getColorStatusCleanup(true);
        }
    }
}

$type_view_demande_particuliere = CAppUI::pref("type_view_demande_particuliere");
$degre                          = $type_view_demande_particuliere == "last_macro" ? null : "low";
if (in_array($type_view_demande_particuliere, ["trans_hight", "macro_hight"])) {
    $degre = "high";
}
$cible_importante = in_array($type_view_demande_particuliere, ["last_macro", "macro_low", "macro_hight"]) ? true : false;
$important        = $cible_importante ? false : true;

/* @var CPatient[] $patients */
$patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
CPatient::massLoadIPP($patients);
CStoredObject::massLoadBackRefs($patients, "dossier_medical");

CStoredObject::massLoadFwdRef($sejours, "praticien_id");
CStoredObject::massCountBackRefs($sejours, "tasks", ["realise" => "= '0'"], [], "taches_non_realisees");
CStoredObject::massLoadBackRefs($sejours, "dossier_medical");
CSejour::massLoadSurrAffectation($sejours);
CSejour::massLoadBackRefs($sejours, "user_sejour");
CSejour::massLoadNDA($sejours);

$dossiers = [];
foreach ($sejours as $sejour) {
    $sejour->loadRefPatient();
    $sejour->loadRefPraticien();
    $sejour->checkDaysRelative($date);
    $sejour->loadJourOp($date);
    $sejour->countAlertsNotHandled("medium", "observation");

    $sejour->loadRefPrescriptionSejour();
    $prescription = $sejour->_ref_prescription_sejour;
    $prescription->loadRefsLinesRegime($id_elts_regime);
    $prescription->loadRefsLinesJeun($id_elts_jeun);
    if ($prescription->_id) {
        $prescription->loadJourOp($date);
        foreach ($prescription->_ref_object->_ref_operations as $_operation) {
            if ($_operation->_ref_anesth) {
                $_operation->_ref_anesth->loadRefFunction();
            }
        }
    }

    if ($alert_handler) {
        $prescription->_count_alertes = $prescription->countAlertsNotHandled("medium");
        $prescription->_count_alertes = $prescription->countAlertsNotHandled("high");
    }

    CPrescription::massAlertConfirmation($prescription);

    // Chargement des taches non effectuées
    $sejour->_count_tasks = $sejour->_count["taches_non_realisees"];

    $sejour->_count_tasks_not_created = 0;
    $sejour->_ref_tasks_not_created   = [];

    if ($prescription->_id) {
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

        if (HandlerManager::isObjectHandlerActive('CPrescriptionAlerteHandler')) {
            $prescription->_count_alertes  = $prescription->countAlertsNotHandled("medium");
            $prescription->_count_urgences = $prescription->countAlertsNotHandled("high");
        } else {
            $prescription->countFastRecentModif();
        }
    }

    // Chargement des transmissions sur des cibles importantes
    $sejour->loadRefsTransmissions($cible_importante, $important, false, 1, null, $degre);
    $sejour->loadRefDossierMedical();


    $patient = $sejour->_ref_patient;
    $patient->loadRefPhotoIdentite();
    $patient->loadRefDossierMedical(false);
    $dossier_medical = $patient->_ref_dossier_medical;
    if ($dossier_medical->_id) {
        $dossiers[$dossier_medical->_id] = $dossier_medical;
    }
    $sejour->loadRefsUserSejour(null, $date, 'affectations');
    $sejour->loadRefsMacrocible("count");
}

// Récupération des identifiants des dossiers médicaux
$dossiers_id = CMbArray::pluck($sejours, "_ref_patient", "_ref_dossier_medical", "_id");

// Suppressions des dossiers médicaux inexistants
CMbArray::removeValue("", $dossiers);

$_counts_allergie   = CDossierMedical::massCountAllergies($dossiers_id);
$_counts_antecedent = CDossierMedical::massCountAntecedents($dossiers_id);

/* @var CDossierMedical[] $dossiers */
foreach ($dossiers as $_dossier) {
    $_dossier->_count_allergies   = array_key_exists($_dossier->_id, $_counts_allergie) ? $_counts_allergie[$_dossier->_id] : 0;
    $_dossier->_count_antecedents = array_key_exists($_dossier->_id, $_counts_antecedent) ? $_counts_antecedent[$_dossier->_id] : 0;
}

$sejours_by_service_id   = [];
$responsables_jour_by_id = [];
foreach ($sejours as $_sejour) {
    if ($_sejour->_ref_curr_affectation) {
        $sejours_by_service_id[$_sejour->_ref_curr_affectation->service_id][] = $_sejour;
    }
}

//Chargement des responsables du jour
$affectation       = new CAffectationUserService();
$responsables_jour = $affectation->loadResponsablesJour($services_ids, $date);

$smarty = new CSmartyDP();

$smarty->assign("sejours_by_service_id", $sejours_by_service_id);
$smarty->assign("isImedsInstalled", (CModule::getActive("dPImeds") && CImeds::getTagCIDC(CGroups::loadCurrent())));
$smarty->assign("date", $date);
$smarty->assign("date_after", CMbDT::date("+1 day", $date));

$smarty->assign("services_ids", array_map('intval', array_values($services_ids)));

if (!$list_only) {
    $smarty->assign("responsables_jour", $responsables_jour);
    $smarty->assign("date_before", CMbDT::date("-1 day", $date));
    $smarty->assign("services", $services);

    $smarty->display("vw_affectations_soignant");
} else {
    $smarty->assign("sejours", $sejours);
    $smarty->assign("service_id", $service_id);
    $smarty->display("inc_affectations_soignant");
}
