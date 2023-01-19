<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$date        = CView::get("date", array('date', 'default' => CMbDT::date()), true);
$blocs_ids   = CView::get("blocs_ids", 'str', true);
$type_sejour = CView::get("type_sejour", 'enum list|' . implode("|", CSejour::$types));
$reload      = CView::get("reload", 'bool default|0');
$type_view   = CView::get("type_view", 'enum list|all|interv|sspi default|all', true); //all|interv|sspi
$period      = CView::get("period", 'str', true);
$services_ids = CView::get("services_ids", "str", true);

if($services_ids && $services_ids[0] === "") {
  $services_ids = CView::setSession("suivi_services_ids", null);
}

CView::checkin();

$group = CGroups::loadCurrent();
if ($blocs_ids) {
  CMbArray::removeValue(0, $blocs_ids);
}

/** @var CBlocOperatoire[] $blocs */
$blocs  = CGroups::loadCurrent()->loadBlocs(PERM_READ, null, "nom");

// Liste des services
$service = new CService();
$where_service = array();
$where_service["group_id"]  = "= '$group->_id'";
$where_service["cancelled"] = "= '0'";
$order_service = "nom";
/** @var CService[] $services */
$services = $service->loadListWithPerms(PERM_READ, $where_service, $order_service);

if ($blocs_ids) {
  $bloc = new CBlocOperatoire();
  $liste_blocs = $bloc->loadList(array("bloc_operatoire_id" => CSQLDataSource::prepareIn($blocs_ids)));
  CStoredObject::massLoadBackRefs($liste_blocs, "salles", "nom");
  $salles = array();
  foreach ($liste_blocs as $_bloc) {
    foreach ($_bloc->loadRefsSalles() as $_salle) {
      $salles[$_salle->_id] = $_salle;
    }
  }
}
else {
  $ljoin = array();
  $ljoin["bloc_operatoire"] = "bloc_operatoire.bloc_operatoire_id = sallesbloc.bloc_id";
  $where = array();
  $where["sallesbloc.bloc_id"] = CSQLDataSource::prepareIn(array_keys($blocs));
  $salle = new CSalle();
  $salles = $salle->loadList($where, "bloc_operatoire.nom, sallesbloc.nom", null, "sallesbloc.salle_id", $ljoin);
}

$date_min = CMbDT::time("00:00:00");
$date_max = CMbDT::time("23:59:59");
if ($period) {
  $hour = CAppUI::gconf("dPadmissions General hour_matin_soir");
  if ($period == "matin") {
    $date_max = $hour;
  }
  else {
    $date_min = $hour;
  }
}

$operations = array();
if (count($salles)) {
  $ljoin = array();
  $ljoin["plagesop"] = "plagesop.plageop_id = operations.plageop_id";
  $ljoin["sejour"]   = "sejour.sejour_id = operations.sejour_id";
  $where = array();
  $where["plagesop.salle_id"]  = CSQLDataSource::prepareIn(array_keys($salles));
  $where["plagesop.date"]      = " = '$date'";
  $where["operations.rank"]    = " IS NOT NULL";
  $where["operations.annulee"] = " = '0'";
  if ($period) {
    $where["operations.time_operation"] = " BETWEEN '$date_min' AND '$date_max'";
  }
  $where["sejour.type"]        = " <> 'exte'";
  if ($type_sejour) {
    $where["sejour.type"] = " = '$type_sejour'";
  }
  $operation = new COperation();
  $operations = $operation->loadList($where, "operations.time_operation DESC", null, "operations.operation_id", $ljoin);

  $where = array();
  $where["operations.salle_id"]   = CSQLDataSource::prepareIn(array_keys($salles));
  $where["operations.plageop_id"] = " IS NULL";
  $where["operations.date"]       = " = '$date'";
  $where["operations.annulee"]    = " = '0'";
  if ($period) {
    $where["operations.time_operation"] = " BETWEEN '$date_min' AND '$date_max'";
  }
  $where["sejour.type"]           = " <> 'exte'";
  if ($type_sejour) {
    $where["sejour.type"]        = " = '$type_sejour'";
  }
  $operation = new COperation();
  $operations_hp = $operation->loadList($where, "operations.time_operation DESC", null, "operations.operation_id", $ljoin);

  // Fusion des deux tableaux d'interv
  $operations = array_merge($operations, $operations_hp);
}

