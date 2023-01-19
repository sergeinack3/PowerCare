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
 * Product Equivalence
 */
class CProductEquivalence extends CMbObject {
  public $equivalence_id;

  // DB Fields
  public $name;

  /** @var CProduct[] */
  public $_ref_products;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec                  = parent::getSpec();
    $spec->table           = 'product_equivalence';
    $spec->key             = 'equivalence_id';
    $spec->uniques["name"] = array("name");

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $specs         = parent::getProps();
    $specs['name'] = 'str notNull seekable';

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
   * Load products
   *
   * @return CProduct[]
   */
  function loadRefsProducts() {
    return $this->_ref_products = $this->loadBackRefs('products', 'name');
  }

  /**
   * @inheritdoc
   */
  function loadRefsBack() {
    $this->loadRefsProducts();
  }
}
