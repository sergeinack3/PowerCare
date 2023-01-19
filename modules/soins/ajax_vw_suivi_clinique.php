<?php

/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CTransmissionMedicale;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\Soins\CSejourTask;

CCanDo::checkRead();

$sejour_id = CView::get("sejour_id", "ref class|CSejour");

CView::checkin();

$group = CGroups::loadCurrent();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadRefCurrAffectation();
$sejour->_ref_curr_affectation->loadView();
$sejour->canRead();
$patient = $sejour->loadRelPatient();
$patient->loadRefsCorrespondantsPatient(null, true);
$patient->loadRefsCorrespondants();
$patient->loadRefPhotoIdentite();
$patient->loadRefsNotes();
$patient->loadRefLatestConstantes(null, ["poids", "taille"]);
$patient->loadRefLastDirectiveAnticipee();

if ($patient->_ref_last_directive_anticipee->_id) {
    $patient->_ref_last_directive_anticipee->loadRefDetenteur();
}

$dossier_medical = $patient->loadRefDossierMedical();

if ($dossier_medical->_id) {
    $dossier_medical->loadRefsAllergies();
    $dossier_medical->loadRefsAntecedents();
    $dossier_medical->countAntecedents();
    $dossier_medical->countAllergies();
}

$sejour->loadRefPraticien();
$sejour->loadRefsOperations();
$sejour->loadRefsConsultations();

// personne qui a autorisé la sortie
$sejour->loadRefConfirmeUser()->loadRefFunction();

$prescription_active = CModule::getActive("dPprescription");

// Gestion des macro-cible seulement si prescription disponible
$cible_importante  = $prescription_active;
$date_transmission = CAppUI::conf("soins synthese transmission_date_limit", $group->_guid) ? CMbDT::dateTime() : null;
$cibles            = $last_trans_cible = $users = $functions = [];
$sejour->loadSuiviMedical(
    null,
    null,
    $cibles,
    $last_trans_cible,
    null,
    $users,
    null,
    $functions,
    0,
    $date_transmission
);

$sejour->loadRefsObservations(true);
$sejour->loadRefsRDVExternes(["rdv_externe.statut" => " != 'annule'"]);
$sejour->loadRefsTasks();
$sejour->loadRefsNotes();

foreach ($sejour->_ref_tasks as $key => $_task) {
    if ($_task->realise) {
        unset($sejour->_ref_tasks[$key]);
        continue;
    }

    $_task->loadRefPrescriptionLineElement();
    $_task->setDateAndAuthor();
    $_task->loadRefAuthor();
    $_task->loadRefAuthorRealise();
}

CSejourTask::sortByDate($sejour->_ref_tasks);

// Tri des transmissions par catégorie
$transmissions = [];

foreach ($sejour->_ref_suivi_medical as $_suivi_by_key) {
    foreach ($_suivi_by_key as $key => $_trans) {
        if (!($_trans instanceof CTransmissionMedicale) || $_trans->degre != "high") {
            continue;
        }
        $_trans->loadRefUser()->loadRefFunction();
        $_trans->loadTargetObject();
        $_trans->calculCibles();
        $sort_key_pattern =
            "$_trans->_class $_trans->user_id $_trans->object_id $_trans->object_class $_trans->libelle_ATC";

        $sort_key = "$_trans->date $sort_key_pattern";

        $date_before     = CMbDT::dateTime("-1 SECOND", $_trans->date);
        $sort_key_before = "$date_before $sort_key_pattern";

        $date_after     = CMbDT::dateTime("+1 SECOND", $_trans->date);
        $sort_key_after = "$date_after $sort_key_pattern";

        // Aggrégation à -1 sec
        if (array_key_exists($sort_key_before, $transmissions)) {
            $sort_key = $sort_key_before;
        } elseif (array_key_exists($sort_key_after, $transmissions)) {
            // à +1 sec
            $sort_key = $sort_key_after;
        }

        if (!isset($transmissions[$sort_key])) {
            $transmissions[$sort_key] = ["data" => [], "action" => [], "result" => []];
        }
        if (!isset($transmissions[$sort_key][0])) {
            $transmissions[$sort_key][0] = $_trans;
        }
        $transmissions[$sort_key][$_trans->type][] = $_trans;
    }
}
krsort($transmissions);

