<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$patient_id = CView::getRefCheckRead("patient_id" , "ref class|CPatient");
CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);

$patient_insnir = $patient->loadRefPatientINSNIR();
$patient_insnir->createDatamatrix($patient_insnir->createDataForDatamatrix());

$listPrat = CConsultation::loadPraticiens(PERM_EDIT, null, null, null, false, false);

$patient->loadRefsDocItems(false);

$where = array();
$where["plageconsult.chir_id"] = CSQLDataSource::prepareIn(array_keys($listPrat));
$patient->loadRefsConsultations($where);
$patient->loadRefsSejours();

$dossier_medical = $patient->loadRefDossierMedical();
$dossier_medical->loadRefsAntecedents();
$dossier_medical->loadRefsTraitements();
$prescription = $dossier_medical->loadRefPrescription();

if ($prescription && is_array($prescription->_ref_prescription_lines)) {
  foreach ($dossier_medical->_ref_prescription->_ref_prescription_lines as $_line) {
    $_line->loadRefsPrises();
  }
}

$consultations =& $patient->_ref_consultations;
$sejours =& $patient->_ref_sejours;

CMbObject::massCountDocItems($consultations);
CStoredObject::massLoadFwdRef($consultations, "plageconsult_id");

// Consultations
foreach ($consultations as $consultation) {
  $consultation->loadRefsDocItems(false);
  $consultation->loadRefConsultAnesth();
  $consultation->loadRefsExamsComp();
  $consultation->loadRefsFichesExamen();
  $consultation->loadRefsActesCCAM();
  $consultation->loadRefsActesNGAP();
  $consultation->loadRefFacture()->loadRefsReglements();
  $consultation->loadRefPlageConsult();
  $consultation->_ref_plageconsult->_ref_chir->loadRefFunction();

  $consultation->loadRefsForms();

  $_latest_constantes = CConstantesMedicales::getLatestFor($patient, null, array("poids", "taille"), $consultation);
  $consultation->_latest_constantes = $_latest_constantes[0];
  
  // Affichage des ordonnances
  $consultation->loadRefsPrescriptions();
  if (isset($consultation->_ref_prescriptions["externe"])) {
    $consultation->_ref_prescriptions["externe"]->loadRefsFiles();
    foreach ($consultation->_ref_prescriptions["externe"]->_ref_files as $key => $_file) {
      if ($_file->annule) {
        unset($consultation->_ref_prescriptions["externe"]->_ref_files[$key]);
      }
    }
  }
}

// Sejours
$where = array();
$where["chir_id"] = CSQLDataSource::prepareIn(array_keys($listPrat));

CMbObject::massCountDocItems($patient->_ref_sejours);

foreach ($patient->_ref_sejours as $sejour) {
  $sejour->loadRefPraticien();
  $sejour->loadRefsPrescriptions();
  $sejour->loadRefsOperations($where);
  $sejour->loadRefsDocItems(false);
  $sejour->loadRefsForms();

  foreach ($sejour->_ref_operations as $operation) {
    $operation->loadRefPlageOp();
    $operation->loadRefChir();
    $operation->loadRefsDocItems(false);
    $operation->loadExtCodesCCAM();
    $operation->loadRefsForms();
  }
}

// Filtre sur les praticiens
$listPrat = CConsultation::loadPraticiensCompta();

// Fichiers / Documents importants
CPatient::getImportantFilesDocs($patient);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("patient" , $patient);
$smarty->assign("listPrat", $listPrat);

$smarty->display("vw_resume.tpl");
