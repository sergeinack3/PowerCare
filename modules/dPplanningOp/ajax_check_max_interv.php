<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CPlageOp;

$type        = CValue::get("type");
$plageop_id  = CValue::get("plageop_id");

$error = false;
if ($plageop_id && $type) {
  $plage_op = new CPlageOp();
  $plage_op->load($plageop_id);
  $plage_op->countOperationsAmbuHospi();

  if ($type == "ambu" && $plage_op->max_ambu && $plage_op->_count_operations_ambu >= $plage_op->max_ambu) {
    $error = true;
  }

  if ($type == "comp" && $plage_op->max_hospi && $plage_op->_count_operations_hospi >= $plage_op->max_hospi) {
    $error = true;
  }
}

CApp::json($error);