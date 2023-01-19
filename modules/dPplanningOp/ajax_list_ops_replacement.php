<?php
/**
 * @package Mediboard\PlanningOp
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
use Ox\Mediboard\Medicament\CMedicament;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkAdmin();

$dm_id            = CView::get("dm_id", "ref class|CDM");
$code_cip         = CView::get("code_cip", "str");
$protocole_op_ids = CView::get("protocole_op_ids", "str");

CView::checkin();

$protocole_op_ids = explode("-", $protocole_op_ids);

CMbArray::removeValue("", $protocole_op_ids);

$operation = new COperation();

$ds = $operation->getDS();

$where = [
  "operations.date" => $ds->prepare(">= ?", CMbDT::date()),
];

$ljoin = [
  "materiel_operatoire" => "materiel_operatoire.operation_id = operations.operation_id"
];

if (count($protocole_op_ids)) {
  $where["materiel_operatoire.protocole_operatoire_id"] = CSQLDataSource::prepareIn($protocole_op_ids);
}

if ($dm_id) {
  $where["materiel_operatoire.dm_id"] = $ds->prepare("= ?", $dm_id);
}
elseif ($code_cip) {
  $bdm = CMedicament::getBase();

  $where["materiel_operatoire.code_cip"] = $ds->prepare("= ?", $code_cip);
  $where["materiel_operatoire.bdm"]      = $ds->prepare("= ?", $bdm);
}

$operations = $operation->loadList($where, "date DESC", null, null, $ljoin);

CStoredObject::massLoadFwdRef($operations, "plageop_id");
$sejours = CStoredObject::massLoadFwdRef($operations, "sejour_id");

CStoredObject::massLoadFwdRef($sejours, "patient_id");

foreach ($operations as $_operation) {
  $_operation->loadRefPatient();
  $_operation->loadRefPlageOp();
}

$smarty = new CSmartyDP();

$smarty->assign("operations", $operations);

$smarty->display("inc_list_ops_replacement");
