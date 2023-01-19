<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp;

use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CPoseDispositifVasculaire;

/**
 * Check item category
 */
class CDailyCheckItemCategory extends CMbObject {
  public $daily_check_item_category_id;

  // DB Fields
  public $title;
  public $desc;
  public $index;

  ////////
  public $target_class;
  public $target_id;
  public $type;
  // OR //
  public $list_type_id;
  ////////

  /** @var CDailyCheckItemType[] */
  public $_ref_item_types;

  /** @var CSalle|CBlocOperatoire|COperation|CPoseDispositifVasculaire */
  public $_ref_target;

  /** @var CDailyCheckListType */
  public $_ref_list_type;

  public $_target_guid;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'daily_check_item_category';
    $spec->key   = 'daily_check_item_category_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props['title'] = 'str notNull';
    $props['desc']  = 'text';
    $props['index'] = 'num notNull min|1 default|1';

    $props['target_class'] = 'enum list|CSalle|CBlocOperatoire|COperation|CPoseDispositifVasculaire notNull default|CSalle';
    $props['target_id']    = 'ref class|CMbObject meta|target_class back|check_list_categories';
    $props['type']         = 'enum list|'.implode('|', array_keys(CDailyCheckList::$types));
    $props['list_type_id'] = 'ref class|CDailyCheckListType autocomplete|_view back|daily_check_list_categories';

    $props['_target_guid'] = 'str notNull';
    return $props;
  }

  /**
   * Load target object
   *
   * @return CSalle|CBlocOperatoire|COperation|CPoseDispositifVasculaire
   */
  function loadRefTarget(){
    return $this->_ref_target = $this->loadFwdRef("target_id");
  }

  /**
   * Load list type
   *
   * @return CDailyCheckListType
   */
  function loadRefListType(){
    return $this->_ref_list_type = $this->loadFwdRef("list_type_id");
  }

  /**
   * Load item types
   *
   * @return CDailyCheckItemType[]
   */
  function loadRefItemTypes() {
    return $this->_ref_item_types = $this->loadBackRefs("item_types", "`index`, title");
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = ($this->target_class == 'CBlocOperatoire' ? 'Salle de réveil' : $this->getLocale("target_class"))." - $this->title";
  }

  /**
   * Get categories tree
   *
   * @param bool $operation see operations
   *
   * @return array
   */
  static function getCategoriesTree($operation = false){
    $object = new self();

    $target_classes = CDailyCheckList::getNonHASClasses($operation);

    $targets = array();
    $by_class = array();

    foreach ($target_classes as $_class) {
      if ($_class != "COperation") {
        /** @var CSalle|CBlocOperatoire $_object */
        $_object = new $_class;
        //$_targets = $_object->loadGroupList();
        $_targets = $_object->loadList();
        array_unshift($_targets, $_object);

        $targets[$_class] = array_combine(CMbArray::pluck($_targets, "_id"), $_targets);
      }

      $where = array("target_class" => "= '$_class'");

      if ($_class == "COperation") {
        $where["list_type_id"] = ' IS NOT NULL';
      }

      /** @var CDailyCheckItemCategory[] $_list */
      $_list = $object->loadList($where, "target_id+0, title", null, null, null, null, null, false); // target_id+0 to have NULL at the beginning

      $by_object = array();
      foreach ($_list as $_category) {
        $_key = $_category->target_id ? $_category->target_id : "all";
        $by_object[$_key][$_category->_id] = $_category;
      }

      $by_class[$_class] = $by_object;
    }

    return array(
      $targets,
      $by_class,
    );
  }
}
