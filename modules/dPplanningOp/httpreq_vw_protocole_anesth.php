<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Prescription\CPrescription;

CCanDo::checkEdit();

$prescription_id = CValue::getOrSession("prescription_id");

$prescription = new CPrescription();
$prescription->load($prescription_id);

$smarty = new CSmartyDP();
$smarty->assign("prescription", $prescription);
$smarty->assign("nodebug", true);
$smarty->display("inc_vw_protocole_anesth");
