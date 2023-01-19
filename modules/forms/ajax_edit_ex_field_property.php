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
use Ox\Mediboard\System\Forms\CExClassFieldProperty;
use Ox\Mediboard\System\Forms\CExObject;

CCanDo::checkEdit();

$object_class         = CValue::get("object_class");
$object_id            = CValue::get("object_id");
$ex_field_property_id = CValue::get("ex_field_property_id");
$opener_field_value   = CValue::get("opener_field_value");
$opener_field_view    = CValue::get("opener_field_view");

CExObject::$_locales_cache_enabled = false;

$ex_field_property = new CExClassFieldProperty();

if (!$ex_field_property->load($ex_field_property_id)) {
  $ex_field_property->object_id    = $object_id;
  $ex_field_property->object_class = $object_class;
}

$ex_field_property->loadTargetObject();
$predicate = $ex_field_property->loadRefPredicate();

if ($predicate) {
  $predicate->loadView();
}

$ex_class = $ex_field_property->_ref_object->loadRefExClass();

$smarty = new CSmartyDP();
$smarty->assign("ex_field_property", $ex_field_property);
$smarty->assign("ex_class", $ex_class);
$smarty->assign("opener_field_value", $opener_field_value);
$smarty->assign("opener_field_view", $opener_field_view);
$smarty->display("inc_edit_ex_field_property.tpl");
