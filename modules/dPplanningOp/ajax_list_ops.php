<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkEdit();

$operation = new COperation();
$now = CMbDT::date();

$operation->_prepa_dt_min            = CView::get("_prepa_dt_min", ["dateTime", "default" => "$now 00:00:00"], true);
$operation->_prepa_dt_max            = CView::get("_prepa_dt_max", ["dateTime", "default" => "$now 23:59:59"], true);
$operation->_prepa_chir_id           = CView::get("_prepa_chir_id", "ref class|CMediusers", true);
$operation->_prepa_spec_id           = CView::get("_prepa_spec_id", "ref class|CFunctions", true);
$operation->_prepa_bloc_id           = CView::get("_prepa_bloc_id", "ref class|CBlocOperatoire", true);
$operation->_prepa_salle_id          = CView::get("_prepa_salle_id", "ref class|CSalle", true);
$operation->_prepa_urgence           = CView::get("_prepa_urgence", "bool default|0", true);
$operation->_prepa_libelle           = CView::get("_prepa_libelle", "str", true);
$operation->_prepa_libelle_prot      = CView::get("_prepa_libelle_prot", "str", true);
$operation->_prepa_order_col         = CView::get("_prepa_order_col", "str default|nom", true);
$operation->_prepa_order_way         = CView::get("_prepa_order_way", "str default|ASC", true);
$operation->_filter_panier           = CView::get("_filter_panier", "str", true);
$operation->_prepa_type_intervention = CView::get("_prepa_type_intervention",
                                                   "enum list|hors_plage|avec_plage|tous default|tous", true);
$operation_id                        = CView::get("operation_id", "ref class|COperation");

CView::checkin();

$ds = $operation->getDS();

if ($operation_id) {
  $operation->load($operation_id);

  CAccessMedicalData::logAccess($operation);

  $operations[$operation->_id] = $operation;
}
else {
  $group = CGroups::loadCurrent();

  $where = [
    "sejour.group_id" => $ds->prepare("= ?", $group->_id),
    "CONCAT(operations.date, ' ', operations.time_operation) BETWEEN '$operation->_prepa_dt_min' AND '$operation->_prepa_dt_max'",
    "operations.urgence" => $ds->prepare("= ?", $operation->_prepa_urgence),
  ];

  $ljoin = [
    "sejour"              => "sejour.sejour_id = operations.sejour_id",
    "materiel_operatoire" => "materiel_operatoire.operation_id = operations.operation_id"
  ];

  if ($operation->_filter_panier === "missing" || $operation->_prepa_libelle_prot) {
    $where["materiel_operatoire.materiel_operatoire_id"] = $ds->prepare("IS NOT NULL");
  }

  if ($operation->_prepa_chir_id) {
    $where["operations.chir_id"] = $ds->prepare("= %", $operation->_prepa_chir_id);
  }
  elseif ($operation->_prepa_spec_id) {
    $user  = new CMediusers();
    $chirs = $user->loadPraticiens(PERM_READ, $operation->_prepa_spec_id);

    $where["operations.chir_id"] = CSQLDataSource::prepareIn(array_keys($chirs));
  }

  if ($operation->_prepa_salle_id) {
    $where["operations.salle_id"] = $ds->prepare("= %", $operation->_prepa_salle_id);
  }
  elseif ($operation->_prepa_bloc_id) {
    $bloc = new CBlocOperatoire();
    $bloc->load($operation->_prepa_bloc_id);

    $where["operations.salle_id"] = CSQLDataSource::prepareIn(array_keys($bloc->loadRefsSalles()));
  }


  if ($operation->_prepa_libelle) {
    $where[] = $ds->prepareLikeMulti(addslashes($operation->_prepa_libelle), 'libelle', 'operations');
  }

  switch ($operation->_prepa_type_intervention) {
    case "avec_plage" :
      $where["operations.plageop_id"] = $ds->prepare("IS NOT NULL");
      break;
    case "hors_plage":
      $where["operations.plageop_id"] = $ds->prepare("IS NULL");
      break;
    default:
      /* Do nothing */
      break;
  }

  $operations = $operation->loadList($where, null, null, "operations.operation_id", $ljoin);
}

$sejours  = CStoredObject::massLoadFwdRef($operations, "sejour_id");
$patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
CStoredObject::massLoadFwdRef($operations, "salle_id");

if ($operation->_filter_panier === "missing" || $operation->_prepa_libelle_prot) {
  CStoredObject::massLoadBackRefs($operations, "materiels_operatoires");
}

$tokens = [];
if ($operation->_prepa_libelle_prot) {
  $tokens = str_replace(" ", "|", strtolower(preg_quote($operation->_prepa_libelle_prot)));
}

foreach ($operations as $_operation) {
  $_operation->computeStatusPanier();

  if (!$operation_id && $operation->_filter_panier) {
    if ($operation->_filter_panier === "ok" && $_operation->_status_panier !== "complete") {
      unset($operations[$_operation->_id]);
      continue;
    }
    elseif ($operation->_filter_panier === "missing" && $_operation->_status_panier !== "incomplete") {
      unset($operations[$_operation->_id]);
      continue;
    }
  }

  $_operation->loadRefsProtocolesOperatoires();

  if ($operation->_prepa_libelle_prot) {
    $keep_op = false;
    foreach ($_operation->_ref_protocoles_operatoires as $_protocole_operatoire) {
      if (!$keep_op && preg_match("/$tokens/", strtolower($_protocole_operatoire->libelle))) {
        $keep_op = true;
        break;
      }
    }

    if (!$keep_op) {
      unset($operations[$_operation->_id]);
      continue;
    }
  }

  $_operation->loadRefPatient();
  $_operation->loadRefSalle();

  if ($operation->_filter_panier === "missing") {
    $_operation->loadRefsMaterielsOperatoires(true, true);
  }
}

$order_way = $operation->_prepa_order_way === "ASC" ? SORT_ASC : SORT_DESC;

switch ($operation->_prepa_order_col) {
  default:
  case "nom":
    CMbArray::pluckSort($operations, $order_way, "_ref_patient", "nom");
    break;
  case "libelle":
    CMbArray::pluckSort($operations, $order_way, "libelle");
    break;
  case "salle_id":
    CMbArray::pluckSort($operations, $order_way, "_ref_salle", "nom");
    break;
  case "numero_panier":
    CMbArray::pluckSort($operations, $order_way, "numero_panier");
    break;
}


// Création du template
$smarty = new CSmartyDP();

$smarty->assign("operations", $operations);
$smarty->assign("operation", $operation);

if ($operation->_id) {
  $smarty->assign("_operation", $operation);
}

$smarty->display($operation->_id ? "inc_line_op" : "inc_list_ops");
