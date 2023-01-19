<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$sejour_id = CValue::get("sejour_id");

$sejour = new CSejour;
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$operations = $sejour->loadRefsOperations();
CMbObject::massLoadFwdRef($operations, "plageop_id");

foreach ($operations as $_operation) {
  $_operation->loadRefPlageOp();
}

$smarty = new CSmartyDP;

$smarty->assign("operations", $operations);
$smarty->assign("sejour_id" , $sejour_id);
$smarty->display("inc_bind_operations.tpl");
