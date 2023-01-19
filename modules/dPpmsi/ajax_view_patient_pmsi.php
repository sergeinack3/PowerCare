<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Imeds\CImeds;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Admin\Rgpd\CRGPDManager;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Fse\CFseFactory;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$group = CGroups::loadCurrent();
$patient_id = CValue::get("patient_id");

// Chargement du séjour
$sejour  = new CSejour();
$sejour->load(CValue::get("sejour_id"));

CAccessMedicalData::logAccess($sejour);

// Chargement du patient
$patient = new CPatient();
if ($patient_id) {
  $patient = $patient->load($patient_id);
}
else {
  $patient = $sejour->loadRelPatient();
}

$patient->loadIPP();
$patient->loadRefsCorrespondants();
$patient->loadRefPhotoIdentite();
$patient->loadPatientLinks();
$patient->countINS();
$patient->updateBMRBHReStatus($sejour);
$patient->loadRefPatientINSNIR();
if (CModule::getActive("fse")) {
  $cv = CFseFactory::createCV();
  if ($cv) {
    $cv->loadIdVitale($patient);
  }
}

// Chargement du séjour
$sejour  = new CSejour();
$sejour->load(CValue::get("sejour_id"));
if ($sejour->patient_id == $patient->_id) {
  $sejour->_ref_patient = $patient;
  $sejour->canDo();
  $sejour->loadNDA();
  $sejour->loadExtDiagnostics();
  $sejour->loadRefsOperations();
  foreach ($sejour->_ref_operations as $_op) {
    $_op->loadRefPraticien();
    $_op->loadRefPlageOp();
    $_op->loadRefAnesth();
    $_op->loadRefsConsultAnesth();
    $_op->loadRefBrancardage();
  }
  $sejour->loadRefsConsultAnesth();
}
else {
  $sejour = new CSejour();
}

if (CModule::getActive("appFineClient")) {
  CAppFineClient::loadIdex($patient, $sejour->group_id);
  $patient->loadRefStatusPatientUser();
}

$manager = new CRGPDManager(CGroups::loadCurrent()->_id);

if ($manager->isEnabledFor($patient)) {
  $consent = $manager->getConsentForObject($patient);
  $patient->setRGPDConsent($consent);
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("canPatients"     , CModule::getCanDo("dPpatients"));
$smarty->assign("hprim21installed", CModule::getActive("hprim21"));
$smarty->assign("isImedsInstalled", (CModule::getActive("dPImeds") && CImeds::getTagCIDC(CGroups::loadCurrent())));
$smarty->assign("patient"         , $patient);
$smarty->assign("sejour"          , $sejour);
$smarty->assign("rgpd_manager"    , $manager);

$smarty->display("inc_vw_patient_pmsi");
