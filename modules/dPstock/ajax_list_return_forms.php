<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Stock\CProductReturnForm;

CCanDo::checkRead();

$start  = CValue::get("start", array());
$status = CValue::get("status");

$start = CMbArray::get($start, $status, 0);

// Loads the return forms
$return_form  = new CProductReturnForm();
$where        = array(
  "status" => "= '$status'",
);
$return_forms = $return_form->loadGroupList($where, "datetime DESC", "$start,25");

CStoredObject::massCountBackRefs($return_forms, "product_outputs");

foreach ($return_forms as $_return_form) {
  $_return_form->updateTotal();
}

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign('return_forms', $return_forms);
$smarty->assign('status', $status);
$smarty->display('inc_list_return_forms.tpl');