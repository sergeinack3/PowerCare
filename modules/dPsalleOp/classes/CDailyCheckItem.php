<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp;

use Ox\Core\CMbObject;

/**
 * Class CDailyCheckItem
 * @package Ox\Mediboard\SalleOp
 */
class CDailyCheckItem extends CMbObject {
  public $daily_check_item_id;

  // DB Fields
  public $list_id;
  public $item_type_id;
  public $checked;
  public $commentaire;

  /** @var CDailyCheckList */
  public $_ref_list;

  /** @var CDailyCheckItemType */
  public $_ref_item_type;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'daily_check_item';
    $spec->key   = 'daily_check_item_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props['list_id']      = 'ref notNull class|CDailyCheckList back|items';
    $props['item_type_id'] = 'ref notNull class|CDailyCheckItemType back|items';
    $props['checked']      = 'enum list|yes|no|nr|na';
    $props['commentaire']  = 'text';
    return $props;
  }

  /**
   * @return string
   */
  function getAnswer() {
    return $this->getFormattedValue("checked");
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = "$this->_ref_item_type (".$this->getAnswer().")";
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    $this->loadRefList();
    $this->loadRefItemType();
  }

  /**
   * Get check list
   *
   * @return CDailyCheckList
   */
  function loadRefList(){
    return $this->_ref_list = $this->loadFwdRef("list_id", true);
  }

  /**
   * Get item type
   *
   * @return CDailyCheckItemType
   */
  function loadRefItemType(){
    return $this->_ref_item_type = $this->loadFwdRef("item_type_id", true);
  }
}
