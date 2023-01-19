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
use Ox\Mediboard\System\Forms\CExClassMandatoryConstraint;

CCanDo::checkEdit();

$ex_constraint_id  = CValue::get("ex_mandatory_constraint_id");
$ex_class_event_id = CValue::get("ex_class_event_id");

$ex_constraint = new CExClassMandatoryConstraint();

if (!$ex_constraint->load($ex_constraint_id)) {
  $ex_constraint->ex_class_event_id = $ex_class_event_id;
}
else {
  $ex_constraint->loadRefsNotes();
}

$event = $ex_constraint->loadRefExClassEvent();

$options          = $event->getHostClassOptions();
$mandatory_fields = CValue::read($options, "mandatory_fields", array());

$object = new $event->host_class();

$smarty = new CSmartyDP();
$smarty->assign("object", $object);
$smarty->assign("ex_constraint", $ex_constraint);
$smarty->assign("mandatory_fields", $mandatory_fields);
$smarty->display("inc_edit_ex_mandatory_constraint.tpl");
