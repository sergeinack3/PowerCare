<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Stock;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Product Reception
 */
class CProductReception extends CMbObject {
  // DB Table key
  public $reception_id;

  // DB Fields
  public $date;
  public $societe_id;
  public $group_id;
  public $reference;
  public $bill_number;
  public $bill_date;
  public $locked;

  /** @var CProductOrderItemReception[] */
  public $_ref_reception_items;
  /** @var CProductOrder */
  public $_ref_order;

  /** @var int */
  public $_count_reception_items;
  public $_total;
  public $_total_ttc;
  public $_total_tva;

  /** @var CSociete */
  public $_ref_societe;

  /** @var CGroups */
  public $_ref_group;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec                       = parent::getSpec();
    $spec->table                = "product_reception";
    $spec->key                  = "reception_id";
    $spec->uniques["reference"] = array("reference");

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                = parent::getProps();
    $props['date']        = 'dateTime seekable';
    $props['societe_id']  = 'ref class|CSociete seekable back|product_receptions';
    $props['group_id']    = 'ref notNull class|CGroups show|0 back|product_receptions';
    $props['reference']   = 'str notNull seekable';
    $props['locked']      = 'bool notNull default|0';
    $props['bill_number'] = 'str maxLength|64 protected seekable';
    $props['bill_date']   = 'date';
    $props['_total']      = 'currency';
    $props['_total_tva']  = 'currency';
    $props['_total_ttc']  = 'currency';

    return $props;
  }

  /**
   * Get unique order number
   *
   * @return string
   */
  private function getUniqueNumber() {
    $format = CAppUI::conf('dPstock CProductOrder order_number_format');

    if (strpos($format, '%id') === false) {
      $format .= '%id';
    }

    $format = str_replace('%id', str_pad($this->_id ? $this->_id : 0, 4, '0', STR_PAD_LEFT), $format);

    return CMbDT::format(null, $format);
  }

  /**
   * Find a reception from an order ID
   *
   * @param int  $order_id Order ID
   * @param bool $locked   Look among locked receptions
   *
   * @return array
   */
  function findFromOrder($order_id, $locked = false) {
    $receptions_prob = array();
    $receptions      = array();

    $order = new CProductOrder;
    $order->load($order_id);
    $order->loadBackRefs("order_items");

    foreach ($order->_back["order_items"] as $order_item) {
      $r = $order_item->loadBackRefs("receptions");

      foreach ($r as $_r) {
        if (!$_r->reception_id) {
          continue;
        }

        $_r->loadRefReception();
        if ($locked || $_r->_ref_reception->locked) {
          continue;
        }

        if (!isset($receptions_prob[$_r->reception_id])) {
          $receptions_prob[$_r->reception_id] = 0;
        }

        $receptions_prob[$_r->reception_id]++;
        $receptions[$_r->reception_id] = $_r->_ref_reception;
      }
    }

    if (!count($receptions_prob)) {
      return $receptions;
    }

    $reception_id = array_search(max($receptions_prob), $receptions_prob);
    if ($reception_id) {
      $this->load($reception_id);
    }

    return $receptions;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->loadRefSociete();
    $this->_view = $this->reference . ($this->societe_id ? " - {$this->_ref_societe->_view}" : "");
  }

  /**
   * @inheritdoc
   */
  function updatePlainFields() {
    if (!$this->_id && $this->locked === null) {
      $this->locked = "0";
    }

    parent::updatePlainFields();
  }

  /**
   * @inheritdoc
   */
  function store() {
    if (!$this->_id && empty($this->reference)) {
      $this->reference = uniqid(rand());
      if ($msg = parent::store()) {
        return $msg;
      }
      $this->reference = $this->getUniqueNumber();
    }

    return parent::store();
  }

  /**
   * Load order
   *
   * @return CProductOrder
   */
  function loadRefOrder() {
    $order_id     = explode("-", $this->reference);
    $order_number = $order_id[0];

    if (count($order_id) > 2) {
      $longeur      = strlen($this->reference) - strlen(end($order_id)) - 1;
      $order_number = substr($this->reference, 0, $longeur);
    }

    $where                 = array();
    $where["order_number"] = " = '$order_number'";

    $order = new CProductOrder();
    $order->loadObject($where);

    return $this->_ref_order = $order;
  }

  /**
   * @inheritdoc
   */
  function loadRefsBack() {
    $this->_ref_reception_items = $this->loadBackRefs('reception_items');
  }

  /**
   * Compute total order price
   *
   * @return void
   */
  function updateTotal() {
    $this->loadRefsBack();
    $this->_total = 0;
    $this->_total_ttc = 0;
    $this->_total_tva = 0;
    foreach ($this->_ref_reception_items as $_item) {
      $this->_total += $_item->computePrice();
      $this->_total_ttc += $_item->_price_ttc;
      $this->_total_tva += $_item->_price_tva;
    }
  }

  /**
   * Count repcetion items
   *
   * @todo supprimer ceci
   *
   * @return int
   */
  function countReceptionItems() {
    return $this->_count_reception_items = $this->countBackRefs('reception_items');
  }

  /**
   * Load societe
   *
   * @return CSociete
   */
  function loadRefSociete() {
    return $this->_ref_societe = $this->loadFwdRef("societe_id", true);
  }

  /**
   * @inheritdoc
   */
  function loadRefsFwd() {
    $this->loadRefSociete();
    $this->_ref_group = $this->loadFwdRef("group_id", true);
  }

  /**
   * @inheritdoc
   */
  function getPerm($permType) {
    if (!$this->_ref_reception_items) {
      $this->loadRefsBack();
    }

    foreach ($this->_ref_reception_items as $item) {
      if (!$item->getPerm($permType)) {
        return false;
      }
    }

    return true;
  }
}
