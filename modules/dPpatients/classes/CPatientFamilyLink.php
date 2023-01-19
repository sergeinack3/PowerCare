<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\CMbObject;

/**
 * Link between patients in the same family
 */
class CPatientFamilyLink extends CMbObject {
  /** @var integer Primary key */
  public $patient_family_link_id;

  public $parent_id_1;
  public $parent_id_2;
  public $patient_id;
  public $type;

  /** @var CPatient */
  public $_ref_parent1;
  /** @var CPatient */
  public $_ref_parent2;
  /** @var CPatient */
  public $_ref_patient;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "patient_family_link";
    $spec->key   = "patient_family_link_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props["parent_id_1"] = "ref class|CPatient back|family_parent_1";
    $props["parent_id_2"] = "ref class|CPatient back|family_parent_2";
    $props["patient_id"]  = "ref class|CPatient notNull back|family_patient";
    $props["type"]        = "enum list|civil|biologique default|biologique";

    return $props;
  }

  /**
   * Load the parent 1
   *
   * @return CPatient|null
   * @throws \Exception
   */
  function loadRefParent1() {
    return $this->_ref_parent1 = $this->loadFwdRef("parent_id_1");
  }

  /**
   * Load the parent 2
   *
   * @return CPatient|null
   * @throws \Exception
   */
  function loadRefParent2() {
    return $this->_ref_parent2 = $this->loadFwdRef("parent_id_2");
  }

  /**
   * Load the patient
   *
   * @return CPatient|null
   * @throws \Exception
   */
  function loadRefPatient() {
    return $this->_ref_patient = $this->loadFwdRef("patient_id");
  }

  /**
   * @see parent::store()
   */
  function store() {
    if ($msg = parent::store()) {
      return $msg;
    }

    return null;
  }
}
