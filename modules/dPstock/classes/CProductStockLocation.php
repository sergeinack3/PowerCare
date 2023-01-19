<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Stock;

use Exception;
use Ox\Core\CClassMap;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Product Stock Location
 */
class CProductStockLocation extends CMbObject {
  // DB Table key
  public $stock_location_id;

  // DB Fields
  public $name;
  public $desc;
  public $position;
  public $group_id;
  public $actif;

  public $object_class;
  public $object_id;
  public $_ref_object;

  /** @var CProductStockGroup[] */
  public $_ref_group_stocks;

  /** @var CGroups */
  public $_ref_group;

  public $_type;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec                  = parent::getSpec();
    $spec->table           = "product_stock_location";
    $spec->key             = "stock_location_id";
    $spec->uniques["name"] = array("name", "object_class", "object_id");

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                 = parent::getProps();
    $props["name"]         = "str notNull seekable";
    $props["desc"]         = "text seekable";
    $props["position"]     = "num min|1";
    $props["group_id"]     = "ref notNull class|CGroups back|product_stock_locations";
    $props["actif"]        = "bool default|1";
    $props["object_id"]    = "ref notNull class|CStoredObject meta|object_class back|stock_locations";
    $props["object_class"] = "enum notNull list|CGroups|CService|CBlocOperatoire";

    $props["_type"]        = "str";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->loadTargetObject(false);

    $this->_shortview = ($this->position ? "[" . str_pad($this->position, 3, "0", STR_PAD_LEFT) . "] " : "") . $this->name;
    $this->_view      = ($this->_ref_object ? "{$this->_ref_object->_view} - " : "") . $this->_shortview;
  }

  /**
   * @inheritdoc
   */
  function updatePlainFields() {
    parent::updatePlainFields();

    if ($this->_type) {
      list($this->object_class, $this->object_id) = explode("-", $this->_type);
      $this->_type = null;
    }

    if (!$this->_id && !$this->position) {
      $existing = $this->loadList(null, "position");
      if ($location = end($existing)) {
        $this->position = $location->position + 1;
      }
      else {
        $this->position = 1;
      }
    }
  }

  /**
   * Get stock class from host class
   *
   * @param string $host_class Host class
   *
   * @return string
   */
  static function getStockClass($host_class) {
    switch ($host_class) {
      case "CGroups":
        return CProductStockGroup::class;
      default:
      case "CBlocOperatoire":
      case "CService":
        return CProductStockService::class;
    }
  }

  /**
   * Get stock type
   *
   * @return null|string
   */
  function getStockType() {
    if (!$this->_id) {
      return null;
    }

    $this->completeField("object_class");

    return self::getStockClass($this->object_class);
  }

  /**
   * Load stocks by type
   *
   * @return void
   */
  function loadRefsStocks() {
    $ljoin = array(
      "product" => "product_stock_group.product_id = product.product_id",
    );
    $this->loadBackRefs("group_stocks", "product.name", null, null, $ljoin);

    if (!empty($this->_back["group_stocks"])) {
      foreach ($this->_back["group_stocks"] as $_id => $_stock) {
        if ($_stock->loadRefProduct()->cancelled) {
          unset($this->_back["group_stocks"][$_id]);
        }
      }
    }

    $ljoin = array(
      "product" => "product_stock_service.product_id = product.product_id",
    );
    $this->loadBackRefs("service_stocks", "product.name", null, null, $ljoin);

    if (!empty($this->_back["service_stocks"])) {
      foreach ($this->_back["service_stocks"] as $_id => $_stock) {
        if ($_stock->loadRefProduct()->cancelled) {
          unset($this->_back["service_stocks"][$_id]);
        }
      }
    }
  }

  /**
   * @inheritdoc
   */
  function loadRefsFwd() {
    $this->_ref_group = $this->loadFwdRef("group_id", true);
  }

  /**
   * Returns the existing location for the product in the host,
   * if it doesn't exist, will return the first location found in the host
   *
   * @param CGroups|CService|CBlocOperatoire|CMbObject $host    Stock location's host object
   * @param CProduct                                   $product Product
   *
   * @return CProductStockLocation The location
   */
  static function getDefaultLocation(CMbObject $host, CProduct $product = null) {
    $stock_class = self::getStockClass($host->_class);

    /** @var CProductStock $stock */
    $stock = new $stock_class;
    $stock->setHost($host);
    $stock->product_id = $product->_id;
    $stock->loadMatchingObject();

    if (!$stock->_id || !$stock->location_id) {
      $ds    = $host->_spec->ds;
      $where = array(
        "object_class" => $ds->prepare("=%", $host->_class),
        "object_id"    => $ds->prepare("=%", $host->_id),
      );

      // pas loadMatchingObject a cause du "position" pré-rempli :(
      $location = new CProductStockLocation();
      if (!$location->loadObject($where, "position")) {
        $location->name     = "Lieu par défaut";
        $location->group_id = ($host instanceof CGroups ? $host->_id : $host->group_id);
        $location->setObject($host);
        $location->store();
      }

      return $location;
    }
    else {
      return $stock->loadRefLocation();
    }
  }

  /**
   * Find a stock from a product ID
   *
   * @param int $product_id Product ID
   *
   * @return CProductStock
   */
  function loadRefStock($product_id) {
    $class = $this->getStockType();

    /** @var CProductStock $stock */
    $stock             = new $class;
    $stock->product_id = $product_id;

    switch ($this->object_class) {
      case "CGroups":
        $stock->group_id = $this->object_id;
        break;
      default:
        $stock->object_id    = $this->object_id;
        $stock->object_class = $this->object_class;
        break;
    }

    $stock->loadMatchingObject();

    return $stock;
  }

  /**
   * Get a group's stock locations
   *
   * @param int $group_id Group ID
   *
   * @return CStoredObject[]
   */
  static function getGroupStockLocations($group_id) {
    $where = "
      (product_stock_location.object_id = '$group_id' AND product_stock_location.object_class = 'CGroups') OR 
      (service.group_id = '$group_id' AND product_stock_location.object_class = 'CService') OR 
      (bloc_operatoire.group_id = '$group_id' AND product_stock_location.object_class = 'CBlocOperatoire')";

    $ljoin = array(
      "service"         => "service.service_id = product_stock_location.object_id",
      "bloc_operatoire" => "bloc_operatoire.bloc_operatoire_id = product_stock_location.object_id",
    );

    $sl = new self;

    return $sl->loadList($where, null, null, null, $ljoin);
  }

  /**
   * @param int $group_id
   *
   * @return CProductStockLocation[]
   * @throws Exception
   */
  public static function getList($group_id) {
    $ds = CSQLDataSource::get('std');

    $where = [
      "actif"        => $ds->prepare('= ?', '1'),
      "object_class" => $ds->prepare("= ?", CClassMap::getSN(CGroups::class)),
      "object_id"    => $ds->prepare("= ?", $group_id),
      "group_id"     => $ds->prepare("= ?", $group_id)
    ];

    return (new self)->loadList($where);
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
}
