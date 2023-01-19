<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\CSecondaryFunction;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\PlanningOp\CTypeAnesth;

CCanDo::checkRead();
$ds   = CSQLDataSource::get("std");

// Liste des prises en charge
$filter_sejour = new CSejour();
// filtre
$type_pec_spec   = array(
  "str",
  "default" => $filter_sejour->_specs["type_pec"]->_list
);

$date     = CView::get("date", "date default|now", true);
$type_pec = CView::get("type_pec", $type_pec_spec);
CView::checkin();

// Toutes les salles des blocs
$group =  CGroups::loadCurrent();
$listBlocs = $group->loadBlocs(PERM_READ);

// Les salles autorisées
$salle = new CSalle();
$listSalles = $salle->loadListWithPerms(PERM_READ);

// Listes des interventions hors plage
$operation = new COperation();

$ljoin = array();
$ljoin["sejour"] = "sejour.sejour_id = operations.sejour_id";

$where = array ();
$where["date"] = "= '$date'";
$where["plageop_id"] = "IS NULL";

// filtre sur les types pec des sejours
$where["sejour.type_pec"] = CSQLDataSource::prepareIn($type_pec);

/** @var COperation[] $urgences */
$urgences = $operation->loadGroupList($where, "salle_id, date, time_operation, chir_id", null, null, $ljoin);

CStoredObject::massCountBackRefs($urgences, "notes");
$sejours = CStoredObject::massLoadFwdRef($urgences, "sejour_id");
$patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
CStoredObject::massLoadBackRefs($patients, "dossier_medical");
CStoredObject::massLoadBackRefs($patients, "bmr_bhre");

$where['operations.urgence'] = " = '1'";
$count_urgences = $operation->countList($where, null, $ljoin);

$vacations_urgence = array();

if ($count_urgences > 0) {
  $salles_urgence = $salle->loadListWithPerms(PERM_EDIT);

  $plage = new CPlageOp();
  $where = array(
    "date"              => " = '$date'",
    "urgence"           => " = '1'",
    "plagesop.salle_id" => CSQLDataSource::prepareIn(array_keys($listSalles)),
    "group_id"          => "= '$group->_id'"
  );

  $ljoin = array(
    "sallesbloc"      => "sallesbloc.salle_id = plagesop.salle_id",
    "bloc_operatoire" => "bloc_operatoire.bloc_operatoire_id = sallesbloc.bloc_id"
  );

  $vacations_urgence = $plage->loadList($where, 'debut', null, null, $ljoin);

  CStoredObject::massLoadFwdRef($vacations_urgence, "chir_id");
  foreach ($vacations_urgence as $_plage) {
    $_plage->loadRefChir();
    $_plage->makeView();
    $_plage->loadRefSalle();
  }
}

foreach ($urgences as &$urgence) {
  if (!$urgence->loadRefChir()->canDo()->read) {
    unset($urgences[$urgence->_id]);
    continue;
  }
  $urgence->loadRefsFwd();
  $urgence->loadRefAnesth();
  $patient = $urgence->_ref_sejour->loadRefPatient();
  $patient->updateBMRBHReStatus($urgence->_ref_sejour);
  $dossier_medical = $patient->loadRefDossierMedical();
  $dossier_medical->loadRefsAntecedents();
  $dossier_medical->countAntecedents();
  $dossier_medical->countAllergies();
  $urgence->_ref_chir->loadRefsFwd();
  $urgence->loadRefsNotes();
  $urgence->isUrgence();
  $urgence->computeStatusPanier();
  
  // Chargement des plages disponibles pour cette intervention
  $urgence->_ref_chir->loadBackRefs("secondary_functions");
  $secondary_functions = array();
  /** @var CSecondaryFunction $curr_sec_func */
  foreach ($urgence->_ref_chir->_back["secondary_functions"] as $curr_sec_func) {
    if ($curr_sec_func->loadRefFunction()->group_id == $group->_id) {
      $secondary_functions[$curr_sec_func->function_id] = $curr_sec_func;
    }
  }
  $where = array();
  $selectPlages  = "(plagesop.chir_id = %1 OR plagesop.spec_id = %2
    OR plagesop.spec_id ".CSQLDataSource::prepareIn(array_keys($secondary_functions)).")";
  $where[]       = $ds->prepare($selectPlages, $urgence->chir_id, $urgence->_ref_chir->function_id);
  $where["date"] = "= '$date'";
  $where["salle_id"] = CSQLDataSource::prepareIn(array_keys($listSalles));
  $order = "salle_id, debut";
  $plage = new CPlageOp;
  $urgence->_alternate_plages = $plage->loadList($where, $order);
  foreach ($urgence->_alternate_plages as $curr_plage) {
    $curr_plage->loadRefsFwd();
  }
}

$anesth = new CMediusers();
$anesths = $anesth->loadAnesthesistes(PERM_READ);

// Liste des types d'anesthésie
$listAnesthType = new CTypeAnesth();
$listAnesthType = $listAnesthType->loadGroupList();

// Création du template
$smarty = new CSmartyDP("modules/dPsalleOp");

$smarty->debugging = false;

$smarty->assign("urgences"          , $urgences);
$smarty->assign("listBlocs"         , $listBlocs);
$smarty->assign("listSalles"        , $listSalles);
$smarty->assign("anesths"           , $anesths);
$smarty->assign("date"              , $date);
$smarty->assign("group"             , $group);
$smarty->assign("listAnesthType"    , $listAnesthType);
$smarty->assign('vacations_urgence' , $vacations_urgence);
$smarty->assign("type_pec"          , $type_pec);
$smarty->assign("filter_sejour"     , $filter_sejour);

$smarty->display("vw_urgences");
