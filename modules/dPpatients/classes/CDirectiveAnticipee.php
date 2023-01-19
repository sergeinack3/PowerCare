<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Exception;
use Ox\Core\CMbObject;

/**
 * Informations relatives aux directives anticipées exprimées par le patient
 */
class CDirectiveAnticipee extends CMbObject {
  // DB Table key
  public $directive_anticipee_id;

  // DB fields
  public $patient_id;
  public $description;
  public $date_recueil;
  public $date_validite;
  public $detenteur_id;
  public $detenteur_class;

  /** @var CCorrespondant|CCorrespondantPatient|CPatient */
  public $_ref_detenteur;
  /** @var CPatient */
  public $_ref_patient;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'directive_anticipee';
    $spec->key   = 'directive_anticipee_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                    = parent::getProps();
    $props["patient_id"]      = "ref class|CPatient notNull back|directives_patient";
    $props["description"]     = "text helped";
    $props["date_recueil"]    = "date notNull";
    $props["date_validite"]   = "date moreEquals|date_recueil";
    $props["detenteur_id"]    = "ref class|CMbObject meta|detenteur_class notNull back|directives_detenteur";
    $props["detenteur_class"] = "enum list|CCorrespondant|CCorrespondantPatient|CPatient|CMedecin";

    return $props;
  }

  /**
   * Charge le patient
   *
   * @return CPatient
   */
  function loadRefPatient() {
    return $this->_ref_patient = $this->loadFwdRef("patient_id");
  }

  /**
   * Chargement le détenteur de directive anticipée
   *
   * @return CCorrespondant|CCorrespondantPatient|CPatient
   */
  function loadRefDetenteur() {
    $this->_ref_detenteur = $this->loadFwdRef("detenteur_id", true);

    return $this->_ref_detenteur;
  }

  /**
   * @return string|void|null
   * @throws Exception
   */
  public function store() {
    parent::store();

    // Just check if it's been stored
    if ($this->_id) {
      $this->loadRefPatient();
      $this->_ref_patient->directives_anticipees = 1;
      $this->_ref_patient->store();
    }
  }
}

