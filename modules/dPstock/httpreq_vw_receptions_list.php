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
use Ox\Mediboard\Stock\CProductReception;
use Ox\Mediboard\Stock\CProductStockGroup;

CCanDo::checkEdit();

$start            = intval(CValue::get("start", 0));
$keywords         = CValue::get("keywords");
$without_supplier = CValue::get("without_supplier");
$category_id      = CValue::get('category_id');

// Chargement des receptions de l'etablissement
$reception = new CProductReception();

$ljoin = array();

$where             = array();
$where["group_id"] = "= '" . CProductStockGroup::getHostGroup() . "'";

if (!$without_supplier) {
  $where["product_reception.societe_id"] = "IS NOT NULL";
}

if ($category_id) {
  $where["product.category_id"] = "= '$category_id'";

  $ljoin['product_order_item_reception'] = 'product_reception.reception_id = product_order_item_reception.reception_id';
  $ljoin['product_order_item']           = 'product_order_item_reception.order_item_id = product_order_item.order_item_id';
  $ljoin['product_reference']            = 'product_order_item.reference_id = product_reference.reference_id';
  $ljoin['product']                      = 'product_reference.product_id = product.product_id';
}

$receptions = $reception->seek($keywords, $where, "$start, 25", true, $ljoin, "date DESC");
$total      = $reception->_totalSeek;

foreach ($receptions as $_reception) {
  $_reception->countReceptionItems();
}

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign("reception", $reception);
$smarty->assign("receptions", $receptions);
$smarty->assign("total", $total);
$smarty->assign("start", $start);
$smarty->display('inc_receptions_list.tpl');

