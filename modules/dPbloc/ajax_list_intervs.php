<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Kereon\CKereonService;
use Ox\Mediboard\Kereon\CKereonServiceException;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CTypeAnesth;

CCanDo::checkEdit();

$plageop_id = CView::get("plageop_id", "ref class|CPlageOp");
$list_type  = CView::get("list_type", "enum list|left|right default|left");

CView::checkin();

$anesth = new CTypeAnesth();
$anesth = $anesth->loadGroupList();

// Infos sur la plage opératoire
$plage = new CPlageOp();
$plage->load($plageop_id);
$plage->loadRefsFwd();

$intervs = $plage->loadRefsOperations(true, "rank, rank_voulu, horaire_voulu", true, $list_type != "left");

$chirs = CStoredObject::massLoadFwdRef($intervs, "chir_id");
CStoredObject::massLoadFwdRef($chirs, "function_id");
$sejours = CStoredObject::massLoadFwdRef($intervs, "sejour_id");
$patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
CStoredObject::massLoadBackRefs($patients, "bmr_bhre");
CStoredObject::massCountBackRefs($intervs, "affectations_personnel");
CStoredObject::massCountBackRefs($intervs, "notes");
$seconde_plage = new CPlageOp();
if (CAppUI::gconf("dPplanningOp COperation multi_salle_op") && $plage->chir_id) {
  $seconde_plage = CPlageOp::findSecondePlageChir($plage, $new_time);
}

foreach ($intervs as $_interv) {
  $_interv->loadRefsFwd();
  $_interv->_ref_chir->loadRefFunction();
  $_interv->_ref_sejour->loadRefsFwd();
  $_interv->loadRefsNotes();
  $_interv->loadRefsCommande();
  $_interv->computeStatusPanier();
  $_interv->_count_affectations_personnel = $_interv->countBackRefs("affectations_personnel");
  $patient = $_interv->_ref_sejour->_ref_patient;
  $patient->loadRefDossierMedical();
  $patient->_ref_dossier_medical->countAllergies();
  $patient->_ref_dossier_medical->countAntecedents();
  $patient->getSurpoids();
  $patient->updateBMRBHReStatus($_interv);

  // Recherche d'une intervention au même jour pour le patient
  $operation = new COperation();
  $ljoin = array(
    "sejour" => "sejour.sejour_id = operations.sejour_id"
  );
  $where = array(
    "operations.operation_id" => "!= '$_interv->_id'",
    "operations.date"         => "= '$_interv->date'",
    "sejour.patient_id"       => "= '$patient->_id'"
  );

  if ($operation->loadObject($where, null, null, $ljoin)) {
    $operation->loadRefPlageOp();
    $operation->loadRefChir();
    $_interv->_other_interv_patient = $operation;
  }

  if ($seconde_plage->_id) {
    $_interv->_ref_prev_op = CPlageOp::findPrevOp($_interv, $plage, $seconde_plage, $_interv->time_operation, false);
    $_interv->_ref_prev_op->loadRefPlageOp();
    $_interv->_ref_prev_op->loadRefPatient();
    $_interv->_ref_next_op = CPlageOp::findNextOp($_interv, $plage, $seconde_plage, $_interv->time_operation);
    $_interv->_ref_next_op->loadRefPatient();
    $_interv->_ref_next_op->loadRefPlageOp();
  }
}

// liste des plages du praticien
$where = array(
  "date"    => "= '$plage->date'",
  "chir_id" => "= '$plage->chir_id'",
);

/** @var CPlageOp[] $list_plages */
$list_plages = $plage->loadList($where);
CStoredObject::massLoadFwdRef($list_plages, "salle_id");
foreach ($list_plages as $_plage) {
  $_plage->loadRefSalle();
}

usort($list_plages, function (CPlageOp $a, CPlageOp $b) {
    return strcmp($a->_ref_salle->nom, $b->_ref_salle->nom);
});

$best_time_intervention = array();

if (CModule::getActive("kereon") && $list_type == "right" && (count($intervs) > 1)) {
  $plage->multicountOperations();

  try {
    $kereon = new CKereonService(CGroups::loadCurrent()->_id);
    $response = $kereon->predict($intervs);
  }
  catch (CKereonServiceException $e) {
    CAppUI::stepAjax($e->getMessage(), UI_MSG_ERROR);
  }
  catch (Exception $e) {
    CAppUI::stepAjax($e->getMessage(), UI_MSG_ERROR);
  }

  // Calcuclate the best value
  $best_time_intervention = $kereon->getBestTimeSaver(reset($response), $plage);
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("listPlages"            , $list_plages);
$smarty->assign("plage"                 , $plage);
$smarty->assign("anesth"                , $anesth);
$smarty->assign("intervs"               , $intervs);
$smarty->assign("list_type"             , $list_type);
$smarty->assign("seconde_plage"         , $seconde_plage);
$smarty->assign("best_time_intervention", $best_time_intervention);
$smarty->assign("nb_interv"             , count($intervs));
$smarty->display("inc_list_intervs");
