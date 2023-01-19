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
 * Product CUMP
 */
class CProductCUMP extends CMbObject {
  public $product_cump_id;

  // DB Fields
  public $group_id;
  public $datetime;
  public $stock_group_id;
  public $unit_price;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'product_cump';
    $spec->key   = 'product_cump_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                   = parent::getProps();
    $props['group_id']       = 'ref notNull class|CGroups back|product_cumps';
    $props['datetime']       = 'dateTime notNull';
    $props['stock_group_id'] = 'ref notNull class|CProductStockGroup back|cumps';
    $props['unit_price']     = 'currency notNull';

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->datetime;
  }
}
