<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CModeleEtiquette;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\CSecondaryFunction;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();

$date         = CView::get("date", "date default|" . CMbDT::date(), true);
$canceled     = CView::get("canceled", "bool default|0", true);
$board        = CView::get("board", "bool default|0");
$boardItem    = CView::get("boardItem", "bool default|0");
$pratSel      = CView::get("pratSel", "ref class|CMediusers", true);
$function_id  = CView::get("functionSel", "ref class|CFunctions");
$hiddenPlages = CView::get("hiddenPlages", "str");

CView::checkin();

$userSel   = CMediusers::get($pratSel);

if($pratSel == "" && !$function_id) {
  $pratSel = -1;
}

$nb_canceled     = 0;
$nb_not_canceled = 0;
$current_group   = CGroups::loadCurrent();

// Urgences du jour
$list_urgences = [];
$list_ops_secondaires = [];
$operation = new COperation();

$where = [];
$ljoinUrgences = [];

// Si un praticien est sélectionné, filtre sur le praticien
if ($pratSel) {
  $whereChir = $userSel->getUserSQLClause();
  $where[100] = "chir_id $whereChir OR anesth_id $whereChir";
}
// Si un cabinet est sélectionné, filtre sur le cabinet
if ($function_id) {
  $ljoinUrgences["users_mediboard"] = "users_mediboard.user_id = operations.chir_id OR users_mediboard.user_id = operations.anesth_id";
  $where["users_mediboard.function_id"] = " = '$function_id'";
}
$where["operations.date"] = "= '$date'";
$where["operations.plageop_id"] = "IS NULL";

if (!$canceled) {
  $where["annulee"] = "= '0'";
}
/** @var COperation[] $list_urgences */
$list_urgences = $operation->loadList($where, "annulee, date", null, null, $ljoinUrgences);

$where["annulee"] = "= '1'";
$nb_urg_canceled = $operation->countList($where, null, $ljoinUrgences);
$nb_canceled += $nb_urg_canceled;
$nb_not_canceled += count($list_urgences);

// Chargement des opérations en tant que praticien secondaire
if ($board || $pratSel) {
  $where[100] = "'$userSel->_id' IN (operations.chir_2_id, operations.chir_3_id, operations.chir_4_id)";

  unset($where["operations.plageop_id"]);
  unset($where["annulee"]);
  if (!$canceled) {
    $where["annulee"] = "= '0'";
  }

  $list_ops_secondaires = $operation->loadList($where, "annulee, date", null, null, $ljoinUrgences);

  $nb_not_canceled += count($list_ops_secondaires);
}

foreach (array($list_urgences, $list_ops_secondaires) as $_list_ops) {
  $sejours  = CStoredObject::massLoadFwdRef($_list_ops, "sejour_id");
  $patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
  CStoredObject::massLoadBackRefs($patients, "bmr_bhre");
  CStoredObject::massCountBackRefs($_list_ops, 'actes_ccam');
  CMbObject::massCountDocItems($_list_ops);
  CMbObject::massCountDocItems($sejours);

  foreach ($_list_ops as $_urg) {
    $_urg->canDo();
    $_urg->loadRefsFwd();
    $_urg->loadRefsCommande();
    $_urg->countDocItems();
    $_urg->countForms();
    $_sejour = $_urg->_ref_sejour;

    /* Comptage du nombre d'activités CCAM */
    $_urg->_count['codes_ccam'] = 0;
    foreach (CMbArray::pluck($_urg->_ext_codes_ccam, "activites") as $_code) {
      $_urg->_count['codes_ccam'] += count($_code);
    }

    $_sejour->loadRefsFwd();
    $_sejour->_ref_patient->updateBMRBHReStatus($_sejour);
    $_sejour->canDo();
    $_sejour->countDocItems();
    $_sejour->countForms();
    $_sejour->_ref_patient->loadRefDossierMedical()->countAllergies();

    $presc = $_sejour->loadRefPrescriptionSejour();
    if ($presc && $presc->_id) {
      $presc->countLinesMedsElements($userSel->_id);
    }
  }
}

