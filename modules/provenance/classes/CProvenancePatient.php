<?php
/**
 * @package Mediboard\Provenance
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Provenance;

use Exception;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Patients\CPatient;

/**
 * Class CProvenancePatient
 *
 * @package Ox\Mediboard\Provenance
 */
class CProvenancePatient extends CMbObject {
  // Table Key
  public $provenance_patient_id;

  /** @var CPatient */
  public $_ref_patient;

  /** @var CProvenance */
  public $_ref_provenance;

  // Table Fields
  public $patient_id;
  public $provenance_id;
  public $commentaire;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();

    $spec->table             = 'provenance_patient';
    $spec->key               = 'provenance_patient_id';
    $spec->uniques["unique"] = ["patient_id", "provenance_id"];

    return $spec;
  }

  /**
   * @return array
   */
  function getProps() {
    $props                  = parent::getProps();
    $props["patient_id"]    = "ref notNull class|CPatient cascade back|provenance_patient";
    $props["provenance_id"] = "ref notNull class|CProvenance back|provenances_patient";
    $props["commentaire"]   = "str";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->loadRefPatient();
    $this->loadRefProvenance();
    $this->_view = $this->_ref_provenance->libelle;
    if ($this->commentaire) {
      $this->_view .= ' - ' . $this->commentaire;
    }
  }

  /**
   *  Charge le patient associé
   *
   * @return CStoredObject|null
   * @throws Exception
   */
  function loadRefPatient() {
    return $this->_ref_patient = $this->loadFwdRef('patient_id', true);
  }

  /**
   * Charge la provenance associée
   *
   * @return CStoredObject|null
   * @throws Exception
   */
  function loadRefProvenance() {
    return $this->_ref_provenance = $this->loadFwdRef('provenance_id', true);
  }
}