<?php
/**
 * @package Mediboard\Stats
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
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\COperationWorkflow;

CCanDo::checkRead();

$type_modif = CValue::getOrSession("type_modif", "annule");
$date_max   = CValue::getOrSession("_date_max", CMbDT::date());
$date_min   = CValue::get("_date_min", $date_max);

$prat_id   = CValue::get("prat_id");
$salle_id  = CValue::get("salle_id");
$bloc_id   = CValue::get("bloc_id");
$code_ccam = CValue::get("code_ccam");

CView::enforceSlave();

$prat = new CMediusers;
$prat->load($prat_id);

$date_min = CMbDT::date("first day of -0 months", $date_min);
$date_max = CMbDT::date("last day of +0 months", $date_max);

$salles = CSalle::getSallesStats($salle_id, $bloc_id);

$miner = new COperationWorkflow();
$miner->warnUsage();

$operation = new COperation();

$ljoin["operation_workflow"] = "operation_workflow.operation_id = operations.operation_id";

$where["date_operation"] = "BETWEEN '$date_min' AND '$date_max'";
$where["salle_id"]       = CSQLDataSource::prepareIn(array_keys($salles));
$where[]                 = ($type_modif == "annule") ?
  "DATE(date_operation) = DATE(date_cancellation)" :
  "DATE(date_operation) = DATE(date_creation)";

// Filtre sur le praticien
if ($prat_id) {
  $where["operations.chir_id"] = "= '$prat_id'";
}

// Filtre sur les codes CCAM
if ($code_ccam) {
  $where["operations.codes_ccam"] = "LIKE '%$code_ccam%'";
}

$order = "date_operation, salle_id";

/** @var COperation[] $operations */
$operations = $operation->loadList($where, $order, null, null, $ljoin);
$list       = array();
$counts     = array();
CStoredObject::massLoadFwdRef($operations, "plageop_id");
$sejours = CStoredObject::massLoadFwdRef($operations, "sejour_id");
CStoredObject::massLoadFwdRef($sejours, "patient_id");
$praticiens = CStoredObject::massLoadFwdRef($operations, "chir_id");
CStoredObject::massLoadFwdRef($praticiens, "function_id");

foreach ($operations as $_operation) {
  $_operation->loadRefPlageOp();
  $_operation->loadRefPatient();
  $_operation->loadRefPraticien()->loadRefFunction();
  $month = CMbDT::format($_operation->_datetime, "%Y-%m");
  $plage = $_operation->plageop_id ? "inPlage" : "horsPlage";
  if (!isset($counts[$month])) {
    $counts[$month] = 0;
  }
  $counts[$month]++;
  $list[$month][$plage][$_operation->_id] = $_operation;
}

// Affichage vide
if (empty($counts)) {
  $month          = CMbDT::format($date_max, "%Y-%m");
  $counts[$month] = 0;
  $list[$month]   = array();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("list", $list);
$smarty->assign("counts", $counts);
$smarty->assign("date_max", $date_max);
$smarty->assign("type_modif", $type_modif);

$smarty->display("vw_cancelled_operations.tpl");