<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();
$consult_id = CView::getRefCheckRead("consult_id", "ref class|CConsultation", true);
$dossier_anesth_id = CView::get("dossier_anesth_id", "ref class|CConsultAnesth", true);
CView::checkin();

$consult = new CConsultation();
$consult->load($consult_id);

CAccessMedicalData::logAccess($consult);

$consult->loadRefPlageConsult();
$patient = $consult->loadRefPatient();
$consult->loadRefsDossiersAnesth();

$tab_op = array();
foreach ($consult->_refs_dossiers_anesth as $consult_anesth) {
  $consult_anesth->loadRelPatient();
  $consultation = $consult_anesth->_ref_consultation;
  $consultation->_ref_patient->loadRefLatestConstantes(null, array("poids"), $consultation);

  if (!$consultation->_ref_patient->_ref_constantes_medicales->poids && $consultation->loadRefSejour()->_id) {
    $date = $consultation->_ref_plageconsult->date;
    $cte = CConstantesMedicales::getRelated(array("poids"), $patient, $consultation->_ref_sejour, $date." 00:00:00", $date." 23:59:00");
    $consultation->_ref_patient->_ref_constantes_medicales->poids = count($cte) ? reset($cte)->poids : false;
  }

  $consult_anesth->loadRefOperation()->loadRefSejour();
  $consult_anesth->_ref_operation->_ref_sejour->loadRefDossierMedical();

  if (!$consult_anesth->operation_id) {
    $tab_op[] = 0;
  }
  else {
    $tab_op[] = $consult_anesth->operation_id;
  }
}

if (!count($tab_op)) {
  $tab_op[] = 0;
}

$dossier_medical_patient = $consult->_ref_patient->loadRefDossierMedical();
$dossier_medical_patient->loadRefsAntecedents();
$dossier_medical_patient->loadRefsTraitements();
$dossier_medical_patient->loadRefPrescription();

//antecedents oblogatoires
$mandatories = array();
$mandatory_types = explode('|', CAppUI::conf("dPpatients CAntecedent mandatory_types"));
foreach ($mandatory_types as $_type) {
  $mandatories[$_type] = array();
}
foreach ($dossier_medical_patient->_ref_antecedents_by_type as $type => $_atcs) {
  if (count($_atcs) && in_array($type, $mandatory_types)) {
    foreach($_atcs as $_atc) {
      $mandatories[$type][$_atc->_id] = $_atc;
    }
  }
}

$op_sans_dossier_anesth = 0;
// Chargement du patient
$patient->countBackRefs("sejours");

$sejour = new CSejour();
$group_id = CGroups::loadCurrent()->_id;
$where = array();
$where["patient_id"] = "= '$patient->_id'";
$where["entree_prevue"] = ">= '".$consult->_ref_plageconsult->date." 00:00:00'";
if (CAppUI::gconf("dPpatients sharing multi_group") == "hidden") {
  $where["sejour.group_id"] = "= '$group_id'";
}
$order = "entree_prevue ASC";

$patient->_ref_sejours = $sejour->loadList($where, $order);

// Chargement de ses séjours
foreach ($patient->_ref_sejours as $_key => $_sejour) {
  $_sejour->loadRefsOperations();
  foreach ($_sejour->_ref_operations as $_key_op => $_operation) {
    $_operation->loadRefsConsultAnesth();
    $_operation->loadRefPlageOp();
    $_operation->loadRefChir()->loadRefFunction()->loadRefGroup();
    $day = CMbDT::daysRelative($consult->_ref_plageconsult->date, $_operation->date);
    if (!$_operation->_ref_consult_anesth->_id && $day >= 0) {
      $op_sans_dossier_anesth = $_operation->_id;
    }
  }
}

$dossier_anesth = new CConsultAnesth();
if ($dossier_anesth_id && isset($consult->_refs_dossiers_anesth[$dossier_anesth_id])) {
  $dossier_anesth->_id = $dossier_anesth_id;
}

$use_moebius = (bool)(CModule::getActive("moebius") && CAppUI::pref('ViewConsultMoebius'));

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("consult"               , $consult);
$smarty->assign("mandatory_types"       , $mandatories);
$smarty->assign("dm_patient"            , $dossier_medical_patient);
$smarty->assign("tab_op"                , $tab_op);
$smarty->assign("op_sans_dossier_anesth", $op_sans_dossier_anesth);
$smarty->assign("dossier_anesth"        , $dossier_anesth);
$smarty->assign("use_moebius"           , $use_moebius);
$smarty->display("inc_check_consult_anesth");