$sejour->_ref_transmissions = $transmissions;

$sejour->loadRefsConsultAnesth();
$sejour->_ref_consult_anesth->loadRefConsultation();

$show_prescription = CAppUI::conf("soins synthese show_prescription", $group->_guid);
if ($prescription_active && $show_prescription) {
    $prescription_sejour = $sejour->loadRefPrescriptionSejour();
    $prescription_sejour->loadJourOp(CMbDT::date());
    // Chargement des lignes de prescriptions
    $prescription_sejour->loadRefsLinesMedComments();
    foreach ($prescription_sejour->_ref_lines_med_comments["med"] as $_line_med) {
        /**@var CPrescriptionLineMedicament $_line_med */
        $_line_med->updateAlerteAntibio();
    }
    $prescription_sejour->loadRefsLinesElementsComments();

    // Chargement des prescription_line_mixes
    $prescription_sejour->loadRefsPrescriptionLineMixes();

    foreach ($prescription_sejour->_ref_prescription_line_mixes as $curr_prescription_line_mix) {
        $curr_prescription_line_mix->loadRefsLines();

        $curr_prescription_line_mix->updateAlerteAntibio();

        $curr_prescription_line_mix->_compact_view = [];
        foreach ($curr_prescription_line_mix->_ref_lines as $_line) {
            if (!$_line->solvant) {
                $curr_prescription_line_mix->_compact_view[] = $_line->_ref_produit->ucd_view;
            }
        }
        if (count($curr_prescription_line_mix->_compact_view)) {
            $curr_prescription_line_mix->_compact_view = implode(", ", $curr_prescription_line_mix->_compact_view);
        } else {
            $curr_prescription_line_mix->_compact_view = "";
        }
    }
}

if ($prescription_active) {
    $date        = CMbDT::dateTime();
    $days_config = CAppUI::conf("dPprescription general nb_days_prescription_current", $group->_guid);
    $date_before = CMbDT::dateTime("-$days_config DAY", $date);
    $date_after  = CMbDT::dateTime("+$days_config DAY", $date);
}

CStoredObject::massLoadBackRefs($sejour->_ref_operations, "context_ref_brancardages");

foreach ($sejour->_ref_operations as $_operation) {
    $_operation->loadRefsFwd();
    $_operation->loadRefChirs();
    $_operation->loadRefBrancardage();
    $_operation->countAlertsNotHandled();
    $_operation->loadRefVisiteAnesth()->loadRefFunction();
    $_operation->loadRefsBrancardages();
}


CStoredObject::massLoadBackRefs($sejour->_ref_consultations, "context_ref_brancardages");

foreach ($sejour->_ref_consultations as $_consult) {
    $_consult->loadRefBrancardage();
    $_consult->loadRefsBrancardages();
}

$sejour->loadRefsObjectifsSoins();

$sejour->loadRefPrestation();
$sejour->loadRefsAutorisationsPermission();

if (CAppUI::conf("dPhospi prestations systeme_prestations", $sejour->loadRefEtablissement()) === "expert") {
    $sejour->loadLiaisonsForPrestation("all");
}

$smarty = new CSmartyDP();
$smarty->assign("sejour", $sejour);

if ($prescription_active) {
    $smarty->assign("date", $date);
    $smarty->assign("days_config", $days_config);
    $smarty->assign("date_before", $date_before);
    $smarty->assign("date_after", $date_after);
}
$smarty->assign("show_prescription", $show_prescription);
$smarty->display("inc_vw_suivi_clinique");
