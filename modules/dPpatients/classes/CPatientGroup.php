<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Link between patient and group, plus some metadata
 */
class CPatientGroup extends CMbObject {
  /** @var integer Primary key */
  public $patient_group_id;

  /** @var integer CGroups ID */
  public $group_id;

  /** @var integer CPatient ID */
  public $patient_id;

  /** @var bool Did the patient allowed personal data sharing? */
  public $share;

  /** @var string Last modification date and time */
  public $last_modification;

  /** @var integer CMediusers ID */
  public $user_id;

  /** @var CGroups Group reference */
  public $_ref_group;

  /** @var CPatient Patient reference */
  public $_ref_patient;

  /** @var CMediusers Owner reference */
  public $_ref_user;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();

    $spec->table                    = "patient_group";
    $spec->key                      = "patient_group_id";
    $spec->uniques["patient_group"] = array('patient_id', 'group_id');

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();

    $props['group_id']          = 'ref class|CGroups notNull cascade back|patient_group';
    $props['patient_id']        = 'ref class|CPatient notNull cascade back|patient_groups';
    $props['share']             = 'bool notNull default|0';
    $props['last_modification'] = 'dateTime notNull';
    $props['user_id']           = 'ref class|CMediusers notNull cascade back|authorisations_patient_etab';

    return $props;
  }

  /**
   * Gets concerned group
   *
   * @return CGroups
   */
  function loadRefGroup() {
    return $this->_ref_group = $this->loadFwdRef('group_id');
  }

  /**
   * Gets concerned patient
   *
   * @return CPatient
   */
  function loadRefPatient() {
    return $this->_ref_patient = $this->loadFwdRef('patient_id');
  }

  /**
   * Gets owner
   *
   * @return CMediusers
   */
  function loadRefUser() {
    return $this->_ref_user = $this->loadFwdRef('user_id');
  }

  /**
   * @see parent::store()
   */
  function store() {
    if (!$this->_id || $this->fieldModified('share')) {
      $this->last_modification = CMbDT::dateTime();
      $this->user_id           = CMediusers::get()->_id;
    }

    return parent::store();
  }
}
