<?php

/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSQLDataSource;
use Ox\Core\Mutex\CMbMutex;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Bloc\CSSPI;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CPoseDispositifVasculaire;
use Ox\Mediboard\System\CUserLog;

/**
 * Daily Check List, can also be non-daily !
 */
class CDailyCheckList extends CMbObject { // not a MetaObject, as there can be multiple objects for different dates
  public $daily_check_list_id;

  // DB Fields
  public $date;
  public $object_class;
  public $object_id;
  public $type;
  public $comments;
  public $validator_id;
  public $date_validate;
  public $validator2_id;
  public $date_validate2;
  public $com_validate2;
  public $list_type_id;
  public $group_id;
  public $decision_go;
  public $result_nogo;
  public $code_red;

  /** @var CMediusers */
  public $_ref_validator;
  public $_ref_validator2;

  /** @var CSalle|CBlocOperatoire|COperation|CPoseDispositifVasculaire */
  public $_ref_object;

  /** @var CDailyCheckItemType[] */
  public $_ref_item_types;

  /** @var CDailyCheckListType */
  public $_ref_list_type;

  public $_items;
  public $_validator_password;
  public $_readonly;
  public $_date_min;
  public $_date_max;
  public $_type;
  public $_signature;

  static $types = array(
    // Secu patient
    "preanesth" => "normal",
    "preop"     => "normal",
    "postop"    => "normal",

    // Endoscopie digestive
    "preendoscopie"  => "endoscopie",
    "postendoscopie" => "endoscopie",

    // Endoscopie bronchique
    "preendoscopie_bronchique"  => "endoscopie-bronchique",
    "postendoscopie_bronchique" => "endoscopie-bronchique",

    // Radiologie interventionnelle
    "preanesth_radio" => "radio",
    "preop_radio"     => "radio",
    "postop_radio"    => "radio",

    // Pose dispositif vasculaire
    "disp_vasc_avant"   => "disp-vasc",
    "disp_vasc_pendant" => "disp-vasc",
    "disp_vasc_apres"   => "disp-vasc",

    // Césarienne
    "avant_indu_cesar" => "cesarienne",
    "cesarienne_avant" => "cesarienne",
    "cesarienne_apres" => "cesarienne",

    // Bloc opératoire suisse
    "preanesth_ch"  => "normal_ch",
    "preop_ch"      => "normal_ch",
    "postop_ch"     => "normal_ch",

    // Secu patient version 2018
    "preanesth_2016" => "normal_2018",
    "preop_2016"     => "normal_2018",
    "postop_2016"    => "normal_2018",
  );

  static $_last_types = array(
    "postop", "postendoscopie", "postendoscopie_bronchique",  "postop_radio",
    "disp_vasc_apres",  "cesarienne_apres", "postop_ch",  "postop_2016"
  );

  static $_HAS_classes = array(
    "COperation",
    "CPoseDispositifVasculaire",
  );

  static $_HAS_lists = array(
    1 => array(
      "normal_2018"           => "Au bloc opératoire (v. 2018)",
      "endoscopie"            => "En endoscopie digestive (v. 2013-01)",
      "endoscopie-bronchique" => "En endoscopie bronchique (v. 2013)",
      "radio"                 => "En radiologie interv. (v. 2011-01)",
      "cesarienne"            => "En césarienne (v. 2014-01)",
    ),
    2 => array(
      "normal_ch"             => "Au bloc opératoire (v. 2014-08)",
      "endoscopie"            => "En endoscopie digestive (v. 2013-01)",
    ),
  );

  static $_HAS_comments_other = array(
    "endoscopie",
    "endoscopie-bronchique"
  );

  /**
   * Get non-HAS classes
   *
   * @param bool $operation see operations
   *
   * @return array
   */
  static function getNonHASClasses($operation = false){
    static $check_list = null;
    if ($check_list === null) {
      $check_list = new self;
    }

    $target_classes = array_keys($check_list->_specs["object_class"]->_locales);
    $target_classes = array_diff($target_classes, CDailyCheckList::$_HAS_classes);
    if ($operation) {
      $target_classes[] = "COperation";
    }

    return $target_classes;
  }

