<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CTypeAnesth;

CCanDo::checkEdit();

$salles_ids      = CView::get("salles_ids", "str");
$date            = CView::get("date", "date");
$chir_id         = CView::get("chir_id", "num");
$distinct_plages = CView::get("distinct_plages", "bool default|0", true);

CView::checkin();

$anesth = new CTypeAnesth();
$anesth = $anesth->loadGroupList();

$plages = array();
$operations = array();

$plage = new CPlageOp();

$where = array(
  "plagesop.salle_id" => CSQLDataSource::prepareIn($salles_ids),
  "plagesop.date"     => "= '$date'",
  "plagesop.chir_id"  => "= '$chir_id'"
);

$plages = $plage->loadList($where);

foreach ($plages as $_plage) {
  $_plage->loadRefsFwd();

  $ops = $_plage->loadRefsOperations(true, "rank, rank_voulu, horaire_voulu", true, false);

  $chirs = CStoredObject::massLoadFwdRef($ops, "chir_id");
  CStoredObject::massLoadFwdRef($chirs, "function_id");
  $sejours = CMbObject::massLoadFwdRef($ops, "sejour_id");
  CStoredObject::massLoadFwdRef($sejours, "patient_id");
  CStoredObject::massCountBackRefs($ops, "affectations_personnel");

  foreach ($ops as $_op) {
    $_op->loadRefsFwd();
    $_op->_ref_chir->loadRefFunction();
    $_op->_ref_sejour->loadRefsFwd();
    $_op->loadRefsCommande();
    $_op->_count_affectations_personnel = $_op->countBackRefs("affectations_personnel");
    $patient = $_op->_ref_sejour->_ref_patient;
    $patient->loadRefDossierMedical();
    $patient->_ref_dossier_medical->countAllergies();
  }

  $plages[$_plage->_id] = $_plage;

  $operations = array_merge($operations, $ops);
}

$ordered_operations = CMbArray::pluck($operations, "horaire_voulu");
array_multisort($ordered_operations, SORT_ASC, $operations);

$chir = new CMediusers();
$chir->load($chir_id);
$chir->loadRefFunction();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("plages"         , $plages);
$smarty->assign("operations"     , $operations);
$smarty->assign("chir"           , $chir);
$smarty->assign("anesth"         , $anesth);
$smarty->assign("date"           , $date);
$smarty->assign("list_type"      , "left");
$smarty->assign("listPlages"     , array());
$smarty->assign("distinct_plages", $distinct_plages);
$smarty->assign("multisalle"     , 1);

$smarty->display($distinct_plages ? "inc_list_operations_multisalle.tpl" : "inc_list_operations.tpl");
