<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Stock;

use Ox\Core\CMbObject;

/**
 * Product Reception Bill Item
 */
class CProductReceptionBillItem extends CMbObject {
  // DB Table key
  public $reception_bill_item_id;

  // DB Fields
  public $bill_id;
  public $reception_item_id;
  public $quantity;
  public $unit_price; // In the case the reference price changes

  /** @var CProductOrderItemReception */
  public $_ref_reception_item;

  /** @var CProductReceptionBill */
  public $_ref_bill;

  // Form fields
  public $_price;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'product_reception_bill_item';
    $spec->key   = 'reception_bill_item_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $specs                      = parent::getProps();
    $specs['bill_id']           = 'ref class|CProductReceptionBill back|bill_items';
    $specs['reception_item_id'] = 'ref class|CProductOrderItemReception back|bill_items';
    $specs['quantity']          = 'num min|0';
    $specs['unit_price']        = 'currency precise';
    $specs['_price']            = 'currency';

    return $specs;
  }
}
