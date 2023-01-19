<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

/**
 * Class CInfoGroup
 */
class CInfoGroup extends CMbObject {
  public $info_group_id;

  // DB Fields
  public $group_id;
  public $actif;
  public $date;
  public $description;
  public $user_id;
  public $patient_id;
  public $type_id;
  public $service_id;

  // Counts
  public $_count_inactive;

  /** @var CGroups */
  public $_ref_group;

  /** @var CService */
  public $_ref_service;

  /** @var CMediusers */
  public $_ref_user;

  /** @var CPatient */
  public $_ref_patient;

  /** @var CInfoType */
  public $_ref_type;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'info_group';
    $spec->key   = 'info_group_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                = parent::getProps();
    $props["group_id"]    = "ref class|CGroups back|infos_group";
    $props["actif"]       = "bool default|0";
    $props['date']        = "dateTime";
    $props["description"] = "text helped seekable markdown";
    $props['user_id']     = "ref class|CMediusers back|infos_group";
    $props['patient_id']  = 'ref class|CPatient back|infos_group';
    $props['type_id']     = 'ref class|CInfoType autocomplete|name dependsOn|user_id back|infos_group';
    $props['service_id']  = 'ref class|CService back|info_services';

    return $props;
  }

  /**
   * Compte les informations inactives
   *
   * @return int
   */
  function countInactiveItems() {
    $group = CGroups::loadCurrent();
    $user  = CMediusers::get();
    $where = array('actif' => " = '0'");

    $info_group = new CInfoGroup();
    if (CAppUI::conf('dPhospi CInfoGroup split_by_users', $group)) {
      $where['type_id'] = ' IS NULL';

      if (!$user->isAdmin()) {
        $where['user_id'] = " = $user->_id";
      }
    }
    else {
      $where['group_id'] = " = $group->_id";
    }

    return $this->_count_inactive = $info_group->countList($where);
  }

  /**
   * @see  parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->description;
  }

  /**
   * Load group forward reference
   *
   * @return CGroups
   */
  function loadRefGroup() {
    return $this->_ref_group = $this->loadFwdRef("group_id", true);
  }

  /**
   * Load service forward reference
   *
   * @return CService
   */
  function loadRefService() {
    return $this->_ref_service = $this->loadFwdRef("service_id", true);
  }

  /**
   * Charge l'utilisateur qui a créé la tâche
   *
   * @return CMediusers
   */
  function loadRefUser() {
    return $this->_ref_user = $this->loadFwdRef("user_id", true);
  }

  /**
   * Load the linked patient
   *
   * @return CPatient
   */
  public function loadRefPatient() {
    return $this->_ref_patient = $this->loadFwdRef('patient_id', true);
  }

  /**
   * Load the linked type
   *
   * @return CInfoType
   */
  public function loadRefType() {
    return $this->_ref_type = $this->loadFwdRef('type_id', true);
  }

  /**
   * @see parent::store()
   */
  function store() {
    if (!$this->_id) {
      $this->user_id  = CMediusers::get()->_id;
      $this->group_id = CGroups::loadCurrent()->_id;
      $this->date     = CMbDT::dateTime();
    }

    // Standard storage
    if ($msg = parent::store()) {
      return $msg;
    }

    return null;
  }

  /**
   * Load the CInfoGroup for the given user (the user connected by default) depending on the config dPhospi split_by_user
   *
   * @param CMediusers $user     The user (by default the connected user)
   * @param boolean    $inactive If true, the inactive infos will be loaded
   *
   * @return CInfoGroup[]
   */
  public static function loadFor($user = null, $inactive = false) {
    $group = CGroups::loadCurrent();

    if (CAppUI::conf('dPhospi CInfoGroup split_by_users', $group)) {
      $infos = self::loadForUser($user, $inactive);
    }
    else {
      $infos = self::loadForGroup($group, $inactive);
    }

    return $infos;
  }

  /**
   * Load the infos for the given group
   *
   * @param CGroups $group    The group (the current group by default)
   * @param bool    $inactive If true, the inactive infos will be loaded
   *
   * @return CInfoGroup[]
   */
  public static function loadForGroup($group = null, $inactive = false) {
    if (!$group) {
      $group = CGroups::loadCurrent();
    }

    $where = array(
      "group_id"   => "='$group->_id'",
      "service_id" => "IS NULL",
    );
    if (!$inactive) {
      $where["actif"] = "= '1'";
    }

    /** @var CInfoGroup[] $infos */
    $info  = new self;
    $infos = $info->loadList($where, 'date DESC');

    foreach ($infos as $_info) {
      $_info->loadRefUser();
      $_info->loadRefPatient();
    }

    return $infos;
  }


  /**
   * Load the infos for the given user
   *
   * @param CMediusers $user     The user (the current user by default)
   * @param bool       $inactive If true, the inactive infos will be loaded
   *
   * @return CInfoGroup[]
   */
  public static function loadForUser($user = null, $inactive = false) {
    if (!$user) {
      $user = CMediusers::get();
    }

    $where = array();
    if (!$inactive) {
      $where['actif'] = " = '1'";
    }

    $type = new CInfoType();
    if (!$user->isAdmin()) {
      $type->user_id = $user->_id;
    }
    /** @var CInfoType[] $types */
    $types = $type->loadMatchingList('name', null, 'info_type_id');
    $infos = CMbObject::massLoadBackRefs($types, 'infos_group', 'date DESC', $where);
    CMbObject::massLoadFwdRef($infos, 'patient_id');

    $infos_by_type = array();
    $info_where    = array("service_id" => "IS NULL");
    foreach ($types as $type) {
      $type->loadRefUser();
      $type->countInfos($info_where);
      $infos = $type->loadRefInfos($inactive, $info_where);

      foreach ($infos as $info) {
        $info->loadRefUser();
        $info->loadRefPatient();
      }

      if (!$user->isAdmin() || ($user->isAdmin() && count($infos))) {
        $infos_by_type[$type->_id] = array('type' => $type, 'infos' => $infos);
      }
    }

    $info = new CInfoGroup();
    if (!$user->isAdmin()) {
      $where['user_id'] = " = $user->_id";
    }
    $where['type_id']    = ' IS NULL';
    $where['service_id'] = ' IS NULL';
    $infos               = $info->loadList($where, 'date DESC', null, 'info_group_id');

    foreach ($infos as $info) {
      $info->loadRefPatient();
      $info->loadRefUser();
    }

    $infos_by_type[''] = array('type' => new CinfoType(), 'infos' => $infos);

    return $infos_by_type;
  }
}