//Création des tableaux vides pour les ordonner par ordre des salles
$op_salles = $sspis = array();
foreach ($salles as $_salle) {
  $op_salles[$_salle->_id] = array(
    'op' => array(),
    'op_hp' => array(),
    'brancardage' => array(),
    'interv' => array(),
  );
  $sspis["attente"][$_salle->_id] = array();
  $sspis["sspi"][$_salle->_id]    = array();
}

CStoredObject::massLoadFwdRef($operations, "plageop_id");
$chirs = CStoredObject::massLoadFwdRef($operations, "chir_id");
$anesths = CStoredObject::massLoadFwdRef($operations, "anesth_id");
CStoredObject::massLoadFwdRef($anesths, "function_id");
$sejours = CStoredObject::massLoadFwdRef($operations, "sejour_id");
CStoredObject::massLoadFwdRef($sejours, "patient_id");

foreach ($operations as $_key => $_op) {
  /* @var COperation $_op*/
  $_op->loadRefAffectation(false);
  if ($services_ids &&
    (!$_op->_ref_affectation->_id ||
      ($_op->_ref_affectation->service_id && !in_array($_op->_ref_affectation->service_id, $services_ids)))) {
    unset($operations[$_key]);
    continue;
  }
  $_op->loadRefPatient();
  $_op->loadRefPlageOp();
  $_op->loadRefChir()->loadRefFunction();
  $_op->loadRefAnesth()->loadRefFunction();
  if (!$_op->_ref_chir->canDo()->read) {
      unset($operations[$_key]);
      continue;
  }
  if ($_op->_ref_affectation) {
    $_op->_ref_affectation->loadRefLit();
  }
  $salle = $_op->_ref_salle;
  $brancardage = $_op->loadRefBrancardage();
  $first_brancardage = $brancardage->loadFirstByOperation($_op);
  $type_op = "";
  if ($_op->entree_salle && !$_op->sortie_salle) {
    $type_op = "interv";
  }
  elseif (
      !$_op->entree_salle
      && $brancardage
      && $brancardage->_id
      && ($first_brancardage->_ref_demande_brancardage
      || $first_brancardage->_ref_prise_en_charge
      || $first_brancardage->_ref_patient_pret)
  ) {
    $type_op = "brancardage";
  }
  elseif (!$_op->entree_salle) {
    $type_op = $_op->plageop_id ? "op" : "op_hp";
  }
  if ($type_op) {
    $op_salles[$salle->_id][$type_op][$_op->_id] = $_op;
  }

  if ($_op->sortie_salle) {
    $type_sspi = "attente";
    if (!$_op->entree_reveil) {
      $sspis["attente"][$salle->_id][] = $_op;
    }
    elseif (!$_op->sortie_reveil_reel) {
      $sspis["sspi"][$salle->_id][] = $_op;
    }
  }
}

//Suppression des salles vides
foreach ($op_salles as $_salle_id => $_ops_salle) {
  if (!count($_ops_salle["op"]) && !count($_ops_salle["op_hp"]) && !count($_ops_salle["brancardage"]) && !count($_ops_salle["interv"])) {
    unset($op_salles[$_salle_id]);
  }
}
foreach ($sspis as $type => $_ops_by_type) {
  if ($type == "attente") {
    foreach ($_ops_by_type as $_salle_id => $_ops_attente) {
      if (!count($_ops_attente)) {
        unset($sspis[$type][$_salle_id]);
      }
    }
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("op_salles"     , $op_salles);
$smarty->assign("sspis"         , $sspis);
$smarty->assign("type_view"     , $type_view);
$smarty->assign("salles"        , $salles);
$smarty->assign("blocs"         , $blocs);
$smarty->assign("blocs_ids"     , $blocs_ids);
$smarty->assign("services"      , $services);
$smarty->assign("services_ids"  , $services_ids);
$smarty->assign("period"        , $period);

if ($reload) {
  $smarty->display("inc_vw_suivi_bloc");
}
else {
  $smarty->assign("date"    , $date);

  $smarty->display("vw_suivi_bloc");
}
