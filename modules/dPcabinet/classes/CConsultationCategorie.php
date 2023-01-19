<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CExercicePlace;

/**
 * Catégorie de consultation, couleurs et icones pour mieux les classer
 */
class CConsultationCategorie extends CMbObject {
    /** @var string  */
    public const RESOURCE_TYPE = 'consultationCategory';

    /** @var string */
    public const FIELDSET_TARGET = 'target';

  public $categorie_id;

  // DB References
  public $function_id;
  public $praticien_id;

  // DB fields
  public $nom_categorie;
  public $nom_icone;
  public $duree;
  public $commentaire;
  public $seance;       // categorie à utiliser pour les séances
  public $max_seances;  // nombre de séance max
  public $anticipation; // seuil d'anticipation du déclenchement de l'alerte
  public $couleur;
  public $eligible_teleconsultation;
  public $exercice_place_id;

  // AppFine prise RDV
  public $sync_appfine;
  public $authorize_booking_new_patient;

  public $_meeting_order = [];
  public $_threshold_alert;
  public $_sync_appfine = false;

  // Collections
  /** @var CConsultation[] */
  public $_refs_consultations = [];
  public $_ref_function;
  /** @var CMediusers */
  public $_ref_praticien;
  /** @var CExercicePlace */
  public $_ref_exercice_place;
  public $_nb_ref_consultations;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'consultation_cat';
    $spec->key   = 'categorie_id';
    $spec->xor["owner"] = array("function_id", "praticien_id");
    return $spec;
  }

  /**
   * @inheritDoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["function_id"]   = "ref class|CFunctions back|consultation_cats fieldset|target";
    $props["praticien_id"]  = "ref class|CMediusers back|consultation_cats fieldset|target";
    $props["nom_categorie"] = "str notNull fieldset|default";
    $props["nom_icone"]     = "str notNull";
    $props["duree"]         = "num min|1 max|255 notNull default|1 show|0";
    $props["commentaire"]   = "text helped seekable";
    $props["seance"]        = "bool default|0";
    $props["max_seances"]   = "num default|25";
    $props["anticipation"]  = "num default|5";
    $props["couleur"]       = "color";
    $props["sync_appfine"]  = "bool default|0";
    $props["authorize_booking_new_patient"]  = "bool default|1";
    $props["eligible_teleconsultation"]  = "bool default|0";
    $props["exercice_place_id"] = "ref class|CExercicePlace seekable back|exercice_place_categorie";
    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->nom_categorie;
    $this->_threshold_alert = $this->max_seances - $this->anticipation;
  }

  /**
   * @see parent::updatePlainFields()
   */
  function updatePlainFields() {
    parent::updatePlainFields();

    if ($this->nom_icone) {
      $this->nom_icone = basename($this->nom_icone);
    }
  }

  /**
   * Chargement de la fonction associée à la catégorie
   *
   * @return CFunctions
   */
  function loadRefFunction() {
    return $this->_ref_function = $this->loadFwdRef("function_id", true);
  }

    /**
     * Chargement du praticien associé à la catégorie
     *
     * @return CMediusers
     */
    function loadRefPraticien() {
        return $this->_ref_praticien = $this->loadFwdRef("praticien_id", true);
    }

    /**
     * Chargement du lieu
     *
     * @return CExercicePlace
     */
    function loadRefExercicePlace() {
        return $this->_ref_exercice_place = $this->loadFwdRef("exercice_place_id", true);
    }

  /**
   * Charge les consultations liés à une catégorie par patient
   *
   * @param int $patient_id Patient ID
   *
   * @return CConsultation[]
   */
  function loadRefsConsultations($patient_id) {
    $where               = array();
    $where['patient_id'] = " = '$patient_id'";
    return $this->_refs_consultations = $this->loadBackRefs("consultations", null, null, null, null, null, "", $where);
  }

  /**
   * Récupération du nombre de consultations liés à une catégorie
   *
   * @param int $patient_id Identifiant du patient
   *
   * @return int
   */
  function countRefConsultations($patient_id) {
    $where               = array();
    $where['patient_id'] = " = '$patient_id'";
    $consultations = $this->countBackRefs("consultations", $where);

    return $this->_nb_ref_consultations = $consultations;
  }

  /**
   * Get the order of the sessions
   *
   * @param int $patient_id Identifiant du patient
   *
   * @return int
   */
  function getSessionOrder($patient_id) {
    $consultations = $this->loadRefsConsultations($patient_id);

    foreach ($consultations as $_consultation) {
      $_consultation->loadRefPlageConsult();
    }

    $consultations_order = CMbArray::pluck($consultations, "_ref_plageconsult", "date");
    array_multisort($consultations_order, SORT_ASC, $consultations);

    $counter = 1;

    foreach ($consultations as $_consultation) {
      $this->_meeting_order[$_consultation->_id] = $counter;

      $counter++;
    }

    return $this->_meeting_order;
  }

  /**
   * If one of the cerfa 'demande d'entente préalable' exists on a consultation of a group of sessions
   *
   * @param int $patient_id Patient ID
   *
   * @return bool
   */
  function isCerfaEntentePrealable($patient_id) {
    $nb_file = 0;
    $consultations = $this->loadRefsConsultations($patient_id);

    foreach ($consultations as $_consult) {
      $_consult->loadRefsFiles();

      /** @var CFile $_file */
      foreach ($_consult->_ref_files as $_file) {
        if (strpos($_file->file_name, "entente préalable") !== false && $this->seance) {
          $nb_file++;
        }
      }
    }

    return $nb_file > 0;
  }
}
