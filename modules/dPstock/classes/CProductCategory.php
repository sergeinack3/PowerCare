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
 * Product Category
 */
class CProductCategory extends CMbObject {
  public $category_id;

  // DB fields
  public $name;

  public $_count_products;

  /** @var CProduct[] */
  public $_ref_products;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'product_category';
    $spec->key   = 'category_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $specs                    = parent::getProps();
    $specs['name']            = 'str notNull maxLength|50 seekable show|0';
    $specs['_count_products'] = 'num show|1';

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
  function loadView() {
    parent::loadView();

    $this->countProducts();
  }

  /**
   * @inheritdoc
   */
  function loadRefsBack() {
    $this->_ref_products = $this->loadBackRefs('products');
  }

  /**
   * Count products
   *
   * @return int
   */
  function countProducts() {
    return $this->_count_products = $this->countBackRefs("products");
  }

  /**
   * Get product categories
   *
   * @return static[]
   */
  static function getList() {
    $category = new self();

    return $category->loadList(null, 'name');
  }
}
