<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CValue;
use Ox\Mediboard\PlanningOp\COperation;

$operation_id = CValue::post("operation_id");
$date         = CValue::post("date");

$operation = new COperation;
$operation->load($operation_id);

$sejour = $operation->loadRefSejour();
$sejour->entree_prevue = "";
$sejour->sortie_prevue = "";

$msg = $sejour->store();

CAppUI::setMsg($msg ? $msg : CAppUI::tr("CSejour-msg-modify"), $msg ? UI_MSG_ERROR : UI_MSG_OK);

$do = new CDoObjectAddEdit("COperation");
$do->doIt();
