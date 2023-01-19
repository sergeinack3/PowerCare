<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Stock;

use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Medicament\CMedicamentProduit;
use Ox\Mediboard\Medicament\CProduitLivretTherapeutique;
use Ox\Mediboard\Pharmacie\CStockSejour;

/**
 * Service Product Stock
 */
class CProductStockService extends CProductStock
{
  // DB Fields
  public $object_class;
  public $object_id;
  public $common;

  /** @var CService|CBlocOperatoire */
  public $_ref_object;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec                     = parent::getSpec();
    $spec->table              = 'product_stock_service';
    $spec->key                = 'stock_id';
    $spec->uniques["product"] = array("object_id", "object_class", "product_id", "location_id");

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                 = parent::getProps();
    $props["product_id"]  .= " back|stocks_service";
    $props['object_class'] = 'enum notNull list|CService'; //|CBlocOperatoire';
    $props['object_id']    = 'ref notNull class|CMbObject meta|object_class back|product_stock_services';
    $props["location_id"] .= " back|service_stocks";
    $props['common']       = 'bool';

    return $props;
  }

  /**
   * Set object_class and object_id
   *
   * @param CMbObject $object Object
   *
   * @return void
   */
  function setObject(CMbObject $object) {
    $this->_ref_object  = $object;
    $this->object_id    = $object->_id;
    $this->object_class = $object->_class;
  }

  /**
   * Load target object
   *
   * @param bool $cache Use object cache
   *
   * @return CService|CBlocOperatoire
   */
  function loadTargetObject($cache = true) {
    return $this->_ref_object = $this->loadFwdRef("object_id", $cache);
  }

  /**
   * @inheritDoc
   * @todo remove
   */
  function loadRefsFwd() {
    parent::loadRefsFwd();
    $this->loadTargetObject();
  }

  /**
   * Get Stock from product code and service ID
   *
   * @param string $code       Product code
   * @param int    $service_id Service ID
   * @param string $bdm        BDM
   *
   * @return CProductStockService
   */
  static function getFromCode($code, $service_id = null, $bdm = null) {
    if (!$bdm) {
      $bdm = CMedicamentProduit::getBase();
    }

    $stock = new self();

    $where                                       = array();
    $where['product.code']                       = "= '$code'";
    $where['product.bdm']                        = "= '$bdm'";
    $where['product_stock_service.object_class'] = "= 'CService'"; // XXX

    if ($service_id) {
      $where['product_stock_service.object_id'] = "= '$service_id'";
    }

    $ljoin            = array();
    $ljoin['product'] = 'product_stock_service.product_id = product.product_id';

    $stock->loadObject($where, null, null, $ljoin);

    return $stock;
  }

  /**
   * Get Stock from product speciality code and service ID
   *
   * @param string $code       Product code
   * @param int    $service_id Service ID
   * @param string $bdm        BDM
   *
   * @return CProductStockService
   */
  static function getFromCIS($code, $service_id, $bdm = null) {
    if (!$bdm) {
      $bdm = CMedicamentProduit::getBase();
    }

    foreach (CMedicamentProduit::getArticlesFromCIS($code, $bdm) as $_article) {
      $_stock = self::getFromCode($_article->code_cip, $service_id);
      if ($_stock->_id) {
        return $_stock;
      }
    }

    return new self();
  }

  /**
   * Get a stock from a product and a host
   *
   * @param CProduct  $product Product
   * @param CMbObject $host    Host
   *
   * @return CProductStockService
   */
  static function getFromProduct(CProduct $product, CMbObject $host) {
    $stock = new self;
    $stock->setObject($host);
    $stock->product_id = $product->_id;
    $stock->loadMatchingObject();

    return $stock;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = "$this->_ref_product (".$this->_ref_location->_shortview.")";
  }

  /**
   * Load locations
   *
   * @return CProductStockLocation[]
   */
  function loadRelatedLocations() {
    $where = array(
      "object_class" => "= '$this->object_class'",
      "object_id"    => "= '$this->object_id'",
    );

    $location = new CProductStockLocation;

    return $this->_ref_related_locations = $location->loadList($where, "name");
  }

  /**
   * @inheritdoc
   */
  function check() {
    if ($msg = parent::check()) {
      return $msg;
    }

    if ($this->location_id) {
      $this->completeField("object_id", "object_class");
      $location = $this->loadRefLocation();

      if (
        $location->object_class !== $this->object_class ||
        $location->object_id != $this->object_id
      ) {
        return "Le stock doit être associé à un emplacement de '" . $this->loadTargetObject() . "'";
      }
    }

    return null;
  }

  /**
   * @inheritdoc
   */
  function loadRefHost() {
    return $this->loadTargetObject();
  }

  /**
   * @inheritdoc
   */
  function setHost(CMbObject $host) {
    $this->setObject($host);
  }
}
