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
use Ox\Mediboard\System\Forms\CExClassFieldNotification;

CCanDo::checkEdit();

$ex_class_field_notification_id = CValue::get("ex_class_field_notification_id");
$ex_class_id                    = CValue::get("ex_class_id");
$ex_field_predicate_id          = CValue::get("ex_field_predicate_id");


$ex_field_notification = new CExClassFieldNotification();
$ex_field_notification->load($ex_class_field_notification_id);

if (!$ex_field_notification->_id) {
  $ex_field_notification->predicate_id = $ex_field_predicate_id;
}

$predicate = $ex_field_notification->loadRefPredicate();

if ($predicate->_id) {
  $predicate->loadView();
}

$ex_field_notification->loadRefTargetUser();

if (!$ex_class_id) {
  $ex_class_id = $predicate->loadRefExClassField()->loadRefExClass()->_id;
}

$smarty = new CSmartyDP();
$smarty->assign("ex_field_notification", $ex_field_notification);
$smarty->assign("ex_class_id", $ex_class_id);
$smarty->display("inc_edit_ex_field_notification.tpl");
