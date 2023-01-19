<?php
/**
 * @package Mediboard\Cabinet
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
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CTechniqueComp;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CTypeAnesth;

CCanDo::checkRead();

// Utilisateur sélectionné ou utilisateur courant
$prat_id = CValue::get("chirSel", 0);

$userSel = CMediusers::get($prat_id);
$userSel->loadRefs();
$canUserSel = $userSel->canDo();

// Vérification des droits sur les praticiens
$listChir = CConsultation::loadPraticiens(PERM_EDIT);

if (!$userSel->isMedical()) {
  CAppUI::setMsg("Vous devez selectionner un professionnel de santé", UI_MSG_ALERT);
  CAppUI::redirect("m=dPcabinet&tab=0");
}

$canUserSel->needsEdit();

$selConsult        = CValue::get("selConsult", 0);
$dossier_anesth_id = CValue::getOrSession("dossier_anesth_id", 0);

if (isset($_GET["date"])) {
  $selConsult = null;
  CValue::setSession("selConsult", 0);
}

$anesth = new CTypeAnesth();
$anesth = $anesth->loadGroupList();

// Consultation courante
$consult            = new CConsultation();
$consult->_ref_chir = $userSel;

if ($selConsult) {
  $consult = CConsultation::findOrFail($selConsult);

  CAccessMedicalData::logAccess($consult);

  $canConsult = $consult->canDo();
  $canConsult->needsEdit();

  $consult->loadRefConsultAnesth();
  $consult->loadRefPlageConsult();
  $consult->loadRefPatient();

  if (isset($consult->_refs_dossiers_anesth[$dossier_anesth_id])) {
    $consult->_ref_consult_anesth = $consult->_refs_dossiers_anesth[$dossier_anesth_id];
    $consult->_ref_consult_anesth->loadRefs();
    $sejour =& $consult->_ref_consult_anesth->_ref_sejour;

    if ($consult->_ref_consult_anesth->_ref_operation->operation_id) {
      if ($consult->_ref_consult_anesth->_ref_operation->passage_uscpo === null) {
        $consult->_ref_consult_anesth->_ref_operation->passage_uscpo = "";
      }
      $consult->_ref_consult_anesth->_ref_operation->loadRefSejour();
      $sejour =& $consult->_ref_consult_anesth->_ref_operation->_ref_sejour;
    }
  }

  $consult_anesth =& $consult->_ref_consult_anesth;
}
else {
  $consult->_ref_consult_anesth = new CConsultAnesth();
}

$consult_anesth =& $consult->_ref_consult_anesth;
$consult_anesth->loadRefsInfoChecklist();

$consult_anesth->loadRefScoreLee();
$consult_anesth->loadRefScoreMet();
$consult_anesth->loadRefScoreHemostase();
$consult_anesth->loadRefConsultation();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("isPrescriptionInstalled", CModule::getActive("dPprescription"));
$smarty->assign("consult", $consult);
$smarty->assign("consult_anesth", $consult_anesth);
$smarty->assign("anesth", $anesth);
$smarty->assign("techniquesComp", new CTechniqueComp());
$smarty->assign("userSel", $userSel);
$smarty->display("inc_consult_anesth/acc_infos_anesth");
