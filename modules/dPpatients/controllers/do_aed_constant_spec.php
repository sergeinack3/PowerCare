<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CView;
use Ox\Mediboard\Patients\Constants\CConstantException;
use Ox\Mediboard\Patients\Constants\CConstantSpec;

$code        = CView::get("code", "str");
$name        = CView::get("name", "str");
$unit        = CView::get("primary_unit", "str");
$value_class = CView::get("value_class", "str");
$category    = CView::get("category", "str");
$min         = CView::get("value_min", "str");
$max         = CView::get("value_max", "str");
$list        = CView::get("constantSpec_list", "str");
$period      = CView::get("period", "num");
$min_value   = CView::get("min_value", "str");
$max_value        = CView::get("max_value", "str");
$alert_id         = CView::get("alert_id", "ref class|CConstantAlert");
$constant_spec_id = CView::get("constant_spec_id", "ref class|CConstantSpec");

$units = array();
foreach ($_GET as $_key => $value) {
  if (strstr($_key, "coeff_") && $value !== "") {
    $explode = explode("_", $_key);
    $units[CMbArray::get($explode, "1")]["coeff"] = $value;
  }
  elseif (strstr($_key, "unit_") && $value !== "") {
    $explode = explode("_", $_key);
    $units[CMbArray::get($explode, "1")]["label"] = $value;
  }
}
CView::checkin();

$constant_spec = new CConstantSpec();
$constant_spec->load($constant_spec_id);
$constant_spec->code         = $code;
$constant_spec->name         = $name;
$constant_spec->value_class  = $value_class;
$constant_spec->category     = $category;
$constant_spec->list         = $list;
$constant_spec->min_value    = $min;
$constant_spec->max_value    = $max;
$constant_spec->period       = $period;
$constant_spec->min_value    = $min_value;
$constant_spec->max_value    = $max_value;
$constant_spec->active       = 1;
$constant_spec->serializeUnit($unit, $units);
try{
  if ($msg = $constant_spec->store()) {
    throw new CConstantException(CConstantException::INVALID_STORE_SPEC, $msg);
  }
} catch (CConstantException $constantException) {
  CAppUI::stepAjax($constantException->getMessage(), UI_MSG_ERROR);
}
