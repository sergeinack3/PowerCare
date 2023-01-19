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
 * Inclusion du patient aux programmes de la clinique
 */
class CInclusionProgramme extends CMbObject {
  // DB Table key
  public $inclusion_programme_id;

  // DB fields
  public $patient_id;
  public $programme_clinique_id;
  public $date_debut;
  public $date_fin;
  public $commentaire;

  /** @var CProgrammeClinique */
  public $_ref_programme_clinique;
  /** @var CPatient */
  public $_ref_patient;
  /** @var CInclusionProgrammeLine[] */
  public $_refs_inclusion_programme_lines;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'inclusion_programme';
    $spec->key   = 'inclusion_programme_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                          = parent::getProps();
    $props["patient_id"]            = "ref notNull class|CPatient back|inclusions_programme";
    $props["programme_clinique_id"] = "ref notNull class|CProgrammeClinique back|inclusions_programme";
    $props["date_debut"]            = "date";
    $props["date_fin"]              = "date";
    $props["commentaire"]           = "text";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
  }

  /**
   * Get the program
   *
   * @return CProgrammeClinique
   */
  function loadRefProgrammeClinique() {
    return $this->_ref_programme_clinique = $this->loadFwdRef("programme_clinique_id", true);
  }

  /**
   * Get the patient
   *
   * @return CPatient
   */
  function loadRefPatient() {
    return $this->_ref_patient = $this->loadFwdRef("patient_id", true);
  }

  /**
   * Get the prescription lines associated with a program
   *
   * @return CInclusionProgrammeLine[]
   */
  function loadRefsInclusionProgrammeLines() {
    return $this->_refs_inclusion_programme_lines = $this->loadBackRefs("programme_line", true);
  }

  /**
   * @see parent::store()
   */
  function store() {
    return parent::store();
  }
}
