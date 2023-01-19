<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\CSecondaryFunction;

CCanDo::checkRead();

$chir_id       = CValue::get("chir_id");
$field_name    = CValue::get("field_name", "_function_secondary_id");
$empty_function_principale = CValue::get("empty_function_principale", 0);
$type_onchange = CValue::get("type_onchange", "consult");
$change_active = CValue::get("change_active", "1");

$chir = new CMediusers();
$chir->load($chir_id);
$chir->loadRefFunction()->loadRefGroup();

/** @var CSecondaryFunction[] $_functions */
$_functions = $chir->loadBackRefs("secondary_functions");

CMbObject::massLoadFwdRef(CMbArray::pluck($_functions, "_ref_function"), "group_id");

foreach ($_functions as $_function) {
  $_function->_ref_function->loadRefGroup();
}

$smarty = new CSmartyDP();

$smarty->assign("_functions", $_functions);
$smarty->assign("chir"      , $chir);
$smarty->assign("field_name", $field_name);
$smarty->assign("empty_function_principale", $empty_function_principale);
$smarty->assign("type_onchange", $type_onchange);
$smarty->assign("change_active", $change_active);

$smarty->display("inc_refresh_secondary_functions.tpl");
