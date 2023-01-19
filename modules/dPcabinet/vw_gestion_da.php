<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$conusultation_id  = CValue::getOrSession("conusultation_id");
$consult_anesth  = CValue::getOrSession("consult_anesth_id");

$consult = new CConsultation();
$consult->load($conusultation_id);

CAccessMedicalData::logAccess($consult);

$consult->loadRefPlageConsult();
$patient = $consult->loadRefPatient();
$patient->loadRefsSejours();
foreach ($patient->_ref_sejours as $_sejour) {
  $_sejour->loadRefsOperations();
  foreach ($_sejour->_ref_operations as $op) {
    $op->loadRefPlageOp();
    $op->loadRefChir();
  }
}
$patient->getNextSejourAndOperation();
// Chargement du patient
$patient = $consult->_ref_patient;
$patient->countBackRefs("consultations");
$patient->countBackRefs("sejours");
$patient->loadRefs();
$patient->loadRefsNotes();
$patient->loadRefPhotoIdentite();

$sejour = new CSejour();
$group = CGroups::loadCurrent();
$group_id = $group->_id;
$where = array();
$where["patient_id"] = "= '$patient->_id'";
if (CAppUI::gconf("dPpatients sharing multi_group") == "hidden") {
  $where["sejour.group_id"] = "= '$group_id'";
}
$order = "entree ASC";
$patient->_ref_sejours = $sejour->loadList($where, $order);

$date_consult = $consult->_ref_plageconsult->date;
$ops_sans_dossier_anesth = array();
$ops_annulees = array();
$where_op = array("date >= '".$consult->_ref_plageconsult->date."'");
// Chargement de ses séjours
foreach ($patient->_ref_sejours as $_key => $_sejour) {
  if ($date_consult > $_sejour->entree_prevue && $date_consult > $_sejour->sortie_prevue) {
    unset($patient->_ref_sejours[$_sejour->_id]);
    continue;
  }
  $_sejour->loadRefsOperations($where_op);
  $_sejour->loadRefsFwd();
  foreach ($_sejour->_ref_operations as $_operation) {
    $_operation->loadRefsFwd();
    $_operation->_ref_chir->loadRefFunction()->loadRefGroup();
    $consult_anesth_op = $_operation->_ref_consult_anesth;
    if (!$consult_anesth_op->_id) {
      if ($_operation->annulee) {
        $ops_annulees[] = $_operation;
      }
      else {
        $ops_sans_dossier_anesth[] = $_operation;
      }
    }
    else {
      $consult_anesth_op->loadRefOperation();
      $consult_anesth_op->loadRefConsultation()->loadRefPatient()->loadRefLatestConstantes(null, array("poids"), $consult);
      $consult_anesth_op->_ref_consultation->loadRefPlageConsult();
    }
  }
  if (!count($_sejour->_ref_operations)) {
    unset($patient->_ref_sejours[$_sejour->_id]);
  }
}

$consult->loadRefPraticien();
$consult->loadRefsDossiersAnesth();
$consult->loadRefFirstDossierAnesth();

$tab_op = array();
foreach ($consult->_refs_dossiers_anesth as $consultation_anesth) {
  $consultation_anesth->loadRelPatient();
  $consult->_ref_patient->loadRefLatestConstantes(null, array("poids"), $consult);
  $consultation_anesth->_ref_consultation = $consult;

  $consultation_anesth->loadRefOperation()->loadRefSejour();
  $consultation_anesth->_ref_operation->_ref_sejour->loadRefDossierMedical();
}

$dossier_medical_patient = $patient->loadRefDossierMedical();
$dossier_medical_patient->loadRefsAntecedents();
$dossier_medical_patient->loadRefsTraitements();
$dossier_medical_patient->loadRefPrescription();

$user = new CMediusers();
$user->load($consult->_ref_praticien->_id);
$listChirs   = $user->loadPraticiens(PERM_READ);

if (CModule::getActive("maternite")) {
  $patient->getNextGrossesse($consult->_ref_plageconsult->date);
}

$functions_ids = CMbArray::pluck(CMediusers::loadCurrentFunctions(), "function_id");
$perm_to_duplicate = CAppUI::gconf("dPcabinet CConsultation csa_duplicate_by_cabinet") ? false : true;
$consult->_ref_praticien->loadRefFunction();
if ((in_array($consult->_ref_praticien->function_id, $functions_ids) || $consult->_ref_praticien->_ref_function->canDo()->edit) && !$perm_to_duplicate) {
  $perm_to_duplicate = true;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("consult"                 , $consult);
$smarty->assign("patient"                 , $patient);
$smarty->assign("dm_patient"              , $dossier_medical_patient);
$smarty->assign("ops_sans_dossier_anesth" , $ops_sans_dossier_anesth);
$smarty->assign("ops_annulees"            , $ops_annulees);
$smarty->assign("first_operation"         , reset($ops_sans_dossier_anesth));
$smarty->assign("consult_anesth"          , $consult_anesth);
$smarty->assign("listChirs"               , $listChirs);
$smarty->assign("perm_to_duplicate"       , $perm_to_duplicate);

$smarty->display("inc_consult_anesth/vw_gestion_da.tpl");
