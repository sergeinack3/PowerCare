<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$object_guid = CView::get("object_guid", "str");
$sejour_id   = CView::get("sejour_id", "ref class|CSejour");
CView::checkin();

CAccessMedicalData::logAccess("CSejour-$sejour_id");

$object = CMbObject::loadFromGuid($object_guid);
$group  = CGroups::loadCurrent();

$service = $object instanceof CService ? $object : null;
$bloc    = $object instanceof CBlocOperatoire ? $object : null;

if ($service && $service->_id) {
    if ($sejour_id) {
        $affectation            = new CAffectation();
        $affectation->sejour_id = $sejour_id;

        $affectation->loadMatchingObject();
        $affectations = [$affectation];
    } else {
        $chambres = $service->loadRefsChambres(false);

        // Récupération des affectations en cours
        $affectation = new CAffectation();

        $where = [
            "affectation.service_id" => "= '$service->_id'",
            "affectation.sortie"     => ">= '" . CMbDT::dateTime() . "'",
            "affectation.sejour_id"  => "IS NOT NULL",
            "affectation.lit_id"     => "IS NOT NULL",
            "sejour.sortie_reelle"   => "IS NULL",
        ];

        $ljoin = [
            "sejour" => "sejour.sejour_id = affectation.sejour_id",
        ];

        $affectations = $affectation->loadList($where, "parent_affectation_id", null, null, $ljoin);
        CStoredObject::massLoadFwdRef($affectations, 'lit_id');
    }

    $ljoin_consult = ["plageconsult" => "plageconsult.plageconsult_id = consultation.plageconsult_id"];

    $sejours = CStoredObject::massLoadFwdRef($affectations, "sejour_id");

    $patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
    CStoredObject::massLoadBackRefs($patients, "bmr_bhre");
    CStoredObject::massLoadFwdRef($sejours, "praticien_id");
    $naissances    = CStoredObject::massLoadBackRefs($sejours, "naissance");
    $sejours_maman = CStoredObject::massLoadFwdRef($naissances, "sejour_maman_id");
    $consultations = CStoredObject::massLoadBackRefs(
        $sejours,
        "consultations",
        "date DESC, heure DESC",
        null,
        $ljoin_consult
    );
    CStoredObject::massLoadFwdRef($consultations, "plageconsult_id");
    CStoredObject::massLoadBackRefs($sejours_maman, "operations", "date");

    CPatient::massLoadIPP($patients);
    CPatient::massCountPhotoIdentite($patients);

    $sejours_chambre = [];

    $affectations_mother = [];
    $affectations_child  = [];

    // Rassembler les affectations des mamans et des bébés
    foreach ($affectations as $_affectation) {
        if ($_affectation->parent_affectation_id) {
            if (!isset($affectations_child[$_affectation->parent_affectation_id])) {
                $affectations_child[$_affectation->parent_affectation_id] = [];
            }
            $affectations_child[$_affectation->parent_affectation_id][] = $_affectation;
            continue;
        }

        $affectations_mother[$_affectation->_id] = $_affectation;
    }

    $affectations_gather = [];

    // Cas des bébés présents sans les mamans
    foreach ($affectations_child as $_affectation_mother_id => $_affectations_child_by_parent) {
        if (isset($affectations_mother[$_affectation_mother_id])) {
            continue;
        }
        foreach ($_affectations_child_by_parent as $_affectation_child) {
            $affectations_gather[$_affectation_child->_id] = $_affectation_child;
        }
        unset($affectations_child[$_affectation_mother_id]);
    }

    foreach ($affectations_mother as $_affectation_mother) {
        $affectations_gather[$_affectation_mother->_id] = $_affectation_mother;

        if (isset($affectations_child[$_affectation_mother->_id])) {
            foreach ($affectations_child[$_affectation_mother->_id] as $_affectation_child) {
                $affectations_gather[$_affectation_child->_id] = $_affectation_child;
            }
        }
    }

    $affectations = $affectations_gather;

    foreach ($affectations as $_affectation) {
        $_affectation->loadRefLit();

        /** @var CSejour $_sejour */
        $_sejour = $sejours[$_affectation->sejour_id];

        $_sejour->loadRefPatient()->loadRefPhotoIdentite();
        $_sejour->_ref_patient->updateBMRBHReStatus($_sejour);
        $_sejour->loadRefPraticien()->loadRefFunction();
        $_sejour->loadRefsConsultations();
        $_sejour->_ref_last_consult->loadRefPlageConsult();
        $naissance = $_sejour->loadRefNaissance();

        if ($naissance->_id) {
            $naissance->loadRefSejourMaman()->loadRefsOperations();
        }

        $_affectation->_ref_sejour                              = $_sejour;
        $_sejour->_ref_curr_affectation                         = $_affectation;
        $sejours_chambre[$_affectation->_ref_lit->chambre_id][] = $_sejour;
    }

    if ($sejour_id) {
        $affectation = reset($affectations);

        $_sejour = $affectation->_ref_sejour;

        // Création du template
        $smarty = new CSmartyDP();

        $smarty->assign("_sejour", $_sejour);
        $smarty->assign("_zone", $_affectation->_ref_lit->_ref_chambre);
        $smarty->assign("with_div", 0);

        $smarty->display("inc_patient_placement.tpl");

        return;
    }

    $grille      = [];
    $lits_occupe = 0;

    $exist_plan = count($sejours_chambre);

    CService::vueTopologie($chambres, $grille, $listSejours, $sejours_chambre, $lits_occupe);
} elseif ($bloc && $bloc->_id) {
    $salles = $bloc->loadRefsSalles(["actif" => "= '1'"]);

    $date_min = CMbDT::dateTime();
    $date_max = CMbDT::date('+1 day') . ' 23:59:59';

    $operation  = new COperation();
    $ds = $operation->getDS();

    $ljoin              = [];
    $ljoin["sejour"]    = "operations.sejour_id = sejour.sejour_id";
    $ljoin["grossesse"] = "sejour.grossesse_id = grossesse.grossesse_id";

    $where                            = [];
    $where["date"]                    = $ds -> prepareBetween(CMbDT::date("-1 Day"), CMbDT::date($date_min));
    $where["sejour.entree_prevue"]    = "<= '$date_max'";
    $where["sejour.sortie_prevue"]    = ">= '$date_min'";
    $where['sejour.sortie_reelle']    = "IS NULL OR sortie_reelle > '$date_max'";
    $where["sejour.annule"]           = " = '0' ";
    $where["sejour.grossesse_id"]     = "IS NOT NULL";
    $where["sejour.group_id"]         = "= '$group->_id'";
    $where["salle_id"]                = CSQLDataSource::prepareIn(array_keys($salles));
    $where["operations.sortie_salle"] = "IS NULL";
    $where["operations.annulee"]      = "= '0'";

    $operations = $operation->loadList($where, null, null, "operation_id", $ljoin);

    CMbObject::massLoadFwdRef($operations, "salle_id");
    $sejours  = CMbObject::massLoadFwdRef($operations, "sejour_id");
    $patients = CMbObject::massLoadFwdRef($sejours, "patient_id");
    CStoredObject::massLoadBackRefs($patients, "bmr_bhre");

    $operations_salle = [];

    foreach ($operations as $_operation) {
        $_operation->loadRefPlageOp();
        $_operation->updateSalle();
        $_operation->loadRefPraticien();
        $sejour  = $_operation->loadRefSejour();
        $patient = $sejour->loadRefPatient();
        $patient->updateBMRBHReStatus($_operation);

        $operations_salle[$_operation->salle_id][] = $_operation;
    }

    $grille         = [];
    $listOperations = [];
    $salles_occupe  = 0;

    $exist_plan = count($operations_salle);

    CBlocOperatoire::vueTopologie($salles, $grille, $listOperations, $operations_salle, $salles_occupe);
}

if (!$bloc && !$service && $sejour_id) {
    $sejour = new CSejour();
    $sejour->load($sejour_id);
    $sejour->loadRefsAffectations();
    $sejour->loadRefPatient();
    $sejour->loadRefsConsultations();
    $sejour->loadRefNaissance();
    $sejour->loadRefPraticien();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("object", $object);

if ($bloc) {
    $smarty->assign("listOperations", $listOperations);
    $smarty->assign("salles_occupe", $salles_occupe);
} else {
    $smarty->assign("listSejours", $listSejours);
    $smarty->assign("lits_occupe", $lits_occupe);
}

$smarty->assign("grille", $grille);
$smarty->assign("exist_plan", $exist_plan);
if (!$bloc && !$service && $sejour_id) {
    $smarty->assign("_sejour", $sejour);
    $smarty->assign("refresh_etiquette", true);
    $smarty->display("inc_patient_placement");
} else {
    $smarty->display("inc_placement_patients");
}
