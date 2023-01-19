<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Stock\CProductReturnForm;

CCanDo::checkEdit();

$order = new CProductReturnForm();

// Loads the return forms
$return_form       = new CProductReturnForm();
$where             = array(
  "status" => "= 'new'",
);
$list_return_forms = $return_form->loadGroupList($where, "datetime DESC");

CStoredObject::massLoadBackRefs($list_return_forms, "product_outputs");

foreach ($list_return_forms as $_return_form) {
  $_return_form->updateTotal();
  $_return_form->loadRefsOutputs();
  $_return_form->loadRefSupplier();
}

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign('list_return_forms', $list_return_forms);
$smarty->display('inc_return_forms_tabs.tpl');
