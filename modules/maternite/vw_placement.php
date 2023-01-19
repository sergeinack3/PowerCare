<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\SalleOp\CDailyCheckList;
use Ox\Mediboard\System\CUserLog;

/**
 * Liste des accouchements à placer en salle de naissance
 */
CCanDo::checkRead();
$ds   = CSQLDataSource::get("std");
$date = CValue::getOrSession("date", CMbDT::date());

// Toutes les salles des blocs
$whereBloc           = array();
$whereBloc["type"]   = "= 'obst'";
$whereBloc["actif"]  = "= '1'";
$whereSalle          = array();
$whereSalle["actif"] = "= '1'";
$listBlocs           = CGroups::loadCurrent()->loadBlocs(PERM_READ, true, "nom", $whereBloc, $whereSalle);

// Les salles autorisées
$salle                         = new CSalle();
$ljoin                         = array("bloc_operatoire" => "sallesbloc.bloc_id = bloc_operatoire.bloc_operatoire_id");
$where["bloc_operatoire.type"] = "= 'obst'";
$where["sallesbloc.actif"]     = "= '1'";
/** @var CSalle[] $listSalles */
$listSalles = $salle->loadListWithPerms(PERM_READ, $where, null, null, null, $ljoin);

// Chargement des Chirurgiens
$chir      = new CMediusers();
$listChirs = $chir->loadPraticiens(PERM_READ);

// Listes des interventions hors plage
$operation = new COperation();

$ljoin              = array();
$ljoin["sejour"]    = "operations.sejour_id = sejour.sejour_id";
$ljoin["grossesse"] = "sejour.grossesse_id = grossesse.grossesse_id";

$where = array();
// Interv ou travail qui commence le jour choisi et n'a pas terminé d'accoucher
$where[]                      = "operations.date = '$date' OR (
  grossesse.datetime_debut_travail IS NOT NULL AND
  DATE(grossesse.datetime_debut_travail) < '$date' AND
  grossesse.datetime_accouchement IS NULL AND
  grossesse.active = '1'
)";
$where['operations.annulee'] = "= '0'";
$where["operations.chir_id"]  = CSQLDataSource::prepareIn(array_keys($listChirs));
$where["sejour.grossesse_id"] = "IS NOT NULL";

/** @var CStoredObject[] $urgences */
$urgences = $operation->loadGroupList($where, "salle_id, chir_id", null, null, $ljoin);

$sejours  = CStoredObject::massLoadFwdRef($urgences, "sejour_id");
$patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
CStoredObject::massLoadFwdRef($sejours, "grossesse_id");
CStoredObject::massLoadBackRefs($patients, "bmr_bhre");

$plage = new CPlageOp();

/** @var COperation[] $urgences */
foreach ($urgences as &$urgence) {
  $urgence->loadRefsFwd();
  $urgence->loadRefAnesth();
  $urgence->_ref_chir->loadRefsFwd();
  $urgence->isUrgence();

  $sejour  = $urgence->_ref_sejour;
  $patient = $sejour->loadRefPatient();
  $patient->updateBMRBHReStatus($sejour);
  $sejour->loadRefGrossesse();

  // Chargement des plages disponibles pour cette intervention
  $urgence->_ref_chir->loadBackRefs("secondary_functions");
  $secondary_functions = array();
  foreach ($urgence->_ref_chir->_back["secondary_functions"] as $curr_sec_func) {
    $secondary_functions[$curr_sec_func->function_id] = $curr_sec_func;
  }
  $where             = array();
  $selectPlages      = "(plagesop.chir_id = %1 OR plagesop.spec_id = %2
    OR plagesop.spec_id " . CSQLDataSource::prepareIn(array_keys($secondary_functions)) . ")";
  $where[]           = $ds->prepare($selectPlages, $urgence->chir_id, $urgence->_ref_chir->function_id);
  $where["date"]     = "= '$date'";
  $where["salle_id"] = CSQLDataSource::prepareIn(array_keys($listSalles));
  $order             = "salle_id, debut";

  $urgence->_alternate_plages = $plage->loadList($where, $order);
  foreach ($urgence->_alternate_plages as $curr_plage) {
    $curr_plage->loadRefsFwd();
  }
}

$anesth  = new CMediusers();
$anesths = $anesth->loadAnesthesistes(PERM_READ);

$date_last_checklist = array();
foreach ($listSalles as $salle) {
  if ($salle->cheklist_man) {
    $checklist               = new CDailyCheckList();
    $checklist->object_class = $salle->_class;
    $checklist->object_id    = $salle->_id;
    $checklist->loadMatchingObject("date DESC, date_validate DESC");
    if ($checklist->_id) {
      $log               = new CUserLog();
      $log->object_id    = $checklist->_id;
      $log->object_class = $checklist->_class;
      $log->loadMatchingObject("date DESC");
      $date_last_checklist[$salle->_id] = $log->date;
    }
    elseif ($checklist->date) {
      $date_last_checklist[$salle->_id] = $checklist->date;
    }
    else {
      $date_last_checklist[$salle->_id] = "";
    }
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->debugging = false;

$smarty->assign("urgences", $urgences);
$smarty->assign("listBlocs", $listBlocs);
$smarty->assign("listSalles", $listSalles);
$smarty->assign("anesths", $anesths);
$smarty->assign("date", $date);
$smarty->assign("date_last_checklist", $date_last_checklist);

$smarty->display("vw_placement.tpl");
