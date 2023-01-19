<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CMaterielOperatoire;

CCanDo::checkEdit();

$operations_ids = CView::post("operations_ids", "str");

CView::checkin();

$operations_ids = explode("-", $operations_ids);

CMbArray::removeValue("", $operations_ids);

if (!count($operations_ids)) {
  return;
}

$materiel_op = new CMaterielOperatoire();

$where = [
  "status" => "IS NULL OR status " . CSQLDataSource::prepareNotIn(["ok"]),
  "operation_id" => CSQLDataSource::prepareIn($operations_ids)
];

foreach ($materiel_op->loadList($where) as $_materiel_op) {
  $_materiel_op->status = "ok";

  $msg = $_materiel_op->store();

  CAppUI::setMsg($msg ? : "CMaterielOperatoire-msg-modify", $msg ? UI_MSG_ERROR : UI_MSG_OK);
}

echo CAppUI::getMsg();