// Liste des opérations du jour sélectionné
$list_plages = array();

$where = array();
$ljoin = array(
  "operations" => "plagesop.plageop_id = operations.plageop_id"
);
$where["plagesop.date"] = "= '$date'";

// Si un praticien est sélectionné, filtre sur le praticien
if ($pratSel) {
  $userSel->loadBackRefs("secondary_functions");

  $secondary_specs = array();
  /** @var CSecondaryFunction $_sec_spec */
  foreach ($userSel->_back["secondary_functions"] as  $_sec_spec) {
    $secondary_specs[] = $_sec_spec->function_id;
  }
  $in = "";
  if (count($secondary_specs)) {
    $in = " OR plagesop.spec_id ".CSQLDataSource::prepareIn($secondary_specs);
  }
  $where[] = "plagesop.chir_id $whereChir
            OR plagesop.anesth_id $whereChir
            OR operations.anesth_id $whereChir
            OR plagesop.spec_id = '$userSel->function_id' $in
            OR (plagesop.chir_id IS NULL AND plagesop.spec_id IS NULL AND plagesop.urgence = '1')";
}

// Si un cabinet est sélectionné, filtre sur le cabinet
if ($function_id) {
  $ljoin["users_mediboard"] = "users_mediboard.user_id = operations.chir_id OR users_mediboard.user_id = operations.anesth_id";
  $where["users_mediboard.function_id"] = " = '$function_id'";
}

$order = "debut, salle_id";

$plageop = new CPlageOp();

/** @var CPlageOp[] $list_plages */
$list_plages = $plageop->loadList($where, $order, null, "plagesop.plageop_id", $ljoin);

// Chargement d'optimisation

CStoredObject::massLoadFwdRef($list_plages, "chir_id");
CStoredObject::massLoadFwdRef($list_plages, "anesth_id");
CStoredObject::massLoadFwdRef($list_plages, "spec_id");
CStoredObject::massLoadFwdRef($list_plages, "salle_id");

CStoredObject::massCountBackRefs($list_plages, "notes");

foreach ($list_plages as $_plage) {
  $op_canceled = new COperation();
  $op_canceled->annulee = 1;
  $op_canceled->plageop_id = $_plage->_id;
  $nb_canceled += $op_canceled->countMatchingList();

  $_plage->loadRefChir();
  $_plage->loadRefAnesth();
  $_plage->loadRefSpec();
  $_plage->loadRefSalle();
  $_plage->makeView();
  $_plage->loadRefsNotes();

  //compare current group with bloc group
  $_plage->_ref_salle->loadRefBloc();
  if ($_plage->_ref_salle->_ref_bloc->group_id != $current_group->_id) {
    $_plage->_ref_salle->_ref_bloc->loadRefGroup();
  }

  $where = array();
  if ($pratSel) {
    if ($userSel->isAnesth()) {
      // 2 cas :
      //  - l'anesth n'est pas celui de la plage, alors on charge que les interventions où il est anesth
      //  - l'anesth est celui de la plage : on charge les interventions où il a pu être redéfini et celles où l'anesth est absent
      $where[] = "anesth_id $whereChir" . ($_plage->anesth_id == $userSel->_id ? " OR anesth_id IS NULL" : "") . " OR chir_id $whereChir";
    }
    else {
      $where["chir_id"] = $whereChir;
    }
  }

  $_plage->loadRefsOperations($canceled, "annulee ASC, rank, rank_voulu, horaire_voulu", true, null, $where);

  // Chargement d'optimisation

  CStoredObject::massLoadFwdRef($_plage->_ref_operations, "chir_id");
  CStoredObject::massCountBackRefs($_plage->_ref_operations, 'actes_ccam');
  $sejours = CStoredObject::massLoadFwdRef($_plage->_ref_operations, "sejour_id");
  $patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
  CStoredObject::massLoadBackRefs($patients, "bmr_bhre");
  CMbObject::massCountDocItems($_plage->_ref_operations);
  CMbObject::massCountDocItems($sejours);

  foreach ($_plage->_ref_operations as $_op) {
    $nb_not_canceled++;

    $_op->loadRefsFwd();
    $_sejour = $_op->_ref_sejour;
    $_op->canDo();
    $_op->loadRefsCommande();
    $_op->countDocItems();
    $_op->countForms();
    $_sejour->canDo();
    $_sejour->loadRefsFwd();
    $_sejour->_ref_patient->updateBMRBHReStatus($_sejour);
    $_sejour->countDocItems();
    $_sejour->countForms();
    $_sejour->_ref_patient->loadRefDossierMedical()->countAllergies();

    /* Comptage du nombre d'activités CCAM */
    $_op->_count['codes_ccam'] = 0;
    foreach (CMbArray::pluck($_op->_ext_codes_ccam, "activites") as $_code) {
      $_op->_count['codes_ccam'] += count($_code);
    }

    $presc = $_sejour->loadRefPrescriptionSejour();
    if ($presc && $presc->_id) {
      $presc->countLinesMedsElements($userSel->_id);
    }
  }
}

