<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkEdit();

$operation_id = CView::post("operation_id", "num");
$date         = CView::post("date", "date");
$chir_id      = CView::post("chir_id", "num");
$plageop_id   = CView::post("plageop_id", "num");

CView::checkin();

$op = new COperation();
$op->load($operation_id);

$salle = $op->loadRefPlageOp()->loadRefSalle();

$plage = new CPlageOp();
$plage->load($plageop_id);

if (!$plage->_id) {
  $where = array(
    "plagesop.date"       => "= '$date'",
    "plagesop.chir_id"    => "= '$chir_id'",
    "plagesop.salle_id" => "!= '$salle->_id'"
  );

  $plages = $plage->loadList($where);

  if (count($plages) > 1) {
    CAppUI::callbackAjax("MultiSalle.choosePlage", $operation_id, $date, $chir_id, null);
    CApp::rip();
  }

  $plage = reset($plages);
}

if (CAppUI::gconf("dPplanningOp COperation multi_salle_op")) {
  $old_plage = $op->loadRefPlageOp();

  $op->_ref_plageop = $plage;
  $op->plageop_id = $plage->_id;
  $op->salle_id = $plage->salle_id;

  $operations = $plage->loadRefsOperations(true, "rank, time_operation, rank_voulu, horaire_voulu", true, true);

  $operations[$op->_id] = $op;

  $operations_order = CMbArray::pluck($operations, "time_operation");
  array_multisort($operations_order, SORT_ASC, $operations);

  $i = 0;
  foreach ($operations as $_operation) {
    $_operation->rank = ++$i;

    if ($msg = $_operation->store(false)) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
    }
    else {
      CAppUI::setMsg(CAppUI::tr("COperation-msg-modify"));
    }
  }

  $i = 0;
  foreach ($old_plage->loadRefsOperations(true, "rank, time_operation, rank_voulu, horaire_voulu", true, true) as $_operation) {
    $_operation->rank = ++$i;

    if ($msg = $_operation->store(false)) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
    }
    else {
      CAppUI::setMsg(CAppUI::tr("COperation-msg-modify"));
    }
  }
}
else {
  $op->_move = "out";

  if ($msg = $op->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
    echo CAppUI::getMsg();
    CApp::rip();
  }

  $op->plageop_id = $plage->_id;
  $op->salle_id = $plage->salle_id;
  $op->rawStore();

  $op = new COperation();
  $op->load($operation_id);

  $op->_move = "last";

  if ($msg = $op->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
  }
  else {
    CAppUI::setMsg(CAppUI::tr("COperation-msg-modify"));
  }
}


echo CAppUI::getMsg();

CAppUI::callbackAjax("reloadPlanning");
