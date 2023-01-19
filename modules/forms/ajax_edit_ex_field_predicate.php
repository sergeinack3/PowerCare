<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\Forms\CExClassField;
use Ox\Mediboard\System\Forms\CExClassFieldGroup;
use Ox\Mediboard\System\Forms\CExClassFieldPredicate;

CCanDo::checkEdit();

$ex_field_id           = CView::get("ex_field_id", "num");
$ex_field_predicate_id = CView::get("ex_field_predicate_id", "ref class|CExClassFieldPredicate");
$exclude_ex_field_id   = CView::get("exclude_ex_field_id", "num");
$ex_group_id           = CView::get("ex_group_id", "ref class|CExClassFieldGroup");
$opener_field_value    = CView::get("opener_field_value", "str");
$opener_field_view     = CView::get("opener_field_view", "str");

CView::checkin();

$ex_field_predicate = new CExClassFieldPredicate();
$ex_field_predicate->load($ex_field_predicate_id);

if (!$ex_field_predicate->_id && $ex_field_id != $exclude_ex_field_id) {
  $ex_field_predicate->ex_class_field_id = $ex_field_id;
}

$ex_field_predicate->loadRefExClassField();

$ex_field = new CExClassField();
$ex_field->load($ex_field_id);

if ($ex_group_id && !$ex_field->_id) {
  $ex_group = new CExClassFieldGroup();
  $ex_group->load($ex_group_id);
  $ex_class = $ex_group->loadRefExClass();
}
else {
  $ex_class = $ex_field->loadRefExClass();
}

$smarty = new CSmartyDP();
$smarty->assign("ex_field_predicate", $ex_field_predicate);
$smarty->assign("ex_class", $ex_class);
$smarty->assign("exclude_ex_field_id", $exclude_ex_field_id);
$smarty->assign("opener_field_value", $opener_field_value);
$smarty->assign("opener_field_view", $opener_field_view);
$smarty->display("inc_edit_ex_field_predicate.tpl");
