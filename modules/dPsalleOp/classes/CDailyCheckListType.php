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
use Ox\Mediboard\Bloc\CSSPI;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Personnel\CPersonnel;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CPoseDispositifVasculaire;

/**
 * Check list type
 */
class CDailyCheckListType extends CMbObject {
  public $daily_check_list_type_id;

  public $group_id;
  public $check_list_group_id;
  public $type;
  public $title;
  public $type_validateur;
  public $description;
  public $lock_view;
  public $use_validate_2;
  public $decision_go;
  public $alert_child;

  public $_object_guid;
  public $_duplicate;

  /** @var CSalle|CBlocOperatoire|COperation|CPoseDispositifVasculaire */
  public $_ref_object;

  /** @var CDailyCheckItemCategory[] */
  public $_ref_categories;

  /** @var CGroups */
  public $_ref_group;

  /** @var CDailyCheckListTypeLink[] */
  public $_ref_type_links;

  public $_links = array();

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'daily_check_list_type';
    $spec->key   = 'daily_check_list_type_id';
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props['group_id']        = 'ref notNull class|CGroups back|daily_check_list_types';
    $props['check_list_group_id'] = 'ref class|CDailyCheckListGroup back|check_list_group';
    $props['type']            = 'enum notNull list|ouverture_salle|fermeture_salle|ouverture_preop|fermeture_preop|ouverture_sspi|intervention|fermeture_sspi default|ouverture_salle';
    $props['title']           = 'str notNull';
    $props['type_validateur'] = "set vertical list|chir|anesth|".implode('|', CPersonnel::$_types)."|chir_interv";
    $props['lock_view']       = 'bool default|0';
    $props['use_validate_2']  = 'bool default|0';
    $props['description']     = 'text';
    $props['decision_go']     = 'bool default|0';
    $props['alert_child']     = 'bool default|0';

    $props['_object_guid']    = 'str';
    $props['_links']          = 'str';
    $props['_duplicate']      = 'bool default|0';
    return $props;
  }

  /**
   * @inheritdoc
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
  function loadRefGroup(){
    return $this->_ref_group = $this->loadFwdRef("group_id", true);
  }

  /**
   * Load categories
   *
   * @return CDailyCheckItemCategory[]
   */
  function loadRefsCategories(){
    return $this->_ref_categories = $this->loadBackRefs("daily_check_list_categories", "`index`, title");
  }

  /**
   * @inheritdoc
   */
  function store(){
    if ($this->_duplicate == 1) {
      $this->duplicate();
      return;
    }

    if ($msg = parent::store()) {
      return $msg;
    }

    if ($this->_links) {
      $current_links = $this->loadRefTypeLinks();

      // Suppression des liens ayant un object class pas cohérent ou dont l'ID n'est pas dans la liste
      foreach ($current_links as $_link_object) {
        if (
          ((in_array($this->type, array("ouverture_salle", "fermeture_salle")) && $_link_object->object_class != "CSalle") ||
          (in_array($this->type, array("ouverture_sspi", "fermeture_sspi", "ouverture_preop", "fermeture_preop")) && !in_array($_link_object->object_class, array("CBlocOperatoire", "CSSPI")))
          )
          || !in_array("$_link_object->object_class-$_link_object->object_id", $this->_links)
        ) {
          $_link_object->delete();
        }
      }

      // Creation des liens manquants
      foreach ($this->_links[$this->type] as $_object_guid) {
        list($_object_class, $_object_id) = explode("-", $_object_guid);
        // Exclude types from other class
        if (($this->type == "ouverture_salle" && $_object_class != "CSalle") ||
          (in_array($this->type, array("ouverture_sspi", "fermeture_sspi", "ouverture_preop", "fermeture_preop")) && !in_array($_object_class, array("CBlocOperatoire", "CSSPI")))
        || $this->type == "intervention") {
          continue;
        }

        $new_link = new CDailyCheckListTypeLink();
        $new_link->object_class = $_object_class;
        $new_link->object_id    = ($_object_id == "none" ? "" : $_object_id); // "" is important here !
        $new_link->list_type_id = $this->_id;
        $new_link->loadMatchingObject(); // Should never match
        $new_link->store();
      }
    }

    return null;
  }

  /**
   * Load type links
   *
   * @return CDailyCheckListTypeLink[]
   */
  function loadRefTypeLinks(){
    return $this->_ref_type_links = $this->loadBackRefs("daily_check_list_type_links");
  }

  /**
   * Make an array of links
   *
   * @return array
   */
  function makeLinksArray(){
    $this->_links = array();

    $type_links = $this->loadRefTypeLinks();
    foreach ($type_links as $_link) {
      $_guid = $_link->loadRefObject()->_guid;
      $this->_links[$_guid] = $_guid;
    }

    return $this->_links;
  }

  /**
   * Get list types tree
   *
   * @return array
   */
  static function getListTypesTree(){
    $object = new self();
    $group_id = CGroups::loadCurrent()->_id;

    $targets = array();
    $by_type = array();

    foreach ($object->_specs["type"]->_locales as $type => $trad) {
      if ($type != "intervention") {
        /** @var CSalle|CBlocOperatoire $_object */
        $_object = ($type == "ouverture_salle" || $type == "fermeture_salle") ?  new CSalle() : new CBlocOperatoire();
        $_targets = $_object->loadGroupList();
        array_unshift($_targets, $_object);

        $targets[$type] = array_combine(CMbArray::pluck($_targets, "_id"), $_targets);

        $where = array(
          "type"    => "= '$type'",
          "group_id"=> "= '$group_id'",
          "check_list_group_id"=> " IS NULL",
        );

        $by_type[$type] = $object->loadList($where, "title");
        if (in_array($type, array("ouverture_sspi", "fermeture_sspi", "ouverture_preop", "fermeture_preop"))) {
          $sspi = new CSSPI();
          $sspis = $sspi->loadGroupList();
          if (count($sspis)) {
            $targets[$type] += array_combine(CMbArray::pluck($sspis, "_guid"), $sspis);
          }
        }
      }
    }

    return array($targets, $by_type);
  }

  /**
   * Duplicate CheckList
   *
   * @return void
   */
  function duplicate(){
    $checklist = new CDailyCheckListType();
    $checklist->type          = $this->type;
    $checklist->group_id      = $this->group_id;
    $checklist->title         = $this->title." dupliqué";
    $checklist->type_validateur = $this->type_validateur;
    $checklist->description   = $this->description;

    if ($msg = $checklist->store()) {
      return $msg;
    }

    foreach ($this->loadRefTypeLinks() as $link) {
      $_link = $link;
      $_link->_id  = "";
      $_link->list_type_id  = $checklist->_id;
      if ($msg = $_link->store()) {
        return $msg;
      }
    }

    foreach ($this->loadRefsCategories() as $categorie) {
      $items = $categorie->loadRefItemTypes();
      $new_categorie = $categorie;
      $new_categorie->_id  = "";
      $new_categorie->list_type_id  = $checklist->_id;
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
}
