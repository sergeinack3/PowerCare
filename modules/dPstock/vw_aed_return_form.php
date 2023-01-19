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
use Ox\Core\CValue;
use Ox\Mediboard\Stock\CProductCategory;
use Ox\Mediboard\Stock\CProductReturnForm;
use Ox\Mediboard\Stock\CSociete;

CCanDo::checkRead();

$category_id = CValue::getOrSession('category_id');
$societe_id  = CValue::getOrSession('societe_id');
$letter      = CValue::getOrSession('letter');

// Categories list
$category        = new CProductCategory();
$list_categories = $category->loadList(null, 'name');

// Suppliers list
$list_societes = CSociete::getSuppliers(false);

$return_form       = new CProductReturnForm();
$where             = array(
  "status" => "= 'new'",
);
$list_return_forms = $return_form->loadGroupList($where);

CStoredObject::massLoadFwdRef($list_return_forms, "supplier_id");
CStoredObject::massLoadBackRefs($list_return_forms, "product_outputs");

foreach ($list_return_forms as $_return_form) {
  $_return_form->loadRefSupplier();
  $_return_form->updateTotal();

  $_outputs = $_return_form->loadRefsOutputs();
  foreach ($_outputs as $_output) {
    $_output->loadRefStock();
  }
}

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign('list_categories', $list_categories);
$smarty->assign('category_id', $category_id);

$smarty->assign('list_societes', $list_societes);
$smarty->assign('societe_id', $societe_id);
$smarty->assign('letter', $letter);

$smarty->assign('list_return_forms', $list_return_forms);

$smarty->display('vw_aed_return_form.tpl');
