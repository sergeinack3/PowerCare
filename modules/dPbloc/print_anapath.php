<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\PlanningOp\COperation;

/**
 * dPbloc
 */
CCanDo::checkRead();

$date      = CValue::get("date");
$blocs_ids = CValue::get("blocs_ids");

$bloc = new CBlocOperatoire();
$blocs = $bloc->loadList(array("bloc_operatoire_id" => CSQLDataSource::prepareIn($blocs_ids)), "nom");

$operations_tab = array();

foreach ($blocs as $bloc) {
  $in_salles         = CSQLDataSource::prepareIn($bloc->loadBackIds("salles"));
  $ljoin             = array();
  $ljoin["plagesop"] = "operations.plageop_id = plagesop.plageop_id";
  $where             = array();
  $where[]           = "operations.salle_id $in_salles OR plagesop.salle_id $in_salles";
  $where[]           = "operations.date = '$date'";
  $where["anapath"]  = "= 1";
  $order             = "entree_salle, time_operation";

  $operation = new COperation();
  /** @var COperation[] $operations */
  $operations = $operation->loadList($where, $order, null, null, $ljoin);

  CMbObject::massLoadFwdRef($operations, "plageop_id");
  $chirs = CMbObject::massLoadFwdRef($operations, "chir_id");
  CMbObject::massLoadFwdRef($chirs, "function_id");
  $sejours = CMbObject::massLoadFwdRef($operations, "sejour_id");
  CMbObject::massLoadFwdRef($sejours, "patient_id");

  foreach ($operations as $_operation) {
    $_operation->loadRefPatient();
    $_operation->loadRefPlageOp();
    $_operation->updateSalle();
    $_operation->loadRefChir()->loadRefFunction();
    $_operation->loadExtCodesCCAM();
  }

  $operations_tab[$bloc->_id] = $operations;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("date"          , $date);
$smarty->assign("blocs"         , $blocs);
$smarty->assign("operations_tab", $operations_tab);

$smarty->display("print_anapath.tpl");
