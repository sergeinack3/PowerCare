<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Stock\CProductEndowment;

CCanDo::checkEdit();

$endowment_id = CView::get("endowment_id", "ref class|CProductEndowment", true);
$page         = CView::get("page", "num default|0");
$step         = CView::get("step", "num default|10");

CView::checkin();

$endowment = new CProductEndowment();

if ($endowment->load($endowment_id)) {
  $totalProducts = count($endowment->loadRefsEndowmentItems());
  $items         = $endowment->loadRefsEndowmentItems("$page, $step");
  $endowment->loadRefService();

  foreach ($items as $_item) {
    $_item->updateFormFields();
    $_item->_ref_product->loadRefStock();
  }
}

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign('page', $page);
$smarty->assign('step', $step);
$smarty->assign('total_products', $totalProducts);
$smarty->assign('endowment', $endowment);
$smarty->assign("group_id", $endowment->_id ? $endowment->_ref_service->group_id : CGroups::loadCurrent()->_id);
$smarty->display('inc_list_endowment_product.tpl');
