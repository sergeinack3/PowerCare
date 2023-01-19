<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\CViewHistory;
use Ox\Interop\Imeds\CImeds;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$user = CMediusers::get();

$patient_id   = CView::get("patient_id", "ref class|CPatient", true);
$vw_cancelled = CView::get("vw_cancelled", "bool default|0");

// recuperation des id dans le cas d'une recherche de dossiers cliniques 
$consultation_id = CView::get("consultation_id", "ref class|CConsultation");
$sejour_id       = CView::get("sejour_id", "ref class|CSejour");
$operation_id    = CView::get("operation_id", "ref class|COperation");

CView::checkin();

CAccessMedicalData::logAccess("CSejour-$sejour_id");
CAccessMedicalData::logAccess("COperation-$operation_id");
CAccessMedicalData::logAccess("CConsultation-$consultation_id");

// Récuperation du patient sélectionné
$patient = new CPatient();
$patient->load($patient_id);

if (!$patient->_id || $patient->_vip) {
  CAppUI::setMsg("Vous devez selectionner un patient", UI_MSG_ALERT);
  CAppUI::redirect("m=patients&tab=vw_idx_patients");
} else{
    CAccessMedicalData::logAccess($patient, 'consulter dossier');
}

// Save history
$params = array(
  "patient_id"      => $patient_id,
  "vw_cancelled"    => $vw_cancelled,
  "consultation_id" => $consultation_id,
  "sejour_id"       => $sejour_id,
  "operation_id"    => $operation_id,
);
CViewHistory::save($patient, CViewHistory::TYPE_VIEW, $params);

$patient->loadDossierComplet(PERM_READ, false);
$patient->updateNomPaysInsee();
$patient->loadCodeInseeNaissance();
$patient->loadView();

// Chargement de l'IPP
$patient->loadIPP();
$patient->countINS();

$patient->updateBMRBHReStatus();

// Chargement du dossier medical du patient
$dossier_medical = $patient->loadRefDossierMedical();
$dossier_medical->loadComplete();

$nb_consults_annulees = 0;

// Suppression des consultations d'urgences
foreach ($patient->_ref_consultations as $consult) {
  if ($consult->motif == "Passage aux urgences" || ($consult->annule && !$vw_cancelled)) {
    unset($patient->_ref_consultations[$consult->_id]);
    $nb_consults_annulees++;
  }
}

$nb_sejours_annules = 0;
$nb_ops_annulees    = 0;

// Masquer par défault les interventions et séjours annulés
if (!$vw_cancelled) {
  foreach ($patient->_ref_sejours as $_key => $_sejour) {
    foreach ($_sejour->_ref_operations as $_key_op => $_operation) {
      if ($_operation->annulee) {
        unset ($_sejour->_ref_operations[$_key_op]);
        $nb_ops_annulees++;
      }
    }
    if ($_sejour->annule) {
      unset($patient->_ref_sejours[$_key]);
      $nb_sejours_annules++;
    }
  }
}

$events_by_date = $patient->getTimeline();

$patient->_ref_dossier_medical->canDo();

$canCabinet    = CModule::getCanDo("dPcabinet");
$canPlanningOp = CModule::getCanDo("dPplanningOp");
$canBloc       = CModule::getCanDo("dPbloc");

$can_view_dossier_medical =
  $canCabinet->edit ||
  $canBloc->edit ||
  $canPlanningOp->edit ||
  $user->isInfirmiere();

if (CAppUI::gconf("dPpatients sharing patient_data_sharing")) {
  $patient->getSharingGroupsByStatus();
}

$smarty = new CSmartyDP();

$smarty->assign("patient", $patient);
$smarty->assign("object", $patient);

$smarty->assign("canCabinet", $canCabinet);
$smarty->assign("canPlanningOp", $canPlanningOp);

$smarty->assign("consultation_id", $consultation_id);
$smarty->assign("sejour_id", $sejour_id);
$smarty->assign("operation_id", $operation_id);
$smarty->assign("can_view_dossier_medical", $can_view_dossier_medical);
$smarty->assign("isImedsInstalled", (CModule::getActive("dPImeds") && CImeds::getTagCIDC(CGroups::loadCurrent())));
$smarty->assign("nb_sejours_annules", $nb_sejours_annules);
$smarty->assign("nb_ops_annulees", $nb_ops_annulees);
$smarty->assign("nb_consults_annulees", $nb_consults_annulees);
$smarty->assign("vw_cancelled", $vw_cancelled);
$smarty->assign("events_by_date", $events_by_date);

$smarty->display("vw_full_patients.tpl");
