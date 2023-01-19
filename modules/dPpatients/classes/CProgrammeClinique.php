<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\CMbObject;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Programme de la clinique
 */
class CProgrammeClinique extends CMbObject {
  // DB Table key
  public $programme_clinique_id;

  // DB fields
  public $nom;
  public $coordinateur_id;
  public $description;
  public $annule;

  public $_nb_patients;
  public $_date_first_inclusion;
  public $_date_latest_inclusion;

  /** @var CPatient */
  public $_refs_patients;
  /** @var CMedecin */
  public $_ref_medecin;
  /** @var CInclusionProgramme */
  public $_refs_inclusions_programme;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'programme_clinique';
    $spec->key   = 'programme_clinique_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                    = parent::getProps();
    $props["nom"]             = "str notNull";
    $props["coordinateur_id"] = "ref notNull class|CMediusers back|programmes_clinique";
    $props["description"]     = "text";
    $props["annule"]          = "bool show|0";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function loadRefsInclusionsProgramme() {
    $this->_refs_inclusions_programme = $this->loadBackRefs('inclusions_programme', "date_debut ASC");
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->nom;
  }

  /**
   * Load patients
   *
   * @return CPatient
   */
  function loadRefsPatients() {
    return $this->_refs_patients = '';
  }

  /**
   * Load the praticien
   *
   * @return void
   */
  function loadRefMedecin() {
    return $this->_ref_medecin = $this->loadFwdRef("coordinateur_id");
  }

  /**
   * Count patient number
   *
   * @return int The patient count
   */
  function countPatients() {
    $where                                              = array();
    $ljoin                                              = array();
    $ljoin["programme_clinique"]                        = "programme_clinique.programme_clinique_id = inclusion_programme.programme_clinique_id";
    $where["inclusion_programme.programme_clinique_id"] = "= '$this->_id'";
    $where["programme_clinique.annule"]                 = "= '0'";

    $inclusion_programme = new CInclusionProgramme();

    return $this->_nb_patients = $inclusion_programme->countList($where, null, $ljoin);
  }

  /**
   * get first date inclusion and last date inclusion
   *
   */
  function getDateFirstLastInclusion() {
    $this->loadRefsInclusionsProgramme();
    $inclusions = $this->_refs_inclusions_programme;

    $inclusion_first = null;
    $inclusion_last  = null;

    // Get first inclusion
    foreach ($inclusions as $_inclusion) {
      if ($_inclusion->date_debut) {
        $inclusion_first = $_inclusion;
        break;
      }
    }

    // Get last inclusion
    $inclusions_reverse = array_reverse($inclusions);
    foreach ($inclusions_reverse as $_inclusion) {
      if ($_inclusion->date_debut) {
        $inclusion_last = $_inclusion;
        break;
      }
    }

    $first_date  = ($inclusion_first) ? $inclusion_first->date_debut : "";
    $latest_date = ($inclusion_last) ? $inclusion_last->date_fin : "";

    $this->_date_first_inclusion  = $first_date;
    $this->_date_latest_inclusion = $latest_date;
  }


  /**
   * @see parent::store()
   */
  function store() {
    return parent::store();
  }
}