  /**
   * Get types by values
   *
   * @return array
   */
  static function getTypeByValues() {
    $list = array();
    foreach (CDailyCheckList::$types as $name => $type) {
      if ($type != "disp-vasc") {
        $list[$type][] = $name;
      }
    }
    return $list;
  }

  /**
   * Get the lists related to an object
   *
   * @param CMbObject $object Object to get the check lists of
   * @param string    $date   The reference date
   * @param string    $type   type de checklist
   * @param bool      $multi  ouverture en modale de la checklist
   *
   * @return array
   */
  static function getCheckLists(CMbObject $object, $date, $type = "ouverture_salle", $multi = false) {
    $daily_check_list_type = new CDailyCheckListType();
    $where = array(
      "daily_check_list_type_link.object_class" => "= '$object->_class'",
      "daily_check_list_type_link.object_id IS NULL
      OR
     daily_check_list_type_link.object_id = '$object->_id'",
    );
    $where["type"] = " = '$type'";
    $ljoin = array(
      "daily_check_list_type_link" => "daily_check_list_type_link.list_type_id = daily_check_list_type.daily_check_list_type_id",
    );
    /** @var CDailyCheckListType[] $daily_check_list_types  */
    $daily_check_list_types = $daily_check_list_type->loadGroupList($where, "title", null, "daily_check_list_type_id", $ljoin);

    /** @var CDailyCheckList[] $daily_check_lists  */
    $daily_check_lists = array();

    $check_list_not_validated = 0;

    $currUser = CMediusers::get();
    $currUser->isPraticien();
    $choose_moment_edit = CAppUI::gconf("dPsalleOp CDailyCheckList choose_moment_edit") &&
                          ($type == "ouverture_sspi" || $type == "ouverture_preop");
    foreach ($daily_check_list_types as $_list_type) {
      $_list_type->loadRefsCategories();
      $daily_check_list = CDailyCheckList::getList($object, $date, null, $_list_type->_id);
      $daily_check_list->loadItemTypes();
      $daily_check_list->loadBackRefs('items');
      $list_type = $daily_check_list->loadRefListType();

      if ((!$daily_check_list->_id || !$daily_check_list->validator_id ||
          ($list_type->use_validate_2 && !$daily_check_list->validator2_id))
        && ($choose_moment_edit || $daily_check_list->_ref_list_type->lock_view || !$currUser->_is_praticien || $multi)) {
        $check_list_not_validated++;
      }
      if ($choose_moment_edit || $daily_check_list->_ref_list_type->lock_view || !$currUser->_is_praticien || $multi) {
        $daily_check_lists[] = $daily_check_list;
      }
    }

    return array(
      $check_list_not_validated,
      $daily_check_list_types,
      $daily_check_lists,
    );
  }

