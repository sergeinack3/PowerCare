<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Medicament\CMedicamentProduit;
use Ox\Mediboard\Stock\CProduct;

CCanDo::checkRead();

$category_id = CView::post("category_id", "ref class|CProductCategory");
$keywords    = CView::post("speciality", "str");
CView::checkin();

$code = false;

if (ctype_digit($keywords)) {
  $keywords = (strlen($keywords) === 13) ? substr($keywords, 5, -1) : $keywords;
  $code     = true;
}

$ds       = CSQLDataSource::get('std');
$group_id = CGroups::get()->_id;

// Prepare the request
$where   = [
  "bdm" => $ds->prepare("= ?", CMedicamentProduit::getBase()),
];
$where[] = "cancelled = '0' OR cancelled IS NULL";

if ($category_id) {
  $where['category_id'] = $ds->prepare("= ?", $category_id);
}

if ($keywords) {
  $where[] = ($code) ? "code like '%$keywords%'" : "name like '%$keywords%' OR description like '%$keywords%'";
}

$order = [
  "name ASC",
  "code ASC"
];

// Request
$product       = new CProduct();
$list_products = $product->loadList($where, $order, "0,10");

$quantities = [];

CStoredObject::massLoadBackRefs($list_products, "stocks_group");
foreach ($list_products as $_product) {
  $stocks = $_product->loadRefsStocksGroup();

  foreach ($stocks as $_stock) {
    if ($_stock->group_id == $group_id) {
      $quantities[$_product->_id] = $_stock->quantity;
    }
  }
}


$smarty = new CSmartyDP();

$smarty->assign('list_products', $list_products);
$smarty->assign('keywords', $keywords);
$smarty->assign('quantities', $quantities);

$smarty->display('inc_list_product_autocomplete');
