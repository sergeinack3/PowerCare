<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Stock;

use Exception;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Product Discrepancy (Ecart d'inventaire)
 */
class CProductDiscrepancy extends CMbObject {
  public $discrepancy_id;

  public $quantity;
  public $date;
  public $description;

  public $object_class;
  public $object_id;
  public $_ref_object;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'product_discrepancy';
    $spec->key   = 'discrepancy_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $specs                = parent::getProps();
    $specs['quantity']    = 'num notNull';
    $specs['date']        = 'dateTime notNull';
    $specs['description'] = 'text';
    $specs['object_id']   = 'ref notNull class|CProductStock meta|object_class back|discrepancies';
    $specs["object_class"] = "str notNull class show|0";

    return $specs;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->loadRefsFwd();
    $this->_view = $this->_ref_object->_view . " ({$this->quantity})";
  }

  /**
   * @param CStoredObject $object
   * @deprecated
   * @todo redefine meta raf
   * @return void
   */
  public function setObject(CStoredObject $object) {
    CMbMetaObjectPolyfill::setObject($this, $object);
  }

  /**
   * @param bool $cache
   * @deprecated
   * @todo redefine meta raf
   * @return bool|CStoredObject|CExObject|null
   * @throws Exception
   */
  public function loadTargetObject($cache = true) {
    return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
  }

  /**
   * @inheritDoc
   * @todo remove
   */
  function loadRefsFwd() {
    parent::loadRefsFwd();
    $this->loadTargetObject();
  }
}
