<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Define a type for a CInfoGroup
 */
class CInfoType extends CMbObject {
  /** @var integer Primary key */
  public $info_type_id;

  /** @var string The name of the type */
  public $name;

  /** @var integer The id of the owner */
  public $user_id;

  /** @var CMediusers The owner */
  public $_ref_user;

  /** @var CInfoGroup[] */
  public $_ref_infos;

  /** @var integer */
  public $_count_inactive_infos;
  /** @var integer */
  public $_count_active_infos;
  /** @var integer */
  public $_count_infos;

  /**
   * Initialize the class specifications
   *
   * @return CMbObjectSpec
   */
  public function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "info_types";
    $spec->key   = "info_type_id";

    return $spec;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  public function getProps() {
    $props = parent::getProps();

    $props['name']    = 'str notNull';
    $props['user_id'] = 'ref class|CMediusers notNull back|infos_types';

    return $props;
  }

  /**
   * @inheritdoc
   */
  public function sotre() {
    if (!$this->_id) {
      $this->user_id = CMediusers::get()->_id;
    }

    return parent::store();
  }

  /**
   * @inheritdoc
   */
  public function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->name;
  }

  /**
   * Load the user
   *
   * @return CMediusers
   */
  public function loadRefUser() {
    /** @var CMediusers */
    $this->_ref_user = $this->loadFwdRef('user_id', true);

    return $this->_ref_user;
  }

  /**
   * Load the infos for this type
   *
   * @param bool  $inactive If true, the inactive infos will be loaded
   * @param array $where    Optional conditions
   *
   * @return CInfoGroup[]
   */
  public function loadRefInfos($inactive = false, $where = array()) {
    if (!$inactive) {
      $where['actif'] = " = '1'";
    }

    /** @var CInfoGroup[] */
    $this->_ref_infos = $this->loadBackRefs('infos_group', 'date DESC', null, 'info_group_id', null, null, null, $where);

    return $this->_ref_infos;
  }

  /**
   * Count the inactive infos linked to this
   *
   * @param array $where Optional conditions
   *
   * @return void
   */
  public function countInfos($where = array()) {
    $this->_count_inactive_infos = $this->countBackRefs('infos_group', array_merge(array('actif' => " = '0'"), $where));
    $this->_count_active_infos   = $this->countBackRefs('infos_group', array_merge(array('actif' => " = '1'"), $where));
    $this->_count_infos          = $this->_count_inactive_infos + $this->_count_active_infos;
  }

  /**
   * Load the types for the given user (the connected user by default)
   *
   * @param CMediusers $user The user
   *
   * @return CInfoType []
   */
  public static function loadForUser($user = null) {
    if (!$user) {
      $user = CMediusers::get();
    }

    $type          = new self;
    $type->user_id = $user->_id;

    /** @var CInfoType[] $types */
    $types = $type->loadMatchingList('name', null, 'info_type_id');

    return $types;
  }
}
