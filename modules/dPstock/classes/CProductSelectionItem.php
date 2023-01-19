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
 * Product Selection Item
 */
class CProductSelectionItem extends CMbObject {
  // DB Table key
  public $selection_item_id;

  // DB Fields
  public $product_id;
  public $selection_id;

  /** @var CProduct */
  public $_ref_product;

  /** @var CProductSelection */
  public $_ref_selection;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec                       = parent::getSpec();
    $spec->table                = 'product_selection_item';
    $spec->key                  = 'selection_item_id';
    $spec->uniques["selection"] = array("product_id", "selection_id");

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $specs                 = parent::getProps();
    $specs["product_id"]   = "ref notNull class|CProduct autocomplete|name dependsOn|cancelled back|selections";
    $specs["selection_id"] = "ref notNull class|CProductSelection back|selection_items";

    return $specs;
  }

  /**
   * @inheritdoc
   */
  function loadRefsFwd() {
    $this->_ref_product   = $this->loadFwdRef("product_id", true);
    $this->_ref_selection = $this->loadFwdRef("selection_id", true);
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->loadRefsFwd();
    $this->_view = $this->_ref_product->_view;
  }
}
