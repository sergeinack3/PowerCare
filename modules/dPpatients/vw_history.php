<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

// Définition des variables
$patient_id = CView::get("patient_id", "ref class|CPatient");

CView::checkin();

//Récupération du dossier complet patient
$patient = new CPatient();
$patient->load($patient_id);
$patient->loadDossierComplet();

$patient->loadHistory();

// log pour les séjours
foreach ($patient->_ref_sejours as $sejour) {
  $sejour->loadHistory();

  // log pour les opérations de ce séjour
  $sejour->loadRefsOperations();
  foreach ($sejour->_ref_operations as $operation) {
    $operation->loadHistory();
  }

  // log pour les affectations de ce séjour
  $sejour->loadRefsAffectations();
  foreach ($sejour->_ref_affectations as $affectation) {
    $affectation->loadHistory();
  }
}

// log pour les consultations
foreach ($patient->_ref_consultations as $consultation) {
  $consultation->loadHistory();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("patient", $patient);

$smarty->display("vw_history.tpl");
