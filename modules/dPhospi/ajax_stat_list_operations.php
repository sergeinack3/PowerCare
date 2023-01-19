<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\COperation;

$date       = CValue::get("date");
$service_id = CValue::get("service_id");
$group_id   = CGroups::loadCurrent()->_id;

$where = array();
$ljoin = array();
$index = "date";

$where["duree_uscpo"] = "> 0";
$where["annulee"]     = "!= '1'";

$operation = new COperation();
$max_uscpo = $operation->_specs["duree_uscpo"]->max;
// Minimal date will narrow scope and boost index execution greatly
$date_min = CMbDT::date("-$max_uscpo DAY", $date);
$where[]  = "operations.date BETWEEN '$date_min' AND '$date'";
$where[]  = "DATE_ADD(operations.date, INTERVAL duree_uscpo DAY) > '$date'";

$ljoin["sejour"]          = "sejour.sejour_id = operations.sejour_id";
$where["sejour.group_id"] = "= '$group_id'";

if ($service_id) {
  $where["sejour.service_id"] = " = '$service_id'";
}

$where[] = "operations.passage_uscpo = '1' or operations.passage_uscpo IS NULL";

// Prévues
$operations_prevues = $operation->loadList($where, null, null, null, $ljoin, $index);

// Placées
$ljoin["affectation"] = "affectation.sejour_id = operations.sejour_id";
$where[]              = "DATE_ADD(operations.date, INTERVAL duree_uscpo DAY) <= affectation.sortie";
$operations_placees   = $operation->loadList($where, null, null, null, $ljoin, $index);

/** @var COperation[] $operations */
$operations = array_merge($operations_placees, $operations_prevues);
CMbObject::massLoadFwdRef($operations, "plageop_id");
CMbObject::massLoadFwdRef($operations, "chir_id");
foreach ($operations as $_operation) {
  $_operation->loadRefPatient();
  $_operation->loadRefPlageOp();
  $_operation->loadRefChir()->loadRefFunction();
}

$smarty = new CSmartyDP();

$smarty->assign("operations_prevues", $operations_prevues);
$smarty->assign("operations_placees", $operations_placees);

$smarty->display("inc_stat_list_operations.tpl");

