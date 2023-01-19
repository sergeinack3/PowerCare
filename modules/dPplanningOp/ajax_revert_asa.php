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
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkEdit();

$ds   = CSQLDataSource::get("std");
$view = CValue::get("view", 1);
$date = CMbDT::date();

$request = "SELECT `operations`.*
            FROM `operations`
            LEFT JOIN `plagesop` ON `operations`.`plageop_id` = `plagesop`.`plageop_id`
            WHERE `operations`.`ASA` = '1'
            AND (`plagesop`.`date` >= '".$date."'
            OR `operations`.`date` >= '".$date."')
            AND NOT EXISTS (
              SELECT * FROM `consultation_anesth`
              WHERE `consultation_anesth`.`operation_id` = `operations`.`operation_id`
            );";
$resultats = $ds->loadList($request);

if ($view == false) {
  $request = "UPDATE `operations`
            LEFT JOIN `plagesop` ON `operations`.`plageop_id` = `plagesop`.`plageop_id`
            SET `operations`.`ASA` = NULL
            WHERE `operations`.`ASA` = '1'
            AND (`plagesop`.`date` >= '".$date."'
            OR `operations`.`date` >= '".$date."')
            AND NOT EXISTS (
              SELECT * FROM `consultation_anesth`
              WHERE `consultation_anesth`.`operation_id` = `operations`.`operation_id`
            );";
  $ds->query($request);
  $result = $ds->affectedRows();

  CAppUI::stepAjax(count($resultats)." intervention(s) modifiée(s)", UI_MSG_OK);
}
else {
  $where = array();
  $where["operation_id"] = CSQLDataSource::prepareIn(CMbArray::pluck($resultats, "operation_id"));
  /* @var COperation[] $operations*/
  $operation = new COperation();
  $nb_operations = $operation->countList($where);
  $operations = $operation->loadList($where, null, "100");

  $sejours = CMbObject::massLoadFwdRef($operations, "sejour_id");
  CMbObject::massLoadFwdRef($sejours, "patient_id");
  CMbObject::massLoadFwdRef($operations, "plageop_id");
  CMbObject::massLoadFwdRef($operations, "chir_id");

  foreach ($operations as $op) {
    $op->loadRefPraticien();
    $op->loadRelPatient();
    $op->loadRefPlageOp();
  }

  // Creation du template
  $smarty = new CSmartyDP();
  $smarty->assign("operations"    , $operations);
  $smarty->assign("nb_operations" , $nb_operations);
  $smarty->display("check_score_asa");
}