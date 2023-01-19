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
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;

/**
 * Product return form
 */
class CProductReturnForm extends CMbObject {
  public $product_return_form_id;

  // DB Fields
  public $group_id;
  public $datetime;
  public $supplier_id;
  public $address_class;
  public $address_id;
  public $status;
  public $return_number;
  public $comments;

  public $_total;

  /** @var CSociete */
  public $_ref_supplier;

  /** @var CProductOutput[] */
  public $_ref_outputs;

  /** @var CGroups */
  public $_ref_group;

  /** @var CGroups|CFunctions|CBlocOperatoire */
  public $_ref_address;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'product_return_form';
    $spec->key   = 'product_return_form_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $specs                  = parent::getProps();
    $specs['group_id']      = 'ref notNull class|CGroups back|product_return_forms';
    $specs['datetime']      = 'dateTime notNull';
    $specs['supplier_id']   = 'ref notNull class|CSociete back|return_forms';
    $specs['address_class'] = 'enum notNull list|CGroups|CFunctions|CBlocOperatoire';
    $specs['address_id']    = 'ref notNull class|CMbObject meta|address_class back|product_address_return_forms';
    $specs['status']        = 'enum notNull list|new|pending|sent default|new';
    $specs['comments']      = 'text';
    $specs['return_number'] = 'str';
    $specs['_total']        = 'currency';

    return $specs;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = sprintf("%s : %s", $this->getFormattedValue("datetime"), $this->getFormattedValue("status"));
  }

  /**
   * @inheritdoc
   */
  function updatePlainFields() {
    parent::updatePlainFields();

    if (!$this->_id) {
      $this->group_id = CGroups::loadCurrent()->_id;
    }
  }

  /**
   * Update total price
   *
   * @return float
   */
  function updateTotal() {
    $this->_total = 0;
    $this->loadRefsOutputs();
    foreach ($this->_ref_outputs as $_output) {
      $this->_total += $_output->unit_price * $_output->quantity;
    }

    return $this->_total;
  }

  /**
   * @inheritdoc
   */
  function loadView() {
    parent::loadView();

    $this->updateTotal();
    $this->loadRefsOutputs();
  }

  /**
   * Load outputs
   *
   * @return CProductOutput[]
   */
  function loadRefsOutputs() {
    $this->_ref_outputs = $this->loadBackRefs("product_outputs");

    foreach ($this->_ref_outputs as $_output) {
      $_output->loadRefStock()->loadRefProduct();
    }

    return $this->_ref_outputs;
  }

  /**
   * Get unique order number
   *
   * @return string
   */
  function getUniqueNumber() {
    $format = CAppUI::conf('dPstock CProductOrder order_number_format');

    if (strpos($format, '%id') === false) {
      $format .= '%id';
    }

    $format = str_replace('%id', str_pad($this->_id ? $this->_id : 0, 4, '0', STR_PAD_LEFT), $format);
    $number = CMbDT::format(null, $format);

    return $number;
  }

  /**
   * @inheritdoc
   */
  function store() {
    $is_new = !$this->_id;

    if ($is_new) {
      if (!$this->group_id) {
        $this->group_id = CGroups::loadCurrent()->_id;
      }

      if (!$this->address_id) {
        $group = $this->loadRefGroup();

        if ($group->pharmacie_id) {
          $this->address_class = "CFunctions";
          $this->address_id    = $group->pharmacie_id;
        }
        else {
          $this->address_class = "CGroups";
          $this->address_id    = $this->group_id;
        }
      }
    }

    if ($is_new && empty($this->return_number)) {
      $this->return_number = uniqid(rand());

      if ($msg = parent::store()) {
        return $msg;
      }

      $this->return_number = $this->getUniqueNumber();
    }

    if (!$is_new && $this->fieldModified("status", "sent")) {
      $this->completeField("supplier_id");
      $_supplier = $this->loadRefSupplier();

      foreach ($this->loadRefsOutputs() as $_output) {
        CProductMovement::logTransaction($_output, $_output->loadRefStock(), $_supplier);
      }
    }

    return parent::store();
  }

  /**
   * Load supplier
   *
   * @return CSociete
   */
  function loadRefSupplier() {
    return $this->_ref_supplier = $this->loadFwdRef("supplier_id");
  }

  /**
   * Load group
   *
   * @return CGroups
   */
  function loadRefGroup() {
    return $this->_ref_group = $this->loadFwdRef("group_id");
  }

  /**
   * Load postal address object
   *
   * @return CGroups|CFunctions|CBlocOperatoire
   */
  function loadRefAddress() {
    $this->_ref_address = $this->loadFwdRef("address_id", true);

    if ($this->address_class == "CFunctions" || $this->address_class == "CBlocOperatoire") {
      $this->_ref_address->loadRefGroup();
    }

    return $this->_ref_address;
  }
}
