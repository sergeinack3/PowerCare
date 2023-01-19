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

/**
 * Societe
 */
class CSociete extends CMbObject {
  static $_load_lite = false;

  // DB Table key
  public $societe_id;

  // DB Fields
  public $name;
  public $code;
  public $distributor_code;
  public $customer_code;
  public $manufacturer_code; // in the barcodes (http://www.morovia.com/education/symbology/scc-14.asp)
  public $address;
  public $postal_code;
  public $city;
  public $phone;
  public $fax;
  public $siret;
  public $email;
  public $contact_name;
  public $carriage_paid;
  public $delivery_time;
  public $departments;

  public $_departments;
  public $_is_supplier;
  public $_is_manufacturer;

  /** @var CProductReference */
  public $_ref_product_references;

  /** @var CProductOrder[] */
  public $_ref_product_orders;

  /** @var CProduct[] */
  public $_ref_products;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec                  = parent::getSpec();
    $spec->table           = 'societe';
    $spec->key             = 'societe_id';
    $spec->uniques["name"] = array("name");

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                      = parent::getProps();
    $props['name']              = 'str notNull maxLength|50 seekable show|0';
    $props['code']              = 'str maxLength|80 seekable protected';
    $props['distributor_code']  = 'str maxLength|80 seekable protected';
    $props['customer_code']     = 'str maxLength|80';
    $props['manufacturer_code'] = 'numchar length|5 seekable protected';
    $props['address']           = 'text seekable';
    $props['postal_code']       = 'str minLength|4 maxLength|5 seekable';
    $props['city']              = 'str seekable';
    $props['phone']             = "phone";
    $props['fax']               = "phone";
    $props['siret']             = 'code siret seekable';
    $props['email']             = 'email';
    $props['contact_name']      = 'str seekable';
    $props['carriage_paid']     = 'str';
    $props['delivery_time']     = 'str';
    $props['departments']       = 'text'; // not str, as it could be longer than 255 chars

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view        = $this->name;
    $this->_departments = explode("|", $this->departments);
    CMbArray::removeValue("", $this->_departments);

    if (count($this->_departments)) {
      $this->_view .= " (" . implode(", ", $this->_departments) . ")";
    }

    if (self::$_load_lite === true) {
      return;
    }

    $this->_is_supplier     = $this->countBackRefs("product_references") > 0;
    $this->_is_manufacturer = $this->countBackRefs("products") > 0;
  }

  /**
   * Get manufacturers
   *
   * @param bool $also_inactive Load also inactive ones
   *
   * @return self[]
   */
  static function getManufacturers($also_inactive = true) {
    $societe = new self;
    $list    = $societe->loadList(null, "name");
    foreach ($list as $_id => $_societe) {
      if (!($_societe->_is_manufacturer || $also_inactive && !$_societe->_is_supplier)) {
        unset($list[$_id]);
      }
    }

    return $list;
  }

  /**
   * Get suppliers
   *
   * @param bool $also_inactive Load also inactive ones
   *
   * @return self[]
   */
  static function getSuppliers($also_inactive = true) {
    $societe = new self;
    $list    = $societe->loadList(null, "name");
    foreach ($list as $_id => $_societe) {
      if (!($_societe->_is_supplier || $also_inactive && !$_societe->_is_manufacturer)) {
        unset($list[$_id]);
      }
    }

    return $list;
  }

  /**
   * @inheritdoc
   */
  function updatePlainFields() {
    parent::updatePlainFields();
    if ($this->_departments) {
      foreach ($this->_departments as &$_dep) {
        $_dep = str_pad($_dep, 2, "0", STR_PAD_LEFT);
      }
      $this->departments = implode("|", $this->_departments);
    }
  }

  /**
   * @inheritdoc
   */
  function loadRefsBack() {
    $ljoin                             = array(
      "product" => "product_reference.product_id = product.product_id"
    );
    $where                             = array(
      "product_reference.societe_id" => " = '$this->_id'"
    );
    $reference                         = new CProductReference();
    $this->_ref_product_references     = $reference->loadList($where, "product.name", null, null, $ljoin);
    $this->_back["product_references"] = $this->_ref_product_references;

    $this->_ref_products       = $this->loadBackRefs('products', "name");
    $this->_ref_product_orders = $this->loadBackRefs('product_orders', "date_ordered");
  }
}
