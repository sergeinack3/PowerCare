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
 * Product Selection
 */
class CProductSelection extends CMbObject {
  public $selection_id;

  // DB Fields
  public $name;

  /** @var CProductSelectionItem[] */
  public $_ref_items;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec                  = parent::getSpec();
    $spec->table           = "product_selection";
    $spec->key             = "selection_id";
    $spec->uniques["name"] = array("name");

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $specs         = parent::getProps();
    $specs["name"] = "str notNull seekable";

    return $specs;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->name;
  }

  /**
   * @inheritdoc
   */
  function loadRefsBack() {
    $this->loadRefsItems();
  }

  /**
   * @return CProductSelectionItem[]
   */
  function loadRefsItems() {
    $ljoin = array(
      "product" => "product.product_id = product_selection_item.product_id"
    );

    return $this->_ref_items = $this->loadBackRefs("selection_items", "product.name", null, null, $ljoin);
  }
}
