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
use Ox\Core\CValue;
use Ox\Interop\Imeds\CImeds;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$patient_id = CValue::getOrSession("patient_id", 0);

if (!$patient_id) {
  CAppUI::setMsg("Vous devez selectionner un patient", UI_MSG_ALERT);
  CAppUI::redirect("m=dPpatients&tab=0");
}

// Récuperation du patient sélectionné
$patient = new CPatient;
$patient->load($patient_id);
$patient->loadDossierComplet(PERM_READ);
$patient->loadRefDossierMedical();
$patient->_ref_dossier_medical->loadRefsAntecedents();
$patient->_ref_dossier_medical->loadRefsTraitements();
$patient->countINS();

$userSel = CMediusers::get();

// Suppression des consultations d'urgences
foreach ($patient->_ref_consultations as $keyConsult => $consult) {
  if ($consult->motif == "Passage aux urgences") {
    unset($patient->_ref_consultations[$keyConsult]);
  }
}

$can_view_dossier_medical =
  CModule::getCanDo('dPcabinet')->edit ||
  CModule::getCanDo('dPbloc')->edit ||
  CModule::getCanDo('dPplanningOp')->edit ||
  $userSel->isFromType(array("Infirmière"));

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("canCabinet", CModule::getCanDo("dPcabinet"));
$smarty->assign("canPlanningOp", CModule::getCanDo("dPplanningOp"));

$smarty->assign("patient", $patient);
$smarty->assign("can_view_dossier_medical", $can_view_dossier_medical);
$smarty->assign("isImedsInstalled", (CModule::getActive("dPImeds") && CImeds::getTagCIDC(CGroups::loadCurrent())));

$smarty->display("inc_vw_full_patients.tpl");