// Praticien concerné
$user = CMediusers::get();
if ($user->isPraticien()) {
  $praticien = $user;
}
else {
  $praticien = CMediusers::get($pratSel);
}

$praticien->loadRefFunction();
$praticien->_ref_function->loadRefGroup();
$praticien->canDo();

// Compter les modèles d'étiquettes
$modele_etiquette = new CModeleEtiquette();
$modele_etiquette->object_class = "COperation";
$modele_etiquette->group_id = $current_group->_id;

$nb_modeles_etiquettes_operation = $modele_etiquette->countMatchingList();

$modele_etiquette->object_class = "CSejour";
$nb_modeles_etiquettes_sejour = $modele_etiquette->countMatchingList();

$nb_printers = 0;
if (CModule::getActive("printing")) {
  // Chargement des imprimantes pour l'impression d'étiquettes
  $function      = $user->loadRefFunction();
  $nb_printers   = $function->countBackRefs("printers");
}

$salles = array();
if (!empty($list_urgences)) {
  $salle = new CSalle();
  $salles = $salle->loadGroupList();
}

$print_content_class = null;
$print_content_id = null;
// Si un praticien est sélectionné, on imprimera ses interventions
if ($pratSel) {
  $print_content_class = "CMediusers";
  $print_content_id    = $pratSel;
}
// Sinon, si un cabinet est sélectionnée, on imprimera les interventions du cabinet
elseif ($function_id) {
  $print_content_class = "CFunctions";
  $print_content_id    = $function_id;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("praticien"                      , $praticien);
$smarty->assign("boardItem"                      , $boardItem);
$smarty->assign("date"                           , $date);
$smarty->assign("canceled"                       , $canceled);
$smarty->assign("listUrgences"                   , $list_urgences);
$smarty->assign("list_ops_secondaires"           , $list_ops_secondaires);
$smarty->assign("listDay"                        , $list_plages);
$smarty->assign("nb_canceled"                    , $nb_canceled);
$smarty->assign("nb_not_canceled"                , $nb_not_canceled);
$smarty->assign("board"                          , $board);
$smarty->assign("nb_printers"                    , $nb_printers);
$smarty->assign("nb_modeles_etiquettes_sejour"   , $nb_modeles_etiquettes_sejour);
$smarty->assign("nb_modeles_etiquettes_operation", $nb_modeles_etiquettes_operation);
$smarty->assign("hiddenPlages"                   , stripslashes($hiddenPlages));
$smarty->assign("salles"                         , $salles);
$smarty->assign("print_content_class"            , $print_content_class);
$smarty->assign("print_content_id"               , $print_content_id);

$smarty->display("inc_list_operations");
