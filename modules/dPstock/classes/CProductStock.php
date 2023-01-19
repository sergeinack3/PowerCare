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
use Ox\Core\Module\CModule;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Dispensation\CProductDelivery;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;

/**
 * Product Stock
 */
class CProductStock extends CMbObject {
  public $stock_id;

  // DB Fields
  public $product_id;
  public $quantity;
  public $code_up;
  public $order_threshold_critical;
  public $order_threshold_min;
  public $order_threshold_optimum;
  public $order_threshold_max;
  public $location_id;

  // Stock percentages
  public $_quantity;
  public $_critical;
  public $_min;
  public $_optimum;
  public $_max;
  // In which part of the graph the quantity is
  public $_zone = 0;
  //if is a manual mofication
  public $_modif_manual;

  public $_package_quantity; // The number of packages
  public $_package_mod; // The modulus of the quantity

  public $_quantity_unite_ref;

  /** @var CProduct */
  public $_ref_product;

  /** @var CProductStockLocation */
  public $_ref_location;

  /** @var CProductStockLocation[] */
  public $_ref_related_locations;

  /** @var CProductDelivery */
  public $_delivery_command;

  /**
   * @inheritdoc
   */
  function getProps() {
    $props               = parent::getProps();
    $props['product_id'] = 'ref notNull class|CProduct seekable autocomplete|name show|0 dependsOn|cancelled';

    $type              = CAppUI::gconf("dPstock CProductStock allow_quantity_fractions") ? "float" : "num";
    $props['quantity'] = "$type notNull";
    $props['code_up']  = 'num';

    $props['order_threshold_critical'] = 'num min|0';
    $props['order_threshold_min']      = 'num min|0 notNull moreEquals|order_threshold_critical';
    $props['order_threshold_optimum']  = 'num min|0 moreEquals|order_threshold_min';
    $props['order_threshold_max']      = 'num min|0 moreEquals|order_threshold_optimum';
    $props['location_id']              = 'ref notNull class|CProductStockLocation autocomplete|name|true';
    $props['_quantity']                = 'pct';
    $props['_critical']                = 'pct';
    $props['_min']                     = 'pct';
    $props['_optimum']                 = 'pct';
    $props['_max']                     = 'pct';
    $props['_zone']                    = 'num';
    $props['_package_quantity']        = 'str';
    $props['_package_mod']             = 'str';
    $props['_modif_manual']            = 'bool default|0';
    $props['_quantity_unite_ref']      = 'float';

    return $props;
  }

  /**
   * Compute optimum quantity
   *
   * @return float
   */
  function getOptimumQuantity() {
    $this->completeField(
      "order_threshold_optimum",
      "order_threshold_min",
      "order_threshold_max"
    );

    if ($this->order_threshold_optimum) {
      return $this->order_threshold_optimum;
    }
    else {
      if ($this->order_threshold_max) {
        return ($this->order_threshold_min + $this->order_threshold_max) / 2;
      }
      else {
        return $this->order_threshold_min * 2;
      }
    }
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->loadRefsFwd();
    $this->_view = $this->_ref_product->_view;

    $units = $this->_ref_product->_unit_quantity ? $this->_ref_product->_unit_quantity : 1;

    $this->_package_mod      = $this->quantity % $units;
    $this->_package_quantity = $this->quantity / $units;

    if ($this->_package_mod || !CAppUI::gconf("dPstock CProductStock allow_quantity_fractions")) {
      $this->_package_quantity = floor($this->_package_quantity);
    }

    // Calculation of the levels for the bargraph
    $max = max(
        $this->quantity,
        $this->order_threshold_min,
        $this->order_threshold_optimum,
        $this->order_threshold_max
      ) / 100;

    if ($max > 0) {
      $this->_quantity = $this->quantity / $max;
      $this->_critical = $this->order_threshold_critical / $max;
      $this->_min      = $this->order_threshold_min / $max - $this->_critical;
      $this->_optimum  = $this->order_threshold_optimum / $max - $this->_critical - $this->_min;
      $this->_max      = $this->order_threshold_max / $max - $this->_critical - $this->_min - $this->_optimum;

      if ($this->quantity <= $this->order_threshold_critical) {
        $this->_zone = 0;
      }
      elseif ($this->quantity <= $this->order_threshold_min) {
        $this->_zone = 1;
      }
      elseif ($this->quantity <= $this->order_threshold_optimum) {
        $this->_zone = 2;
      }
      else {
        $this->_zone = 3;
      }
    }
  }

  /**
   * @inheritdoc
   */
  function store() {
    $this->completeField("location_id", "quantity");
    $this->loadOldObject();
    $old_id       = $this->_id;
    $old_quantity = $this->_old->quantity;

    if (!$this->location_id) {
      $location          = CProductStockLocation::getDefaultLocation($this->loadRefHost(), $this->loadRefProduct());
      $this->location_id = $location->_id;
    }

    // Standard store
    if ($msg = parent::store()) {
      return $msg;
    }

    //Création d'un mouvement lorsque le stock réel du patient est utilisé
    $quantite = $old_id ? ($this->quantity - $old_quantity) : $this->quantity;
    if ((!$old_id || $this->_modif_manual) && CAppUI::gconf("pharmacie CStockSejour use_stock_reel") && $quantite != 0) {
      $mvt           = new CStockMouvement();
      $mvt->type     = "apparition";
      $mvt->quantite = $quantite;
      $mvt->code_up  = $this->code_up;
      $mvt->_bdm     = $this->loadRefProduct()->bdm;
      $mvt->setCible($this);
      $mvt->_increment_stock = false;
      if ($msg = $mvt->store()) {
        return $msg;
      }
    }

    return null;
  }

  /**
   * Load location
   *
   * @return CProductStockLocation
   */
  function loadRefLocation() {
    return $this->_ref_location = $this->loadFwdRef("location_id", true);
  }

  /**
   * Load product
   *
   * @param boolean $cache Use object cache
   *
   * @return CProduct
   */
  function loadRefProduct($cache = true) {
    return $this->_ref_product = $this->loadFwdRef("product_id", $cache);
  }

  /**
   * @inheritdoc
   */
  function loadRefsFwd() {
    $this->loadRefLocation();
    $this->loadRefProduct();
  }

  /**
   * @inheritdoc
   */
  function getPerm($permType) {
    return $this->loadRefProduct()->getPerm($permType) &&
      $this->loadRefHost()->getPerm($permType);
  }

  /**
   * Returns the host object
   *
   * @return CGroups|CService|CBlocOperatoire
   */
  function loadRefHost() {
    trigger_error(__METHOD__ . " not implemented");
  }

  /**
   * Sets the host object
   *
   * @param CMbObject $host Host object
   *
   * @return void
   */
  function setHost(CMbObject $host) {
    trigger_error(__METHOD__ . " not implemented");
  }
}