  /**
   * Get the lists related to an 2 objects
   *
   * @param CMbObject $object  Object to get the check lists of
   * @param string    $date    The reference date
   * @param string    $type    Type de checklist
   * @param bool      $multi   Ouverture en modale de la checklist
   * @param int       $sspi_id Identifiant SSPI
   *
   * @return array
   */
  static function getsCheckLists(CMbObject $object, $date, $type = 'ouverture_salle', $multi = false, $sspi_id = null) {
    //Chargement pour l'objet normal
    [
      $check_list_not_validated,
      $daily_check_list_types,
      $daily_check_lists
      ] = CDailyCheckList::getCheckLists($object, $date, $type, $multi);

    if (in_array($type, array("ouverture_sspi", "fermeture_sspi", "ouverture_preop", "fermeture_preop")) && $sspi_id) {
      $sspi = CSSPI::findOrNew($sspi_id);
      if ($sspi->_id) {
        //Chargement pour la SSPI
        [$not_validated_sspi, $list_types_sspi, $lists_sspi] = CDailyCheckList::getCheckLists($sspi, $date, $type, $multi);
        //Fusion des variables
        $check_list_not_validated = $check_list_not_validated || $not_validated_sspi;
        foreach ($list_types_sspi as $_list_type_sspi) {
          $daily_check_list_types[$_list_type_sspi->_id] = $_list_type_sspi;
        }
        foreach ($lists_sspi as $_list_sspi) {
          $daily_check_lists[] = $_list_sspi;
        }
      }
    }

    return array(
      $check_list_not_validated,
      $daily_check_list_types,
      $daily_check_lists,
    );
  }

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'daily_check_list';
    $spec->key   = 'daily_check_list_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props['date']         = 'date notNull';
    $props['object_class'] = 'enum list|CSalle|CBlocOperatoire|COperation|CPoseDispositifVasculaire|CSSPI notNull default|CSalle';
    $props['object_id']    = 'ref class|CMbObject meta|object_class notNull autocomplete back|check_lists';
    $props['list_type_id'] = 'ref class|CDailyCheckListType back|daily_check_lists';
    $props['type']         = 'enum list|'.implode('|', array_keys(CDailyCheckList::$types));
    $props['validator_id'] = 'ref class|CMediusers back|checked_lists';
    $props['date_validate']= 'dateTime';
    $props['validator2_id']= 'ref class|CMediusers back|checked_lists_2';
    $props['date_validate2']= 'dateTime';
    $props['com_validate2']= 'text';
    $props['group_id']     = 'ref class|CGroups back|daily_check_lists';
    $props['comments']     = 'text helped';
    $props['decision_go']  = 'enum list|go|nogo';
    $props['result_nogo']  = 'enum list|retard|annulation';
    $props['_validator_password'] = 'password notNull';
    $props['_date_min']    = 'date';
    $props['_date_max']    = 'date';
    $props['_type']        = 'enum list|ouverture_salle|ouverture_sspi|ouverture_preop|fermeture_salle|fermeture_sspi|fermeture_preop';
    $props['_signature']   = 'bool default|0';
    $props['code_red']     = 'bool default|0';
    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->loadRefsFwd();
    $this->_view = "$this->_ref_object le $this->date ($this->_ref_validator)";
  }

  /**
   * Tells if the check list is readonly (signed or not)
   *
   * @return bool
   */
  function isReadonly() {
    $this->completeField("validator_id", "date");
    return $this->_readonly = ($this->_id && $this->validator_id && $this->date);
  }

  /**
   * Get validator
   *
   * @return CMediusers
   */
  function loadRefValidator(){
    $this->_ref_validator = $this->loadFwdRef("validator_id", true);
    $this->_ref_validator->loadRefFunction();
    return $this->_ref_validator;
  }
  /**
   * Get validator
   *
   * @return CMediusers
   */
  function loadRefValidator2(){
    $this->_ref_validator2 = $this->loadFwdRef("validator2_id", true);
    $this->_ref_validator2->loadRefFunction();
    return $this->_ref_validator2;
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    if ($this->object_class) {
      $this->_ref_object = $this->loadFwdRef("object_id", true);
      if ($this->object_class == "COperation") {
        $this->_ref_object->loadRefSejour();
      }
    }

    $this->loadRefValidator();
  }

  /**
   * @see parent::check()
   */
  function check() {
    $msg = null;
    $this->completeField("object_class", "date", "validator_id", "list_type_id");
    $this->loadRefListType();
    //Ne pas permettre la validation de la checklist de fermeture de salle si un patient s'y trouve
    if ($this->object_class == "CSalle" && $this->list_type_id && $this->validator_id && !$this->_old->validator_id
        && $this->_ref_list_type->type == "fermeture_salle"
        && CAppUI::gconf("dPsalleOp COperation no_entree_fermeture_salle_in_plage")
    ) {
      $ljoin = array();
      $ljoin["plagesop"] = "plagesop.plageop_id = operations.plageop_id";
      $where = array();
      $where["operations.date"]    = " = '$this->date'";
      $where["operations.annulee"] = " = '0'";
      $where[] = "operations.entree_salle IS NOT NULL";
      $where[] = "operations.sortie_salle IS NULL";
      $where["plagesop.salle_id"]  = " = '$this->object_id'";
      $operation = new COperation();
      $result = $operation->countList($where, "operations.operation_id", $ljoin);
      if ($result) {
        $msg .= CAppUI::tr("CDailyCheckList.no_fermeture_salle_in_plage");
      }
    }
    return $msg . parent::check();
  }

  /**
   * @see parent::store()
   */
  function store() {
    $this->completeField("date", "object_class", "object_id", "type", "list_type_id");
    if (!$this->_id) {
      $checklist_exist = new self;
      $checklist_exist->date         = $this->date;
      $checklist_exist->object_class = $this->object_class;
      $checklist_exist->object_id    = $this->object_id;
      if ($this->type) {
        $checklist_exist->type         = $this->type;
      }
      if ($this->list_type_id) {
        $checklist_exist->list_type_id         = $this->list_type_id;
      }
      $checklist_exist->loadMatchingObject();
      if ($checklist_exist->_id) {
        $this->_id = $checklist_exist->_id;
        $this->daily_check_list_id = $checklist_exist->_id;
      }
    }

    if ($this->validator_id) {
      // Verification du mot de passe
      $user_curant_validator = CMediusers::get()->_id == $this->validator_id;
      if (!$user_curant_validator && $this->_validator_password) {
        $this->loadRefsFwd();
        if (!CUser::checkPassword($this->_ref_validator->_user_username, $this->_validator_password)) {
          $this->validator_id = "";
          return 'Le mot de passe entré n\'est pas correct';
        }
      }

      // Validator_id passé mais il ne faut pas l'enregistrer
      /** @var self $old */
      $old = $this->loadOldObject();
      if (!$this->_validator_password && !$old->validator_id && (!$user_curant_validator || !$this->_signature)) {
        $this->validator_id = "";
      }
    }

    if ($this->validator_id && ($this->fieldModified("validator_id") || !$this->_id) && !$this->date_validate) {
      if ($this->type && $this->object_class == "COperation") {
        $this->loadRefsFwd();
        if (!$this->_ref_object->_ref_sejour->entree_reelle) {
          return CAppUI::tr("CDailyCheckList-error-entree_reelle-sejour");
        }
      }
      $this->date_validate = CMbDT::dateTime();
    }
    if ($this->validator2_id) {
      $user_curant_validator2 = CMediusers::get()->_id == $this->validator2_id;
      // Verification du mot de passe
      if (!$user_curant_validator2 && $this->_validator_password) {
        $this->loadRefValidator2();
        if (!CUser::checkPassword($this->_ref_validator2->_user_username, $this->_validator_password)) {
          $this->validator2_id = "";
          return 'Le mot de passe entré n\'est pas correct';
        }
      }

      // Validator_id passé mais il ne faut pas l'enregistrer
      /** @var self $old */
      $old = $this->loadOldObject();
      if (!$this->_validator_password && !$old->validator2_id && (!$user_curant_validator2 || !$this->_signature)) {
        $this->validator2_id = "";
      }
      if ($this->validator2_id && ($this->fieldModified("validator2_id") || !$this->_id) && !$this->date_validate2) {
        $this->date_validate2 = CMbDT::dateTime();
      }
    }

    if ($msg = parent::store()) {
      return $msg;
    }

    // Sauvegarde des items cochés
    $items = $this->_items ? $this->_items : array();

    $types = $this->loadItemTypes();
    if (!empty($items)) {
      $mutex = new CMbMutex($this->_guid);
      $mutex->acquire();
      foreach ($types as $type) {
        $check_item = new CDailyCheckItem();
        $check_item->list_id      = $this->_id;
        $check_item->item_type_id = $type->_id;
        $check_item->loadMatchingObject();
        $check_item->checked = (isset($items[$type->_id]) ? $items[$type->_id] : "");
        $check_item->commentaire = (isset($items[$type->_id."_commentaire"]) ? $items[$type->_id."_commentaire"] : null);
        $check_item->store(); // Don't return if the item was not present
      }
      $mutex->release();
    }

    return null;
  }

  /**
   * Finds a list corresponding to a few params
   *
   * @param CMbObject $object       The host object
   * @param string    $date         The date
   * @param string    $type         The type of list
   * @param int       $list_type_id List type ID
   * @param boolean   $with_refs    If true, load references for list
   *
   * @return self|self[]
   */
  static function getList(CMbObject $object, $date = null, $type = null, $list_type_id = null, $with_refs = true) {
    $list = new self;
    $list->object_class = $object->_class;
    $list->object_id = $object->_id;
    $list->list_type_id = $list_type_id;
    $list->date = $date;
    $list->type = $type;
    $list->loadMatchingObject();
    $list->_ref_object = $object;
    if ($with_refs) {
      $list->loadRefListType()->loadRefsCategories();
    }
    $list->isReadonly();
    return $list;
  }

  /**
   * Get list type
   *
   * @return CDailyCheckListType
   */
  function loadRefListType() {
    return $this->_ref_list_type = $this->loadFwdRef("list_type_id", true);
  }

  /**
   * Get the list of rooms to check
   *
   * @return CSalle[][]|CBlocOperatoire[][]
   */
  static function getRooms() {
    $list_rooms = array(
      "CSalle"          => array(),
      "CBlocOperatoire" => array(),
      "CSSPI"           => array(),
    );

    foreach ($list_rooms as $class => &$list) {
      /** @var CSalle|CBlocOperatoire $room */
      $room = new $class;
      $list = $room->loadGroupList();

      /** @var CSalle|CBlocOperatoire $empty */
      $empty = new $class;
      $empty->updateFormFields();
      array_unshift($list, $empty);
    }

    return $list_rooms;
  }

  /**
   * Get item types
   *
   * @param bool $is_checklist_group Checklist group
   *
   * @return CDailyCheckItemType[]
   */
  function loadItemTypes(bool $is_checklist_group = false) {
    $ds = $this->getDS();

    $where = array(
      "active" => "= '1'",
    );
    if ($this->type) {
      $where['daily_check_item_category.type'] = $ds->prepare("= %", $this->type);
    }
      if ($this->list_type_id) {
          $where["list_type_id"] = $ds->prepare("= %", $this->list_type_id);
      } else {
          $where["list_type_id"] = $ds->prepare("IS NULL");
      }
    $ljoin = array(
      'daily_check_item_category' => 'daily_check_item_category.daily_check_item_category_id = daily_check_item_type.category_id'
    );

    if ($this->list_type_id) {
      $where["daily_check_item_category.list_type_id"] = $ds->prepare("= %", $this->list_type_id);
    }
    else {
      $where["daily_check_item_category.target_class"] = $ds->prepare("= %", $this->object_class);
      $where[] = $ds->prepare("daily_check_item_category.target_id IS NULL OR daily_check_item_category.target_id = %", $this->object_id);
    }

    $orderby = 'daily_check_item_category.`index`, daily_check_item_category.title, ';

    if ($is_checklist_group) {
        $orderby .= "daily_check_item_type.`index`, ";
    }

    // Si liste des points de la HAS
    if (in_array($this->object_class, self::$_HAS_classes)) {
      $orderby .= "daily_check_item_type_id";
    }
    else {
      $orderby .= "`index`, title";
    }

    $itemType = new CDailyCheckItemType();

    $this->_ref_item_types = $itemType->loadGroupList($where, $orderby, null, null, $ljoin);
    foreach ($this->_ref_item_types as $type) {
      $type->loadRefsFwd();
    }

    /** @var CDailyCheckItem[] $items */
    $items = $this->loadBackRefs('items');

    if ($items) {
      foreach ($items as $item) {
        if (isset($this->_ref_item_types[$item->item_type_id])) {
          $this->_ref_item_types[$item->item_type_id]->_checked = $item->checked;
          $this->_ref_item_types[$item->item_type_id]->_commentaire = $item->commentaire;
          $this->_ref_item_types[$item->item_type_id]->_answer = $item->getAnswer();
        }
      }
    }

    return $this->_ref_item_types;
  }

  /**
   * Get date last checklist for a type
   *
   * @param CMbObject $object               Object to get the check lists of
   * @param string    $type                 Type de checklist
   * @param bool      $validated
   * @param bool      $validation_only_past Validation only past
   *
   * @return string
   */
  static function getDateLastChecklist(CMbObject $object, $type, $validated = false, $validation_only_past = false) {
    $date_last_checklist = null;

    $ljoin = array();
    $ljoin["daily_check_list_type"] = "daily_check_list_type.daily_check_list_type_id = daily_check_list.list_type_id";

    $where = array();
    $where["daily_check_list.object_class"] = " = '$object->_class'";
    $where["daily_check_list.object_id"]    = " = '$object->_id'";
    $where["daily_check_list_type.type"]= " = '$type'";
    if ($validated) {
      $where[]= "(daily_check_list_type.use_validate_2 = '0' AND daily_check_list.date_validate IS NOT NULL)
      OR (daily_check_list_type.use_validate_2 = '1' AND daily_check_list.date_validate2 IS NOT NULL)";
      if ($validation_only_past) {
        $where[]= "(daily_check_list_type.use_validate_2 = '0' AND daily_check_list.date_validate <= '".CMbDT::date()." 00:00:00')
      OR (daily_check_list_type.use_validate_2 = '1' AND daily_check_list.date_validate2 <= '".CMbDT::date()." 00:00:00')";
      }
    }

    $checklist = new self;
    $checklist->loadObject($where, "date DESC, daily_check_list_id DESC", null, $ljoin);

    if ($checklist->_id) {
      if ($checklist->list_type_id && $checklist->loadRefListType()->use_validate_2 && $checklist->date_validate2) {
        $date_last_checklist = $checklist->date_validate2;
      }
      elseif ($checklist->date_validate) {
        $date_last_checklist = $checklist->date_validate;
      }
      else {
        $log = new CUserLog();
        $log->object_id     = $checklist->_id;
        $log->object_class  = $checklist->_class;
        $log->loadMatchingObject("date DESC", "user_log_id");
        $date_last_checklist = $log->date;
      }
    }
    if (!$checklist->_id || !$date_last_checklist) {
      $date_last_checklist = $checklist->date;
    }

    return $date_last_checklist;
  }

  /**
   * Get count checklist for operation
   *
   * @param CMbObject $object Object to get the check lists of
   *
   * @return string
   */
    public static function getCountChecklistInterv(CMbObject $object): int
    {
        $ds = CSQLDataSource::get("std");
        $where = [
            "object_class" => $ds->prepare("= ?", $object->_class),
            "object_id"    => $ds->prepare("= ?", $object->_id),
            "validator_id IS NOT NULL",
        ];
        /** @var CDailyCheckListType[] $daily_check_list_types */
        $daily_check_list = new CDailyCheckList();

        return intval($daily_check_list->countList($where));
    }

    /**
   * Check is item can be a code red
   *
   * @param string $object_class Object class
   * @param string $type         Type checklist
   * @param int    $iterator     Iterator
   *
   * @return boolean
   */
  static function itemCanRedCode($object_class, $type, $iterator) {
    $red_code = false;
    if (in_array($object_class, CDailyCheckList::$_HAS_classes) &&
      (($type == "avant_indu_cesar" && in_array($iterator, array(0, 5))) ||
        ($type == "cesarienne_avant" && $iterator == 3) ||
        $type == "cesarienne_apres")) {
      $red_code = true;
    }
    return $red_code;
  }

    /**
     * Récupère le nombre minimum de checklist signées requises selon le type
     *
     * @param CMbObject $object
     *
     * @return int
     * @throws \Exception
     */
    public static function countNumberCheckListForType(?string $type): int
    {
        switch ($type) {
            case "preendoscopie":
            case "postendoscopie":
            case "preendoscopie_bronchique":
            case "postendoscopie_bronchique":
                return 2;
            default:
                return 3;
        }
    }
}
