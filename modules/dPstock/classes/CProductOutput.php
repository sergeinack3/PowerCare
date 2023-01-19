<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Stock;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Product output
 */
class CProductOutput extends CMbObject implements IProductRelated, IProductStockGroupRelated {
  public $product_output_id;

  // DB Fields
  public $stock_class;
  public $stock_id;
  public $quantity;
  public $unit_price;

  public $datetime;
  public $user_id;

  public $reason;
  public $reason_desc;
  public $return_form_id;

  /** @var CProductStockGroup|CProductStockService */
  public $_ref_stock;

  /** @var CProductReturnForm */
  public $_ref_return_form;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'product_output';
    $spec->key   = 'product_output_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props['stock_class']    = 'str class notNull';
    $props['stock_id']       = 'ref notNull class|CProductStock meta|stock_class back|outputs';
    $props['quantity']       = 'num notNull';
    $props['unit_price']     = 'currency';
    $props['datetime']       = 'dateTime notNull';
    $props['user_id']        = 'ref class|CMediusers notNull back|product_outputs';
    $props['reason']         = 'enum list|other|expired|breakage|loss|gift|discrepancy|notused|toomany|return';
    $props['reason_desc']    = 'text';
    $props['return_form_id'] = 'ref class|CProductReturnForm back|product_outputs';

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = sprintf("%s : %d", $this->getFormattedValue("datetime"), $this->quantity);

    if ($this->reason) {
      $this->_view .= " - " . $this->getFormattedValue("reason");
    }

    if ($this->reason_desc) {
      $this->_view .= " [$this->reason_desc]";
    }
  }

  /**
   * @inheritdoc
   */
  function store() {
    $this->completeField("stock_class", "stock_id");

    $stock = $this->loadRefStock();

    $infinite_group_stock = CAppUI::gconf('dPstock CProductStockGroup infinite_quantity') == '1';
    $negative_allowed     = CAppUI::gconf('dPstock CProductStockGroup negative_allowed') == '1';

    if (!$negative_allowed && !$infinite_group_stock
      && (($this->quantity == 0) || ($stock->quantity < $this->quantity))
    ) {
      $unit = $stock->_ref_product->_unit_title ? $stock->_ref_product->_unit_title : $stock->_ref_product->_view;

      return "Impossible de sortir ce nombre de $unit";
    }

    if (!$infinite_group_stock) {
      $stock->quantity -= $this->quantity;
      if ($msg = $stock->store()) {
        return $msg;
      }
    }

    return parent::store();
  }

  /**
   * @inheritdoc
   */
  function updatePlainFields() {
    parent::updatePlainFields();

    if (!$this->_id) {
      $this->user_id  = $this->user_id ?: CMediusers::get()->_id;
      $this->datetime = $this->datetime ?: CMbDT::dateTime();
    }
  }

  /**
   * Load stock
   *
   * @return CProductStockGroup|CProductStockService
   */
  function loadRefStock() {
    return $this->_ref_stock = $this->loadFwdRef("stock_id", true);
  }

  /**
   * @inheritdoc
   */
  function loadRelProduct() {
    return $this->loadRefStock()->loadRefProduct();
  }

  /**
   * @inheritdoc
   */
  function loadRelProductStockGroup() {
    return $this->loadRefStock();
  }
}
