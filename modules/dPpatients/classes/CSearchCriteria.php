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
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Critères de recherche pour que l'utilisateur puisse retrouver ceux enregistrés
 */
class CSearchCriteria extends CMbObject {
  // DB Table key
  public $search_criteria_id;

  // DB Fields
  public $title;
  public $owner_id;
  public $created;
  public $user_id;
  public $date_min;
  public $date_max;
  public $patient_id;
  public $ald;
  public $group_by_patient;
  public $pat_name;
  public $sexe;
  public $age_min;
  public $age_max;
  public $medecin_traitant;
  public $medecin_traitant_view;
  public $only_medecin_traitant;
  public $rques;
  public $libelle_evenement;
  public $section_choose;

  // Dossier médical
  public $hidden_list_antecedents_cim10;
  public $antecedents_text;
  public $allergie_text;
  public $hidden_list_pathologie_cim10;
  public $pathologie_text;
  public $hidden_list_probleme_cim10;
  public $probleme_text;

  //Consultation
  public $motif;
  public $rques_consult;
  public $examen_consult;
  public $conclusion;

  //Séjour
  public $libelle;
  public $type;
  public $rques_sejour;
  public $convalescence;

  //Intervention
  public $libelle_interv;
  public $rques_interv;
  public $examen;
  public $materiel;
  public $exam_per_op;
  public $codes_ccam;

  //Prescription
  public $produit;
  public $code_cis;
  public $code_ucd;
  public $libelle_produit;
  public $classes_atc;
  public $composant;
  public $keywords_composant;
  public $indication;
  public $keywords_indication;
  public $type_indication;
  public $commentaire;

  // Filter fields
  public $_age_min;
  public $_age_max;
  public $_rques_consult;
  public $_examen_consult;
  public $_rques_sejour;
  public $_libelle_interv;
  public $_rques_interv;

  /** @var CPatient */
  public $_ref_patient;
  /** @var CMediusers */
  public $_ref_user;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'search_criteria';
    $spec->key   = 'search_criteria_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                          = parent::getProps();
    $props["title"]                 = "str notNull";
    $props["owner_id"]              = "ref class|CMediusers notNull back|search_criteria_owner";
    $props["created"]               = "dateTime";
    $props["user_id"]               = "ref class|CMediusers notNull back|search_criteria_user";
    $props["date_min"]              = "dateTime";
    $props["date_max"]              = "dateTime";
    $props["patient_id"]            = "ref class|CPatient back|search_criteria";
    $props["ald"]                   = "bool default|0";
    $props["group_by_patient"]      = "bool default|0";
    $props["pat_name"]              = "str";
    $props["sexe"]                  = "enum list|m|f";
    $props["age_min"]               = "num";
    $props["age_max"]               = "num moreEquals|age_min";
    $props["medecin_traitant"]      = "ref class|CMedecin back|search_criteria";
    $props["medecin_traitant_view"] = "str";
    $props["only_medecin_traitant"] = "bool default|0";
    $props["rques"]                 = "str";
    $props["libelle_evenement"]     = "str";
    $props["section_choose"]        = "enum list|consult|sejour|operation default|consult";

    // Dossier médical
    $props["hidden_list_antecedents_cim10"] = "str";
    $props["antecedents_text"]              = "str";
    $props["allergie_text"]                 = "str";
    $props["hidden_list_pathologie_cim10"]  = "str";
    $props["pathologie_text"]               = "str";
    $props["hidden_list_probleme_cim10"]    = "str";
    $props["probleme_text"]                 = "str";

    //Consultation
    $props["motif"]          = "str";
    $props["rques_consult"]  = "str";
    $props["examen_consult"] = "str";
    $props["conclusion"]     = "str";

    //Séjour
    $props["libelle"]       = "str";
    $props["type"]          = "enum list|comp|ambu|exte|seances|ssr|psy|urg|consult";
    $props["rques_sejour"]  = "str";
    $props["convalescence"] = "str";

    //Intervention
    $props["libelle_interv"] = "str";
    $props["rques_interv"]   = "str";
    $props["examen"]         = "str";
    $props["materiel"]       = "str";
    $props["exam_per_op"]    = "str";
    $props["codes_ccam"]     = "str";

    //Prescription
    $props["produit"]             = "str";
    $props["code_cis"]            = "numchar length|8";
    $props["code_ucd"]            = "numchar length|7";
    $props["libelle_produit"]     = "str";
    $props["classes_atc"]         = "str maxLength|7";
    $props["composant"]           = "num";
    $props["keywords_composant"]  = "str";
    $props["indication"]          = "str";
    $props["keywords_indication"] = "str";
    $props["type_indication"]     = "num";
    $props["commentaire"]         = "str";

    return $props;
  }

  /**
   * Charge le patient sélectionné
   *
   * @return CPatient
   */
  function loadRefPatient() {
    return $this->_ref_patient = $this->loadFwdRef("patient_id", true);
  }

  /**
   * Charge l'utilisateur sélectionné
   *
   * @return CMediusers
   */
  function loadRefUser() {
    return $this->_ref_user = $this->loadFwdRef("user_id", true);
  }

  /**
   * @see parent::store()
   */
  function store() {
    if (!$this->_id) {
      $this->created = CMbDT::dateTime();
    }

    return parent::store();
  }
}
