<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Ccam\CDatedCodeCCAM;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();
$sejour_id = CValue::getOrSession("sejour_id");

// Chargement des praticiens
$listPrat = new CMediusers();
$listPrat = $listPrat->loadPraticiens(PERM_READ);

// Chargement du séjour précis du patient
$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

if ($sejour->group_id != CGroups::loadCurrent()->_id) {
  CAppUI::accessDenied();
}
$sejour->loadRefPatient();

$patient = $sejour->_ref_patient;
$patient->loadRefsFwd();
$patient->loadRefPhotoIdentite();
$patient->loadRefsCorrespondants();
$patient->loadRefDossierMedical();
$patient->_ref_dossier_medical->updateFormFields();
$patient->loadIPP();
$patient->countINS();

// Chargement des séjours du Patient
$sejours = $patient->loadRefsSejours();
$isSejourPatient = null;

if (array_key_exists($sejour_id, $patient->_ref_sejours)) {
  $isSejourPatient = $sejour_id;
}
foreach ($sejours as $_sej) {
  $_sej->loadRefPraticien();
  $_sej->loadRefsOperations();
  $_sej->loadNDA();
  $_sej->canDo();
  foreach ($_sej->_ref_operations as $_op) {
    $_op->countDocItems();
    $_op->canDo();
  }
}

// Dossier médical
$dossier_medical = $patient->loadRefDossierMedical();
$dossier_medical->updateFormFields();
$dossier_medical->loadRefsAntecedents();
$dossier_medical->loadRefsTraitements();
$patient->loadIPP();

$sejour->loadRefPrescriptionSejour();
$dossier_medical = $sejour->loadRefDossierMedical();
$dossier_medical->updateFormFields();
$dossier_medical->loadRefsAntecedents();
$dossier_medical->loadRefsTraitements();
$sejour->loadExtDiagnostics();
$sejour->countExchanges();
$sejour->loadNDA();
$sejour->loadRefsOperations();
$sejour->loadRefsConsultations();
$sejour->loadRefsActes();
$sejour->canDo();

foreach ($sejour->_ref_consultations as $consult) {
  $consult->loadRefPlageConsult();
  $consult->loadExtCodesCCAM();
  $consult->loadRefsActes();
  $consult->loadRefConsultAnesth();
  $consult->loadRefPatient()->loadRefLatestConstantes();
  foreach ($consult->_ref_actes as $_acte) {
    $_acte->loadRefExecutant();
  }
}

foreach ($sejour->_ref_actes as $_acte) {
  $_acte->loadRefExecutant();
}
foreach ($sejour->_ref_operations as $_operation) {
  $_operation->loadRefsFwd();
  $_operation->countExchanges();
  $_operation->countDocItems();
  $_operation->loadRefsActes();
  $_operation->canDo();
  $_operation->_ref_sejour->loadRefsFwd();
  foreach ($_operation->_ext_codes_ccam as $key => $value) {
    $_operation->_ext_codes_ccam[$key] = CDatedCodeCCAM::get($value->code);
  }
  $_operation->getAssociationCodesActes();
  $_operation->loadPossibleActes();
  $_operation->_ref_plageop->loadRefsFwd();
  $_operation->loadRefPraticien();

  // Chargement des règles de codage
  $_operation->loadRefsCodagesCCAM();
  foreach ($_operation->_ref_codages_ccam as $_codages_by_prat) {
    foreach ($_codages_by_prat as $_codage) {
      $_codage->loadPraticien()->loadRefFunction();
      $_codage->loadActesCCAM();
      $_codage->getTarifTotal();
      foreach ($_codage->_ref_actes_ccam as $_acte) {
        $_acte->getTarif();
      }
    }
  }
  if ($_operation->plageop_id) {
    $_operation->_ref_plageop->loadRefsFwd();
  }
  
  $consult_anest = $_operation->_ref_consult_anesth;
  if ($consult_anest->consultation_anesth_id) {
    $consult_anest->loadRefsFwd();
    $consult_anest->_ref_plageconsult->loadRefsFwd();
  }
}


// Création du template
$smarty = new CSmartyDP();

$smarty->assign("canPatients"  , CModule::getCanDo("dPpatients"));
$smarty->assign("canAdmissions", CModule::getCanDo("dPadmissions"));
$smarty->assign("canPlanningOp", CModule::getCanDo("dPplanningOp"));
$smarty->assign("canCabinet"   , CModule::getCanDo("dPcabinet"));

$smarty->assign("hprim21installed", CModule::getActive("hprim21"));
$smarty->assign("sejour"  , $sejour );
$smarty->assign("listPrat", $listPrat);

$smarty->assign("patient", $patient);
$smarty->assign("isSejourPatient" , $isSejourPatient);

$smarty->display("inc_vw_sejour");
