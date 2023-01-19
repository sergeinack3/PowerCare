<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Check list group
 */
class CDailyCheckListGroup extends CMbObject
{
  public $check_list_group_id;

  public $group_id;
  public $title;
  public $description;
  public $actif;

  public $_type_has;
  public $_duplicate;

  /** @var CGroups */
  public $_ref_group;

  /** @var CDailyCheckListType[] */
  public $_ref_check_liste_types;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'daily_check_list_group';
    $spec->key   = 'check_list_group_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props['group_id']    = 'ref notNull class|CGroups back|daily_check_list_group';
    $props['title']       = 'str notNull';
    $props['description'] = 'text';
    $props['actif']       = 'bool default|1';

    $props['_type_has'] = 'text';
    $props['_duplicate'] = 'bool';
    return $props;
  }

  /**
   * @see parent::store()
   */
  function store() {
    if ($msg = parent::store()) {
      return $msg;
    }

    if ($this->_duplicate && $this->_type_has) {
      $this->duplicate();
    }
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->title;
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
   * Load group
   *
   * @return CDailyCheckListType[]
   */
  function loadRefChecklist() {
    return $this->_ref_check_liste_types = $this->loadBackRefs("check_list_group", "daily_check_list_type_id");
  }

  /**
   * Duplicate checklist HAS
   *
   * @return void|string
   */
  function duplicate() {
    $types_checklist = array_intersect(CDailyCheckList::$types, array($this->_type_has));
    foreach ($types_checklist as $type_name => $type) {
      $checklist_type = new CDailyCheckListType();
      $checklist_type->group_id             = $this->group_id;
      $checklist_type->check_list_group_id  = $this->_id;
      $checklist_type->type                 = 'intervention';
      $checklist_type->title                = CAppUI::tr("CDailyCheckList.$type.$type_name.title");
      $checklist_type->description          = CAppUI::tr("CDailyCheckList.$type.$type_name.small");
      $checklist_type->type_validateur      = "chir_interv|op|op_panseuse|iade|sagefemme|manipulateur";
      if ($type_name == "preop_2016") {
        $checklist_type->decision_go = 1;
      }
      elseif ($type_name == "postop_2016") {
        $checklist_type->alert_child = 1;
      }
      if ($msg = $checklist_type->store()) {
        return $msg;
      }

      $where = array();
      $where["type"] = " = '$type_name'";
      $where["target_class"] = " = 'COperation'";
      $where["list_type_id"] = " IS NULL";
      $_categorie = new CDailyCheckItemCategory();

      foreach ($_categorie->loadList($where, "daily_check_item_category_id") as $categorie) {
        /* @var CDailyCheckItemCategory $categorie*/
        $items = $categorie->loadRefItemTypes();
        $new_categorie = $categorie;
        $new_categorie->_id  = "";
        $new_categorie->list_type_id  = $checklist_type->_id;
        if ($msg = $new_categorie->store()) {
          return $msg;
        }
        foreach ($items as $item) {
          $new_item = $item;
          $new_item->_id  = "";
          $new_item->category_id  = $new_categorie->_id;
          if ($msg = $new_item->store()) {
            return $msg;
          }
        }
      }
    }
    return null;
  }

  /**
   * @return \Ox\Core\CStoredObject[]
   */
  static function loadChecklistGroup() {
    $list_group = new self;
    $list_group->group_id = CGroups::loadCurrent()->_id;
    $list_group->actif    = 1;
    $checklists_group = $list_group->loadMatchingList("title");
    foreach ($checklists_group as $check_group) {
      $check_group->loadRefChecklist();
    }

    return $checklists_group;
  }
}
