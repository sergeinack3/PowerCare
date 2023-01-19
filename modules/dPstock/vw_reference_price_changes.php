<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Cahpp\CCAHPPArticle;
use Ox\Mediboard\Stock\CProductOrderItem;
use Ox\Mediboard\Stock\CProductReference;

CCanDo::checkEdit();

$ratio = (float)(CValue::get("ratio", 2));

CApp::setMemoryLimit('512M');
CApp::setTimeLimit(120);

$sql = new CRequest();
$sql->addTable("product_order_item");
$sql->addSelect("
  product_order_item.order_item_id,
  product_order_item.reference_id, 
  product_reference.price AS RP, 
  product_order_item.unit_price AS OP, 
  product_order_item.quantity AS OQ, 
  product_order.order_id, 
  product_order.order_number, 
  product_order.date_ordered");
$sql->addLJoin(array(
  "product_reference" => "product_reference.reference_id = product_order_item.reference_id",
  "product_order"     => "product_order.order_id = product_order_item.order_id",
));
$sql->addWhere("
  product_order.cancelled = '0' 
  AND (product_reference.cancelled = '0' OR product_reference.cancelled IS NULL)
  AND product_reference.price != product_order_item.unit_price
  AND (
    product_order_item.unit_price > product_reference.price*$ratio OR 
    product_reference.price > product_order_item.unit_price*$ratio
  )");
$sql->addOrder("product_reference.code");
$changes = $this->_spec->ds->loadList($sql->makeSelect());

$changes_struct   = array();
$references       = array();
$references_cahpp = array();

foreach ($changes as $_change) {
  if (!isset($references[$_change["reference_id"]])) {
    $_reference = new CProductReference;
    $_reference->load($_change["reference_id"]);
    $references[$_reference->_id] = $_reference;

    $article = new CCAHPPArticle();

    $where = array("reference_fournisseur" => $article->_spec->ds->prepare("=%", $_reference->supplier_code));

    if (!$article->loadObject($where)) {
      $where = array("cip" => $article->_spec->ds->prepare("=%", $_reference->loadRefProduct()->code));
      $article->loadObject($where);
    }

    $references_cahpp[$_reference->_id] = $article;
  }

  $_order_item = new CProductOrderItem;
  $_order_item->load($_change["order_item_id"]);
  $_order_item->loadOrder();
  $_change["order_item"] = $_order_item;

  $changes_struct[$_change["reference_id"]][] = $_change;
}

$order_item        = new CProductOrderItem;
$total_order_items = $order_item->countList();

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign('changes', $changes);
$smarty->assign('changes_struct', $changes_struct);
$smarty->assign('references', $references);
$smarty->assign('references_cahpp', $references_cahpp);
$smarty->assign('total_order_items', $total_order_items);
$smarty->assign('ratio', $ratio);

$smarty->display('vw_reference_price_changes.tpl');

