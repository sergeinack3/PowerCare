<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CCommandeMaterielOp;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkEdit();
$date_min       = CView::get("_date_min", "date default|" . CMbDT::date("-7 day"), true);
$date_max       = CView::get("_date_max", "date default|now", true);
$blocs_ids      = CView::get("blocs_ids", 'str', true);
$praticiens_ids = CView::get("praticiens_ids", 'str', true);
$function_id    = CView::get("function_id", "ref class|CFunctions", true);
$type_commande  = CView::get("type_commande", "str default|bloc", true);
CView::checkin();

$where = array();
if (is_array($blocs_ids)) {
  CMbArray::removeValue(0, $blocs_ids);
  $where["bloc_id"] = CSQLDataSource::prepareIn($blocs_ids);
}
else {
  $listBlocs = CGroups::loadCurrent()->loadBlocs(PERM_READ, null, "nom");

  foreach ($listBlocs as $_bloc) {
    $blocs_ids[] = $_bloc->_id;
  }
}

if (is_array($praticiens_ids)) {
  CMbArray::removeValue(0, $praticiens_ids);
}

// Récupération des salles
$salle  = new CSalle();
$salles = $salle->loadListWithPerms(PERM_READ, $where);

// Récupération des opérations
$ljoin             = array();
$ljoin["plagesop"] = "operations.plageop_id = plagesop.plageop_id";

$where     = array();
$in_salles = CSQLDataSource::prepareIn(array_keys($salles));
$where[]   = "plagesop.salle_id $in_salles  OR operations.salle_id $in_salles";
if ($type_commande == "bloc") {
  $where["materiel"] = "!= ''";
}
else {
  $where["materiel_pharma"] = "!= ''";
}
$where[] = " operations.date BETWEEN '$date_min' AND '$date_max'";

if (!empty($praticiens_ids)) {
  $where["operations.chir_id"] = CSQLDataSource::prepareIn($praticiens_ids);
}
elseif ($function_id) {
  $mediuser                    = new CMediusers();
  $users                       = $mediuser->loadProfessionnelDeSante(PERM_READ, $function_id);
  $where["operations.chir_id"] = CSQLDataSource::prepareIn(array_keys($users));
}

$order = "operations.date, rank";

$operation = new COperation();
$ops       = $operation->loadList($where, $order, null, "operation_id", $ljoin);

$operations = array();
$commande   = new CCommandeMaterielOp();
foreach ($commande->_specs["etat"]->_list as $spec) {
  $operations[$spec] = array();
}

$sejours = CStoredObject::massLoadFwdRef($ops, "sejour_id");
CStoredObject::massLoadFwdRef($sejours, "patient_id");
CStoredObject::massLoadFwdRef($ops, "chir_id");
CStoredObject::massLoadFwdRef($ops, "plageop_id");

foreach ($ops as $_op) {
  /** @var COperation $_op */
  $_op->loadRefPatient();
  $_op->loadRefChir()->loadRefFunction();
  $_op->loadRefPlageOp();
  $_op->loadExtCodesCCAM();
  $_op->loadRefCommande($type_commande);
  if (!$_op->_ref_commande_mat[$type_commande]->_id && !$_op->annulee) {
    $operations["a_commander"][$_op->_id] = $_op;
  }
  elseif ($_op->_ref_commande_mat[$type_commande]->_id) {
    $operations[$_op->_ref_commande_mat[$type_commande]->etat][$_op->_id] = $_op;
  }
}

$smarty = new CSmartyDP();

$smarty->assign("operations"    , $operations);
$smarty->assign("type_commande" , $type_commande);
$smarty->assign("blocs_ids"     , $blocs_ids);
$smarty->assign("praticiens_ids", $praticiens_ids);

$smarty->display("inc_vw_materiel.tpl");
