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
use Ox\Core\CValue;
use Ox\Mediboard\Stock\CProductReception;

CCanDo::checkRead();

$reception_id = CValue::get('reception_id');

$reception = new CProductReception();
$reception->load($reception_id);
$reception->loadRefsBack();
$reception->loadRefsFwd();
$reception->updateTotal();

$classe_comptables = array();
foreach ($reception->_ref_reception_items as $_reception_item) {
  $_reception_item->loadRefs();
  $classe = $_reception_item->_ref_order_item->_ref_reference->_ref_product->classe_comptable?: 0;
  if (!isset($classe_comptables[$classe])) {
    $classe_comptables[$classe] = array(
      "ht" => 0,
      "tva" => 0,
      "ttc" => 0,
    );
  }
  $classe_comptables[$classe]["ht"] += $_reception_item->_price;
  $classe_comptables[$classe]["tva"] += $_reception_item->_price_tva;
  $classe_comptables[$classe]["ttc"] += $_reception_item->_price_ttc;
}
$items_by_class = CMbArray::pluck($reception->_ref_reception_items, "_ref_order_item", "_ref_reference", "_ref_product", "classe_comptable");
array_multisort($items_by_class, SORT_ASC, $reception->_ref_reception_items);

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign("reception", $reception);
$smarty->assign("classe_comptables", $classe_comptables);
$smarty->display('print_reception');

