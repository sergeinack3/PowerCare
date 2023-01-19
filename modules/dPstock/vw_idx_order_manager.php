<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Stock\CProductCategory;
use Ox\Mediboard\Stock\CProductOrder;

CCanDo::checkEdit();
$order_id    = CView::get("order_id", "ref class|CProductOrder", true);
$category_id = CView::get("category_id", "ref class|CProductCategory", true);
CView::checkin();

// Loads the expected Order
$order = new CProductOrder();
if ($order_id) {
  $order->load($order_id);
  $order->updateFormFields();
}

$category        = new CProductCategory();
$list_categories = $category->loadList(null, 'name');

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign('order'          , $order);
$smarty->assign('category_id'    , $category_id);
$smarty->assign('list_categories', $list_categories);
$smarty->display('vw_idx_order_manager');

