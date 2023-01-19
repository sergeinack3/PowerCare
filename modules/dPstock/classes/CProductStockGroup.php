<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Stock;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Mediboard\Dispensation\CProductDelivery;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;

/**
 * Group Product Stock
 */
class CProductStockGroup extends CProductStock {
  public $group_id;

  /** @var CGroups */
  public $_ref_group;

  /** @var CProductDelivery[] */
  public $_ref_deliveries;

  public $_zone_future = 0;
  public $_ordered_count = 0;
  public $_ordered_last;
  public $_orders = array();

  /** @var CGroups */
  private static $_host_group;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'product_stock_group';
    $spec->key   = 'stock_id';

    if (!CAppUI::conf("dPstock host_group_id")) {
      $uniques = array("product_id", "location_id", "group_id");
    }
    else {
      $uniques = array("product_id", "location_id");
    }

    $spec->uniques["product"] = $uniques;

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                   = parent::getProps();
    $props["product_id"]    .= " back|stocks_group";
    $props['group_id']       = 'ref notNull class|CGroups back|product_stocks';
    $props["location_id"]   .= " back|group_stocks";
    $props['_ordered_count'] = 'num notNull pos';
    $props['_ordered_last']  = 'dateTime';
    $props['_zone_future']   = 'num';

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = "$this->_ref_product (".$this->_ref_location->_shortview.")";
  }

  /**
   * Load orders
   *
   * @return void
   */
  function loadRefOrders() {
    // Verifies wether there are pending orders for this stock
    $where                 = array();
    $where['date_ordered'] = 'IS NOT NULL';
    $where[]               = 'deleted IS NULL OR deleted = 0';
    $orderby               = 'date_ordered ASC';
    $order                 = new CProductOrder();

    /** @var CProductOrder[] $list_orders */
    $list_orders   = $order->loadList($where, $orderby);
    $this->_orders = array();

    foreach ($list_orders as $order) {
      if (!$order->_received && !$order->cancelled) {
        $done = false;
        foreach ($order->_ref_order_items as $item) {
          $item->loadRefsFwd();
          $item->_ref_reference->loadRefsFwd();
          $item->_ref_order->loadRefsFwd();

          if (
            $item->_ref_reference->_ref_product &&
            $this->_ref_product &&
            $item->_ref_reference->_ref_product->_id == $this->_ref_product->_id
          ) {
            $this->_ordered_count += $item->quantity;
            $this->_ordered_last  = max(array($item->_ref_order->date_ordered, $this->_ordered_last));
            if (!$done) {
              $this->_orders[] = $order;
              $done            = true;
            }
          }
        }
      }
    }

    $future_quantity = $this->quantity + $this->_ordered_count;

    if ($future_quantity <= $this->order_threshold_critical) {
      $this->_zone_future = 0;
    }
    elseif ($future_quantity <= $this->order_threshold_min) {
      $this->_zone_future = 1;
    }
    elseif ($future_quantity <= $this->order_threshold_optimum) {
      $this->_zone_future = 2;
    }
    else {
      $this->_zone_future = 3;
    }
  }

  /**
   * Get a stock from a product code
   *
   * @param string $code Product code
   *
   * @return CProductStockGroup
   */
  static function getFromCode($code) {
    $stock = new self();

    $where = array('product.code' => "= '$code'");
    $ljoin = array('product' => 'product_stock_group.product_id = product.product_id');

    $stock->loadObject($where, null, null, $ljoin);

    return $stock;
  }

  /**
   * Get group host
   *
   * @param bool $get_id Only get the ID, not the object
   *
   * @return CGroups|int|null
   */
  static function getHostGroup($get_id = true) {
    if (isset(self::$_host_group)) {
      return $get_id ? self::$_host_group->_id : self::$_host_group;
    }

    $host_group_id = CAppUI::conf("dPstock host_group_id");

    if (!$host_group_id) {
      $host_group_id = CGroups::loadCurrent()->_id;
    }

    $group = new CGroups;
    $group->load($host_group_id);

    self::$_host_group = $group;

    if ($get_id) {
      return $group->_id;
    }

    return $group;
  }

  /**
   * Get servoces list
   *
   * @return CService[]
   */
  static function getServicesList() {
    $service = new CService();

    $where = array();

    if (CAppUI::conf("dPstock host_group_id")) {
      $where["group_id"] = "IS NOT NULL";
    }
    $where["cancelled"] = " = '0'";

    return $service->loadListWithPerms(PERM_READ, $where, "nom");
  }

  /**
   * @inheritdoc
   */
  function loadRefsFwd() {
    parent::loadRefsFwd();
    $this->loadRefGroup();
    $this->setHost($this->_ref_group);
  }

  /**
   * Load group
   *
   * @return CGroups
   */
  function loadRefGroup() {
    return $this->_ref_group = $this->loadFwdRef("group_id", true);
  }

  /**
   * @inheritdoc
   */
  function loadRefsBack() {
    $this->loadRefsDeliveries();
  }

  /**
   * Load deliveries
   *
   * @return CProductDelivery[]
   */
  function loadRefsDeliveries() {
    return $this->_ref_deliveries = $this->loadBackRefs('deliveries');
  }

  /**
   * Load locations
   *
   * @return CProductStockLocation[]
   */
  function loadRelatedLocations() {
    $where = array(
      "object_class" => "= 'CGroups'",
      "object_id"    => "= '$this->group_id'",
    );

    $location = new CProductStockLocation();

    return $this->_ref_related_locations = $location->loadList($where, "name");
  }

  /**
   * @inheritdoc
   */
  function updatePlainFields() {
    parent::updatePlainFields();

    $this->completeField("group_id");
    if (!$this->group_id) {
      $this->group_id = CProductStockGroup::getHostGroup();
    }
  }

  /**
   * Load host
   *
   * @return CGroups
   */
  function loadRefHost() {
    return $this->loadRefGroup();
  }

  /**
   * Set host
   *
   * @param CGroups|CMbObject $host Host
   *
   * @return void
   */
  function setHost(CMbObject $host) {
    $this->_ref_group = $host;
    $this->group_id   = $host->_id;
  }
}
