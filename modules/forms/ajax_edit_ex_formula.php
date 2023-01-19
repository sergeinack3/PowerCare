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

CCanDo::checkEdit();

$ex_field_id = CView::get("ex_field_id", "ref class|CExClassField");

CView::checkin();

$ex_field = new CExClassField();
$ex_field->load($ex_field_id);
$ex_field->formulaFromDB();

$formula_possible = true;
$field_names      = array();

$spec_type = $ex_field->getSpecObject()->getSpecType();

if (!CExClassField::formulaCanResult($spec_type)) {
  $formula_possible = false;
}
else {
  $field_names = $ex_field->getFieldNames(true, true);
  $field_names = array_values($field_names);
}

$smarty = new CSmartyDP();
$smarty->assign("ex_field", $ex_field);
$smarty->assign("field_names", $field_names);
$smarty->assign("formula_possible", $formula_possible);
$smarty->display("inc_edit_ex_formula.tpl");