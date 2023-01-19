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
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExClassField;
use Ox\Mediboard\System\Forms\CExObject;

CCanDo::checkEdit();

$ex_field_id = CValue::get("ex_field_id");
$ex_class_id = CValue::get("ex_class_id");
$ex_group_id = CValue::get("ex_group_id");
$error       = CValue::get("error");

CExObject::$_locales_cache_enabled = false;
$ex_field                          = new CExClassField();

$spec_type = "enum";

if ($ex_field->load($ex_field_id)) {
  $spec_type = $ex_field->getSpecObject()->getSpecType();
  $ex_field->loadRefsNotes();
  $ex_field->loadRefsHyperTextLink();
  $ex_field->updateTranslation();
  $ex_field->loadTriggeredData();

  $ex_field->loadFieldTagItems();
  $ex_field->hasAvailableTags();
}
else {
  $ex_field->ex_group_id = $ex_group_id;
  $ex_field->disabled    = 0;
  $ex_field->readonly    = 0;
  $ex_field->hidden      = 0;
}

$ex_field->loadRefExClass();
$properties = $ex_field->loadRefProperties();
foreach ($properties as $_property) {
  $_property->loadRefPredicate()->loadView();
}

$ex_field->_disable_load_native_data = 0;
if ($ex_field->concept_id) {
  $concept = $ex_field->loadRefConcept();
  if ($concept->native_field && strpos($concept->native_field, "CTransmissionMedicale") === 0) {
    $ex_field->_disable_load_native_data = 1;
    $ex_field->load_native_data          = 0;
  }
}

$ex_field->loadRefPredicate()->loadView();
$predicates = $ex_field->loadRefPredicates();

foreach ($predicates as $_predicate) {
  $_predicate->loadView();
}

if ($ex_class_id) {
  $ex_class = new CExClass();
  $ex_class->load($ex_class_id);
}
else {
  $ex_class = $ex_field->_ref_ex_class;
}

$ex_class->loadRefsGroups();

$other_fields = array();

/*$ex_field->_ref_ex_class->loadRefsFields();
foreach($ex_field->_ref_ex_class->_ref_fields as $_field){
  if ($_field->_id != $ex_field->_id)
    $other_fields[] = $_field->name;
}*/

$smarty = new CSmartyDP();
$smarty->assign("ex_field", $ex_field);
$smarty->assign("ex_class", $ex_class);
$smarty->assign("spec_type", $spec_type);
$smarty->assign("other_fields", $other_fields);
$smarty->assign("error", $error);
$smarty->display("inc_edit_ex_field.tpl");