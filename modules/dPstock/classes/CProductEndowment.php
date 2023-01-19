<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Stock;

use Ox\Core\CMbObject;
use Ox\Mediboard\Hospi\CService;

/**
 * Product Endowment
 */
class CProductEndowment extends CMbObject {
  public $endowment_id;

  public $name;
  public $service_id;
  public $actif;
  public $_duplicate_to_service_id;

  /** @var CService */
  public $_ref_service;

  /** @var CProductEndowmentItem[] */
  public $_ref_endowment_items;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec                    = parent::getSpec();
    $spec->table             = 'product_endowment';
    $spec->key               = 'endowment_id';
    $spec->uniques["unique"] = array("name", "service_id");

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                             = parent::getProps();
    $props["name"]                     = "str notNull";
    $props["service_id"]               = "ref notNull class|CService autocomplete|nom dependsOn|group_id back|endowments";
    $props["actif"]                    = "bool default|1";
    $props["_duplicate_to_service_id"] = $props["service_id"];

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = "$this->name";

    if ($this->_ref_service) {
      $this->_view .= " ({$this->_ref_service->_view})";
    }
  }

  /**
   * @inheritdoc
   */
  function loadRefsFwd() {
    parent::loadRefsFwd();

    $this->loadRefService();
  }

  /**
   * Load service
   *
   * @return CService
   */
  function loadRefService() {
    return $this->_ref_service = $this->loadFwdRef("service_id", true);
  }

  /**
   * @inheritdoc
   */
  function loadRefsBack() {
    $this->loadRefsEndowmentItems();
  }

  /**
   * Load items
   *
   * @return CProductEndowmentItem[]
   */
  function loadRefsEndowmentItems($limit = null) {
    $ljoin = array(
      "product"                => "product.product_id = product_endowment_item.product_id",
    );

    return $this->_ref_endowment_items = $this->loadBackRefs('endowment_items', "product.name", $limit, "product_id" , $ljoin);
  }

  /**
   * @inheritdoc
   */
  function getPerm($permType) {
    $this->loadRefsFwd();

    return parent::getPerm($permType) && $this->_ref_service->getPerm($permType);
  }

  /**
   * @inheritdoc
   */
  function store() {
    if ($this->_id && $this->_duplicate_to_service_id) {
      $this->completeField("name");

      $dup             = new self;
      $dup->service_id = $this->_duplicate_to_service_id;
      $dup->name       = $this->name;
      if ($msg = $dup->store()) {
        return $msg;
      }

      $items = $this->loadRefsEndowmentItems();

      foreach ($items as $_item) {
        if ($_item->cancelled) {
          continue;
        }

        $_dup_item               = new CProductEndowmentItem();
        $_dup_item->product_id   = $_item->product_id;
        $_dup_item->quantity     = $_item->quantity;
        $_dup_item->endowment_id = $dup->_id;
        $_dup_item->store();
      }

      $this->_duplicate_to_service_id = null;

      return null;
    }

    return parent::store();
  }
}
