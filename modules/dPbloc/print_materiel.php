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
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();
$function_id    = CView::get("function_id", "ref class|CFunctions", true);
$praticiens_ids = CView::get("praticiens_ids", "str", true);
$type_commande  = CView::get("type_commande", "str default|bloc", true);
$blocs_ids      = CView::get("blocs_ids", 'str', true);

$filter            = new COperation;
$filter->_date_min = CView::get("_date_min", "date default|" . CMbDT::date("-7 day"), true);
$filter->_date_max = CView::get("_date_max", "date default|now", true);
CView::checkin();

$listBlocs = CGroups::loadCurrent()->loadBlocs(PERM_READ, null, "nom");

$where = array();
if (is_array($blocs_ids)) {
  CMbArray::removeValue(0, $blocs_ids);

  $where["bloc_id"] = CSQLDataSource::prepareIn($blocs_ids);

  // noms des blocs
  foreach ($listBlocs as $_bloc) {
    if (in_array($_bloc->_id, $blocs_ids)) {
      $blocs[] = $_bloc->_view;
    }
  }

  $blocs_names = implode(", ", $blocs);
}
else {
  $blocs_ids   = array();
  $blocs_ids[] = reset($listBlocs)->_id;
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

CStoredObject::filterByPerm($salles, PERM_READ);
$in_salles = CSQLDataSource::prepareIn(array_keys($salles));

$where   = array();
$where[] = "plagesop.salle_id $in_salles OR operations.salle_id $in_salles";
if ($type_commande == "bloc") {
  $where["materiel"] = "!= ''";
}
else {
  $where["materiel_pharma"] = "!= ''";
}
$where["operations.date"] = "BETWEEN '$filter->_date_min' AND '$filter->_date_max'";

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
$ops       = $operation->loadList($where, $order, null, null, $ljoin);

$operations = array(
  "commandee"   => array(),
  "a_commander" => array(),
);

$sejours = CStoredObject::massLoadFwdRef($ops, "sejour_id");
CStoredObject::massLoadFwdRef($sejours, "patient_id");
CStoredObject::massLoadFwdRef($ops, "chir_id");
CStoredObject::massLoadFwdRef($ops, "plageop_id");
CStoredObject::massLoadBackRefs($ops, "commande_op", null, array('type' => "= '$type_commande'"));

foreach ($ops as $_op) {
  /** @var COperation $_op */
  $_op->loadRefPatient();
  $_op->loadRefChir()->loadRefFunction();
  $_op->loadRefPlageOp();
  $_op->loadExtCodesCCAM();
  $_op->loadRefCommande($type_commande);
  $_commande = $_op->_ref_commande_mat[$type_commande];
  if (!$_commande->_id && !$_op->annulee) {
    $operations["a_commander"][$_op->_id] = $_op;
  }
  elseif ($_commande->_id && ($_commande->etat == "commandee" || $_commande->etat == "a_commander")) {
    $operations[$_commande->etat][$_op->_id] = $_op;
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("filter"       , $filter);
$smarty->assign("blocs_names"  , $blocs_names);
$smarty->assign("operations"   , $operations);
$smarty->assign("type_commande", $type_commande);

$smarty->display("print_materiel.tpl");
