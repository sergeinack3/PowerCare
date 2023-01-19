<?php
/**
 * @package Mediboard\
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Medicament\CMedicament;
use Ox\Mediboard\PlanningOp\CMaterielOperatoire;

CCanDo::checkAdmin();

$protocole_op_ids = CView::post("protocole_op_ids", "str");
$operation_ids    = CView::post("operation_ids", "str");
$mode_operation   = CView::post("mode_operation", "bool default|0");
$dm_id_from       = CView::post("dm_id_from", "ref class|CDM");
$code_cip_from    = CView::post("code_cip_from", "str");
$dm_id_to         = CView::post("dm_id_to", "ref class|CDM");
$code_cip_to      = CView::post("code_cip_to", "str");

CView::checkin();

$protocole_op_ids = explode("-", $protocole_op_ids);

CMbArray::removeValue("", $protocole_op_ids);

$operation_ids = explode("-", $operation_ids);

CMbArray::removeValue("", $operation_ids);

$bdm = CMedicament::getBase();

$materiel_op = new CMaterielOperatoire();

$ds = $materiel_op->getDS();

$modify = false;

$where = [
  "materiel_operatoire.operation_id" => "IS NULL",
];

$ljoin = [];

if (is_countable($protocole_op_ids) && count($protocole_op_ids)) {
  $where["protocole_operatoire_id"] = CSQLDataSource::prepareIn($protocole_op_ids);
}
elseif (is_countable($operation_ids) && count($operation_ids)) {
  $where["materiel_operatoire.operation_id"] = CSQLDataSource::prepareIn($operation_ids);
}
elseif ($mode_operation) {
  $where["materiel_operatoire.operation_id"] = "IS NOT NULL";
  $where["operations.date"] = $ds->prepare(">= ?", CMbDT::date());

  $ljoiin["operations"] = "operations.operation_id = materiel_operatoire.operation_id";
}

if ($dm_id_from) {
  $where["dm_id"] = "= '$dm_id_from'";
}
elseif ($code_cip_from) {
  $where["code_cip"] = "= '$code_cip_from'";
  $where["bdm"]      = "= '$bdm'";
}

$materiel_ops = $materiel_op->loadList($where, null, null, null, $ljoin);

$modify = count($materiel_ops) !== 0;

if ($modify && !$dm_id_to && !$code_cip_to) {
  $modify = false;
}

if (!$modify) {
  CAppUI::setMsg("CMaterielOperatoire-No DM or product found");

  echo CAppUI::getMsg();

  return;
}

$count = $count_error = $count_modify = 0;
$msgs  = [];

CMaterielOperatoire::$_skip_invalidation = true;

foreach ($materiel_ops as $_materiel_op) {
  $count++;

  if ($dm_id_to) {
    $_materiel_op->dm_id    = $dm_id_to;
    $_materiel_op->code_cip = $_materiel_op->bdm = "";
  }
  elseif ($code_cip_to) {
    $_materiel_op->code_cip = $code_cip_to;
    $_materiel_op->bdm      = $bdm;
    $_materiel_op->dm_id    = "";
  }

  if ($msg = $_materiel_op->store()) {
    $msgs[] = $msg;
    $count_error++;
  }
}

CMaterielOperatoire::$_skip_invalidation = false;

$count_modify = $count - $count_error;

CAppUI::setMsg("CMaterielOperatoire-Count of materiel modifiyed", $count_error ? UI_MSG_WARNING : UI_MSG_OK, $count_modify, $count);

foreach ($msgs as $_msg) {
  CAppUI::setMsg($_msg, UI_MSG_WARNING);
}

echo CAppUI::getMsg();

if (is_countable($operation_ids) && count($operation_ids)) {
  return;
}

// Si des interventions futures ont du matériel opératoire de l'ancien marché, on ouvre une modale pour permettre d'appliquer
// le même changement

$ds = $materiel_op->getDS();

$where["materiel_operatoire.operation_id"] = "IS NOT NULL";
$where['materiel_operatoire.protocole_operatoire_id'] = CSQLDataSource::prepareIn($protocole_op_ids);
$where["operations.date"] = $ds->prepare(">= ?", CMbDT::date());

$ljoin = [
  "operations" => "operations.operation_id = materiel_operatoire.operation_id"
];

if ($materiel_op->countList($where, null, $ljoin) > 0) {
  CAppUI::callbackAjax("ProtocoleOp.seeOperationsReplacement", implode("-", $protocole_op_ids));
}
