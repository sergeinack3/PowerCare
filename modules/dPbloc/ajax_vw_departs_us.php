<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

$date_depart = CValue::get("date_depart");
$bloc_id     = CValue::get("bloc_id");
$order_way   = CValue::get("order_way");
$order_col   = CValue::get("order_col");

$where = array();
$where["date"] = " = '".CMbDT::date($date_depart)."'";
$where["annulee"] = " = '0'";

$ljoin = array();
$ljoin["sallesbloc"] = "sallesbloc.salle_id = operations.salle_id";
if ($bloc_id) {
  $where["sallesbloc.bloc_id"] = "= '$bloc_id'";
}
else {
  $bloc = new CBlocOperatoire();
  $where_bloc["group_id"] = " = '".CGroups::loadCurrent()->_id."'";
  $where_bloc["actif"]    = " = '1'";
  $blocs = $bloc->loadListWithPerms(PERM_READ, $where_bloc, "nom");
  $where["sallesbloc.bloc_id"] = CSQLDataSource::prepareIn(array_keys($blocs));
}

$operation = new COperation();
/** @var COperation[] $operations */
$operations = $operation->loadList($where, "time_operation ASC", null, null, $ljoin);
$sejours = CMbObject::massLoadFwdRef($operations, "sejour_id");
CMbObject::massLoadFwdRef($sejours, "patient_id");
CMbObject::massLoadFwdRef($operations, "salle_id");
CMbObject::massLoadFwdRef($operations, "plageop_id");
CSejour::massLoadCurrAffectation($sejours, $date_depart);

foreach ($operations as $_operation) {
  $_operation->loadRefPlageOp();
  $_operation->updateSalle();
  $_operation->updateHeureUS();
  $sejour = $_operation->loadRefSejour();
  $affectation = $sejour->_ref_curr_affectation;
  $affectation->loadView();
  $sejour->loadRefPatient();
}

// Tri à posteriori
switch ($order_col) {
  case "nom" :
    $sorter = CMbArray::pluck($operations, "_ref_sejour", "_ref_patient", "nom");
    break;
  case "time_operation":
    $sorter = CMbArray::pluck($operations, "time_operation");
    break;
  case "salle_id":
    $sorter = CMbArray::pluck($operations, "salle_id");
    break;
  case "_heure_us":
  default :
    $sorter = CMbArray::pluck($operations, "_heure_us");
}

array_multisort($sorter, $order_way == "ASC" ? SORT_ASC : SORT_DESC, $operations);

$smarty = new CSmartyDP();

$smarty->assign("operations", $operations);
$smarty->assign("order_way" , $order_way);
$smarty->assign("order_col" , $order_col);

$smarty->display("inc_vw_departs_us.tpl");
