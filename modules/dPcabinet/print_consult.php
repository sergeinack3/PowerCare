<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Patients\CConstantesMedicales;

CCanDo::checkRead();

$today = date("d/m/Y");

$consult_id = CValue::get("consult_id", 0);

//Création de la consultation
$consult = new CConsultation();
$consult->load($consult_id);

CAccessMedicalData::logAccess($consult);

$consult->loadRefPatient();
$consult->loadRefSejour();
$consult->loadRefPraticien();
$consult->loadRefsBack();
$consult->loadRefsDocs();
$consult->loadComplete();
$consult->loadRefsInfoChecklistItem(true);
$consult->getSA();
$consult->loadRefSuiviGrossesse();
if ($suivi_grossesse = $consult->_ref_suivi_grossesse) {
  $suivi_grossesse_champs = $suivi_grossesse->sortAttributesByCategory();
}

$sejour = $consult->_ref_sejour;
$sejour->loadRefsConsultations();
$sejour->loadListConstantesMedicales();
$sejour->loadNDA();
$sejour->loadSuiviMedical();
$sejour->loadExtDiagnostics();
$patient = $consult->_ref_patient;

$patient->loadIPP();
$patient->loadRefDossierMedical();
$patient_insnir = $patient->loadRefPatientINSNIR();
$patient_insnir->createDatamatrix($patient_insnir->createDataForDatamatrix());

$dossier_medical = $patient->_ref_dossier_medical;
$dossier_medical->countAntecedents();
$dossier_medical->countTraitements();

$dossier_medical->loadRefPrescription();
$dossier_medical->loadRefsTraitements();

$constantes_medicales_grid = CConstantesMedicales::buildGrid($sejour->_list_constantes_medicales, false);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("patient", $patient);
$smarty->assign("sejour", $sejour);
$smarty->assign("dossier_medical", $dossier_medical);
$smarty->assign("consult", $consult);
$smarty->assign("constantes_medicales_grid", $constantes_medicales_grid);
$smarty->assign("today", $today);
if ($suivi_grossesse) {
  $smarty->assign("suivi_grossesse_champs", $suivi_grossesse_champs);
}

$smarty->display("print_consult.tpl");
