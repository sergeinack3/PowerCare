<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkEdit();

$salle_1  = CValue::get("salle_1");
$salle_2  = CValue::get("salle_2");
$date     = CValue::get("date", CMbDT::date());
$callback = CValue::get("callback");

//same salle
if ($salle_2 == $salle_1) {
  return;
}

//gathering
$operation_1 = new COperation();
$operation_1->salle_id = $salle_1;
$operation_1->date = $date;
/** @var COperation[] $operations_1 */
$operations_1 = $operation_1->loadMatchingList();

$operation_2 = new COperation();
$operation_2->salle_id = $salle_2;
$operation_2->date = $date;
/** @var COperation[] $operations_2 */
$operations_2 = $operation_2->loadMatchingList();



// switching
foreach ($operations_1 as $_op_1) {
  $_op_1->salle_id = $salle_2;
  if ($msg = $_op_1->store()) {
    CAppUI::stepAjax($msg, UI_MSG_ERROR);
  }
}

foreach ($operations_2 as $_op_2) {
  $_op_2->salle_id = $salle_1;
  if ($msg = $_op_2->store()) {
    CAppUI::stepAjax($msg, UI_MSG_ERROR);
  }
}

//succeed
$salle_n1 = new CSalle();
$salle_n1->load($salle_1);
$salle_n2 = new CSalle();
$salle_n2->load($salle_2);
CAppUI::stepAjax("dPplanningOp-msg-succeed_switchin_salle1%s_salle2%s", UI_MSG_OK, $salle_n1->nom, $salle_n2->nom);