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
use Ox\Core\FieldSpecs\CEnumSpec;
use Ox\Mediboard\System\Forms\CExClassField;
use Ox\Mediboard\System\Forms\CExConcept;
use Ox\Mediboard\System\Forms\CExObject;

CCanDo::checkEdit();

$ex_class_field_id = CValue::get("ex_class_field_id");
$form_name         = CValue::get("form_name");
$value             = CValue::get("value");

$ex_class_field = new CExClassField();
$ex_class_field->load($ex_class_field_id);

$ex_class_id = $ex_class_field->loadRefExGroup()->ex_class_id;
$ex_object   = new CExObject($ex_class_id);

$ex_object->{$ex_class_field->name} = $value;

$spec = CExConcept::getConceptSpec($ex_class_field->prop);
if ($spec instanceof CEnumSpec) {
  $ex_class_field->updateEnumSpec($spec);
}

$ex_class_field->readonly = "0";
$ex_class_field->hidden   = "0";

$smarty = new CSmartyDP();
$smarty->assign("ex_field", $ex_class_field);
$smarty->assign("ex_object", $ex_object);
$smarty->assign("form", $form_name);
$smarty->assign("is_predicate", true);
$smarty->display("inc_ex_object_field.tpl");
