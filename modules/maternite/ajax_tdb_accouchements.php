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
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Liste des accouchements en cours du tableau de bord
 */
CCanDo::checkRead();

$date         = CView::get("date", "date default|now");
$see_finished = CView::get("see_finished", "bool default|1");

CView::checkin();

$date_min = CMbDT::date("-1 DAY", $date);
$group    = CGroups::loadCurrent();

$op    = new COperation();
$ljoin = array(
  "sejour"    => "sejour.sejour_id = operations.sejour_id",
  "grossesse" => "sejour.grossesse_id = grossesse.grossesse_id");
$where = array(
  "sejour.grossesse_id" => "IS NOT NULL",
  "sejour.group_id"     => "= '$group->_id' ",
  "date"                => "BETWEEN '$date_min' AND '$date'",
  "operations.annulee"  => "= '0'",
  "sejour.annule"       => "= '0'"
);

// accouchement non terminé = datetime_accouche IS NULL
// ne pas voir les terminés
if (!$see_finished) {
  //$where["grossesse.datetime_accouchement"] = " IS NULL";
  $where["grossesse.active"] = " = '1'";
}

//blocs
$bloc           = new CBlocOperatoire();
$bloc->type     = "obst";
$bloc->group_id = $group->_id;
/** @var CBlocOperatoire[] $blocs */
$blocs  = $bloc->loadMatchingList();
$salles = array();
CStoredObject::massLoadBackRefs($blocs, "salles", "nom");
foreach ($blocs as $_bloc) {
  $salles = $_bloc->loadRefsSalles();
  foreach ($salles as $_salle) {
    $salles[$_salle->_id] = $_salle->_id;
  }
}

// anesth
$anesth  = new CMediusers();
$anesths = $anesth->loadAnesthesistes();

/** @var COperation[] $ops */
$ops = $op->loadList($where, "date DESC, time_operation", null, null, $ljoin);
CStoredObject::massLoadFwdRef($ops, "plageop_id");
CStoredObject::massLoadFwdRef($ops, "anesth_id");
$chirs = CStoredObject::massLoadFwdRef($ops, "chir_id");
CStoredObject::massLoadFwdRef($chirs, "function_id");
$sejours = CStoredObject::massLoadFwdRef($ops, "sejour_id");
CSejour::massLoadCurrAffectation($sejours);
$grossesses = CStoredObject::massLoadFwdRef($sejours, "grossesse_id");
$patientes  = CStoredObject::massLoadFwdRef($grossesses, "parturiente_id");
CStoredObject::massLoadBackRefs($patientes, "bmr_bhre");
CStoredObject::massLoadBackRefs($grossesses, "naissances");

foreach ($ops as $_op) {
  $_op->loadRefChir()->loadRefFunction();
  $_op->loadRefPlageOp();
  $sejour    = $_op->loadRefSejour();
  $grossesse = $sejour->loadRefGrossesse();
  $grossesse->loadRefsNaissances();
  $grossesse->loadRefParturiente()->updateBMRBHReStatus($_op);
  $dossier = $grossesse->loadRefDossierPerinat();
}

$smarty = new CSmartyDP();
$smarty->assign("date", $date);
$smarty->assign("see_finished", $see_finished);
$smarty->assign("ops", $ops);
$smarty->assign("blocs", $blocs);
$smarty->assign("salles" , $salles);
$smarty->assign("anesths", $anesths);
$smarty->display("inc_tdb_accouchements");
