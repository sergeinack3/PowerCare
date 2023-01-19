<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\System\Forms\CExClassConstraint;

CCanDo::checkEdit();

$ex_constraint_id  = CValue::get("ex_constraint_id");
$ex_class_event_id = CValue::get("ex_class_event_id");

$ex_constraint = new CExClassConstraint();

if (!$ex_constraint->load($ex_constraint_id)) {
  $ex_constraint->ex_class_event_id = $ex_class_event_id;
}
else {
  $ex_constraint->loadRefsNotes();
}

$ex_constraint->loadTargetObject();
$event = $ex_constraint->loadRefExClassEvent();

$options                = $event->getHostClassOptions();
$host_field_suggestions = CValue::read($options, "hostfield_sugg", array());
$host_quick_accesses    = CValue::read($options, "quick_access", array());

$list = $event->buildHostFieldsList();

$smarty = new CSmartyDP();
$smarty->assign("ex_constraint", $ex_constraint);
$smarty->assign("class_fields", $list);
$smarty->assign("host_field_suggestions", $host_field_suggestions);
$smarty->assign("host_quick_accesses", $host_quick_accesses);
$smarty->display("inc_edit_ex_constraint.tpl");
