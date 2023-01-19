<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();
$date = CView::get("date", "date default|now", true);
CView::checkin();

$group = CGroups::loadCurrent();

$service              = new CService();
$service->obstetrique = 1;
$service->cancelled   = 0;
$service->group_id    = $group->_id;

$services = $service->loadMatchingList("nom");

$date_min   = CMbDT::dateTime($date);
$date_max   = CMbDT::dateTime("+1 day", $date_min);
$listNotAff = [
    "Non plac�s" => [],
];

// Chargement des sejours n'ayant pas d'affectation pour cette p�riode
$ljoin = [
    "grossesse" => "sejour.grossesse_id = grossesse.grossesse_id",
];

$where                         = [];
$where["sejour.entree_prevue"] = "<= '$date_max'";
$where["sejour.sortie_prevue"] = ">= '$date_min'";
$where['sejour.entree_reelle'] = "IS NOT NULL";
$where['sejour.sortie_reelle'] = "IS NULL OR (sortie_reelle > '$date_max')";
$where["sejour.annule"]        = " = '0' ";
$where["sejour.grossesse_id"]  = "IS NOT NULL";
$where["sejour.group_id"]      = "= '$group->_id'";
$where["grossesse.active"]     = "= '1'";

$sejour                   = new CSejour();
$listNotAff["Non plac�s"] = $sejour->loadList($where, null, null, null, $ljoin);

$ljoin_consult = [
    "plageconsult" => "plageconsult.plageconsult_id = consultation.plageconsult_id",
];

$patients = CStoredObject::massLoadFwdRef($listNotAff["Non plac�s"], "patient_id");
CStoredObject::massLoadBackRefs($patients, "bmr_bhre");
CStoredObject::massLoadFwdRef($listNotAff["Non plac�s"], "praticien_id");
$naissances    = CStoredObject::massLoadBackRefs($listNotAff["Non plac�s"], "naissance");
$sejours_maman = CStoredObject::massLoadFwdRef($naissances, "sejour_maman_id");
$consultations = CStoredObject::massLoadBackRefs(
    $listNotAff["Non plac�s"],
    "consultations",
    "date DESC, heure DESC",
    null,
    $ljoin_consult
);
CStoredObject::massLoadFwdRef($consultations, "plageconsult_id");
CStoredObject::massLoadBackRefs($sejours_maman, "operations", "date");

CPatient::massLoadIPP($patients);
CPatient::massCountPhotoIdentite($patients);

foreach ($listNotAff["Non plac�s"] as $key => $_sejour) {
    /* @var CSejour $_sejour */
    $affectations     = $_sejour->loadRefsAffectations("sortie ASC");
    $last_affectation = end($affectations);

    if (($last_affectation && $last_affectation->lit_id) || ($_sejour->service_id && !in_array(
                $_sejour->service_id,
                array_keys($services)
            ))) {
        unset($listNotAff["Non plac�s"][$key]);
        continue;
    }

    $_sejour->loadRefPatient()->loadRefPhotoIdentite();
    $_sejour->_ref_patient->updateBMRBHReStatus($_sejour);
    $_sejour->loadRefPraticien()->loadRefFunction();
    $_sejour->loadRefsConsultations();
    $_sejour->_ref_last_consult->loadRefPlageConsult();
    $_sejour->loadRefLastOperation();
    $naissance = $_sejour->loadRefNaissance();

    if ($naissance->_id) {
        $naissance->loadRefSejourMaman()->loadRefsOperations();
    }

    $_sejour->_ref_patient->loadRefDossierMedical(false);
    $_sejour->checkDaysRelative($date_min);
    $_sejour->loadRefPrestation();
}

$dossiers = CMbArray::pluck($listNotAff["Non plac�s"], "_ref_patient", "_ref_dossier_medical");
CDossierMedical::massCountAntecedentsByType($dossiers, "deficience");
// Cr�ation du template
$smarty = new CSmartyDP("modules/dPhospi");
$smarty->assign("list_patients_notaff", $listNotAff);
$smarty->assign("show_blocked_bed", 0);
$smarty->display("inc_patients_non_places");
