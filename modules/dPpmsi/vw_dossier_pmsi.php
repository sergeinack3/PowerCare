<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$group = CGroups::loadCurrent();

$sejour  = new CSejour();
$patient = new CPatient();

// Si on passe un numéro de dossier,
// on charge le patient et le séjour correspondant
if ($NDA = CView::get("NDA", "str")) {
  $sejour->loadFromNDA($NDA);
  if ($sejour->_id && $sejour->group_id == $group->_id) {
    $patient = $sejour->loadRefPatient();
    CView::setSession("sejour_id", $sejour->_id);
    CView::setSession("patient_id", $patient->_id);
  }
}

// Si on n'a pas récupéré de patient via le numero de dossier,
// on charge le dossier en session
if (!$patient->_id) {
  $patient->load(CView::get("patient_id", "ref class|CPatient", true));
  $sejour->load(CView::get("sejour_id", "ref class|CSejour", true));
  // Si le séjour a un patient différent de celui selectionné,
  // on le déselectionne
  if ($patient->_id && $sejour->_id && $sejour->patient_id != $patient->_id) {
    CView::setSession("sejour_id");
    $sejour = new CSejour();
  }
  // Si on a un séjour mais pas de patient,
  // on utilise le patient du séjour
  if ($sejour->_id && !$patient->_id) {
    $patient->load($sejour->patient_id);
    CView::setSession("patient_id", $patient->_id);
  }
}
CView::checkin();

// Si on a un patient,
// on charge ses références
if ($patient->_id) {
  $patient->loadRefsSejours();
  $patient->loadRefsConsultations();
  // Si on n'a pas de séjour,
  // on prend le premier de la liste des séjours du patient
  if (!$sejour->_id && count($patient->_ref_sejours)) {
    $sejour = reset($patient->_ref_sejours);
  }
}

// Compteur par volets des items
$sejour->countDocItems();
$sejour->_nb_files_docs -= $sejour->_nb_forms; // Retrait du compte de formulaires car non présents dans la vue des documents du PMSI
$nbActes = 0;
$nbDiag = $sejour->DP ? 1 : 0;
$sejour->countActes();
$nbActes += $sejour->_count_actes;

foreach ($sejour->loadRefsOperations() as $_op) {
  $_op->countActes();
  $nbActes += $_op->_count_actes;
}

// Ajout du compteur d'items documentaires des interventions
$sejour->_nb_files_docs += CMbObject::massCountDocItems($sejour->_ref_operations);

foreach ($sejour->loadRefsConsultations() as $_consult) {
  $_consult->countActes();
  $nbActes += $_consult->_count_actes;
}

$sejour->_nb_files_docs += CMbObject::massCountDocItems($sejour->_ref_consultations);

$nbDiag += $sejour->DR ? 1 : 0;
$nbDiag += count($sejour->loadDiagnosticsAssocies());

$sejour->loadRefRelance();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("patient" , $patient);
$smarty->assign("sejour"  , $sejour);
$smarty->assign("nbActes" , $nbActes);
$smarty->assign("nbDiag"  , $nbDiag);
$smarty->display("vw_dossier_pmsi");