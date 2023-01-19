<?php
/**
 * @package Mediboard\Reservation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Reservation\CExamenOperation;

CCanDo::checkEdit();

$examen_operation_id = CValue::get("examen_operation_id");

$examen_op = new CExamenOperation();
$examen_op->load($examen_operation_id);

$smarty = new CSmartyDP();

$smarty->assign("examen_op", $examen_op);

$smarty->display("inc_edit_examen");
