<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Gestion des protocoles opératoires
 */
class CProtocoleOperatoire extends CMbObject {
  /** @var integer Primary key */
  public $protocole_operatoire_id;

  // DB fields
  public $chir_id;
  public $function_id;
  public $group_id;
  public $libelle;
  public $code;
  public $actif;
  public $numero_version;
  public $remarque;
  public $validation_praticien_id;
  public $validation_praticien_datetime;
  public $validation_cadre_bloc_id;
  public $validation_cadre_bloc_datetime;
  public $description_equipement_salle;
  public $description_installation_patient;
  public $description_preparation_patient;
  public $description_instrumentation;

  // References
  /** @var CMediusers */
  public $_ref_chir;

  /** @var CFunctions */
  public $_ref_function;

  /** @var CGroups */
  public $_ref_group;

  /** @var CMediusers */
  public $_ref_validation_praticien;

  /** @var CMediusers */
  public $_ref_validation_cadre_bloc;

  /** @var CMaterielOperatoire[] */
  public $_refs_materiels_operatoires = [];

  /** @var CMaterielOperatoire[] */
  public $_refs_materiels_operatoires_dm = [];

  /** @var CMaterielOperatoire[] */
  public $_refs_materiels_operatoires_dm_sterilisables = [];

  /** @var CMaterielOperatoire[] */
  public $_refs_materiels_operatoires_produit = [];

  // Form fields
  public $_force_invalide_signature;


  public static $_check_modify = true;

  /**
   * @inheritdoc
   */
  public function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "protocole_operatoire";
    $spec->key   = "protocole_operatoire_id";
    $spec->xor["owner"] = array("chir_id", "function_id", "group_id");
    return $spec;
  }

  /**
   * @inheritdoc
   */
  public function getProps() {
    $props = parent::getProps();
    $props["chir_id"]                          = "ref class|CMediusers back|protocoles_op";
    $props["function_id"]                      = "ref class|CFunctions back|protocoles_op";
    $props["group_id"]                         = "ref class|CGroups back|protocoles_op";
    $props["libelle"]                          = "str notNull";
    $props["code"]                             = "str";
    $props["actif"]                            = "bool default|1";
    $props["numero_version"]                   = "str";
    $props["remarque"]                         = "text helped";
    $props["validation_praticien_id"]          = "ref class|CMediusers back|protocoles_op_prat";
    $props["validation_praticien_datetime"]    = "dateTime";
    $props["validation_cadre_bloc_id"]         = "ref class|CMediusers back|protocoles_op_cadre";
    $props["validation_cadre_bloc_datetime"]   = "dateTime";
    $props["description_equipement_salle"]     = "text helped";
    $props["description_installation_patient"] = "text helped";
    $props["description_preparation_patient"]  = "text helped";
    $props["description_instrumentation"]      = "text helped";
    return $props;
  }

  /**
   * @inheritDoc
   */
  public function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->libelle;
  }

  /**
   * Charge le praticien associé au protocole
   *
   * @return CMediusers
   */
  public function loadRefChir() {
    return $this->_ref_chir = $this->loadFwdRef("chir_id", true);
  }

  /**
   * Charge le cabinet associé au protocole
   *
   * @return CFunctions
   */
  public function loadRefFunction() {
    return $this->_ref_function = $this->loadFwdRef("function_id", true);
  }

  /**
   * Charge le cabinet associé au protocole
   *
   * @return CGroups
   */
  public function loadRefGroup() {
    return $this->_ref_group = $this->loadFwdRef("group_id", true);
  }

  /**
   * Charge le praticien validateur du protocole
   *
   * @return CMediusers
   */
  public function loadRefValidationPraticien(): CMediusers {
    return $this->_ref_validation_praticien = $this->loadFwdRef("validation_praticien_id", true);
  }

  /**
   * Charge le cadre de bloc validateur du protocole
   *
   * @return CMediusers
   */
  public function loadRefValidationCadreBloc(): CMediusers {
    return $this->_ref_validation_cadre_bloc = $this->loadFwdRef("validation_cadre_bloc_id", true);
  }

  /**
   * Charge les matériels opératoires du protocole
   *
   * @param bool $with_refs Chargement des références
   * @return CMaterielOperatoire[]
   */
  public function loadRefsMaterielsOperatoires($with_refs = false) {
    return $this->_refs_materiels_operatoires = CMaterielOperatoire::getList($this, $with_refs, false, true);
  }

  /**
   * @inheritDoc
   */
  public function store() {
    $this->completeField("validation_praticien_id", "validation_cadre_bloc_id");

    if (self::$_check_modify && !$this->fieldModified("validation_praticien_id") && !$this->fieldModified("validation_cadre_bloc_id")) {
      $fields_exclude = array(
        "validation_praticien_id", "validation_praticien_datetime",
        "validation_cadre_bloc_id", "validation_cadre_bloc_datetime"
      );

      foreach ($this->getProps() as $_field => $_prop) {
        if (in_array($_field, $fields_exclude)) {
          continue;
        }

        // Invalidation ou forçage de l'invalidation
        if ($this->fieldModified($_field) || $this->_force_invalide_signature) {
          foreach ($fields_exclude as $_field_exclude) {
            $this->$_field_exclude = "";
          }
          break;
        }
      }
    }

    if ($msg = parent::store()) {
      return $msg;
    }
  }
}
