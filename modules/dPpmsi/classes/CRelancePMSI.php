<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Pmsi;

use Ox\Core\CMbObject;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Description
 */
class CRelancePMSI extends CMbObject {
  /**
   * Comptes-Rendus pouvant être demandés lors d'une relance
   *
   * @var array
   */
  static $docs = array("cro", "crana", "cra", "ls", "cotation", "autre");

  /**
   * @var integer Primary key
   */
  public $relance_pmsi_id;

  // DB Fields
  public $sejour_id;
  public $patient_id;
  public $chir_id;
  public $datetime_creation;
  public $datetime_relance;
  public $datetime_cloture;
  public $datetime_med;
  public $urgence;
  public $cro;
  public $cra;
  public $ls;
  public $crana;
  public $cotation;
  public $autre;
  public $description;
  public $commentaire_dim;
  public $commentaire_med;

  // References
  /** @var CPatient */
  public $_ref_patient;
  /** @var CSejour */
  public $_ref_sejour;
  /** @var CMediusers */
  public $_ref_chir;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "relance_pmsi";
    $spec->key    = "relance_pmsi_id";
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();

    $props["sejour_id"]         = "ref class|CSejour back|relance";
    $props["patient_id"]        = "ref class|CPatient back|relances";
    $props["chir_id"]           = "ref class|CMediusers back|relances";
    $props["datetime_creation"] = "dateTime";
    $props["datetime_relance"]  = "dateTime";
    $props["datetime_cloture"]  = "dateTime";
    $props["datetime_med"]      = "dateTime";
    $props["urgence"]           = "enum list|normal|urgent default|normal";
    $props["cro"]               = "bool default|0";
    $props["crana"]             = "bool default|0";
    $props["cra"]               = "bool default|0";
    $props["ls"]                = "bool default|0";
    $props["cotation"]          = "bool default|0";
    $props["autre"]             = "bool default|0";
    $props["description"]       = "text helped";
    $props["commentaire_dim"]   = "text helped";
    $props["commentaire_med"]   = "text helped";

    return $props;
  }

  /**
   * Charge le patient associé à la relance
   *
   * @return CPatient
   */
  function loadRefPatient() {
    $this->_ref_patient = $this->loadFwdRef("patient_id", true);

    $this->_view = "Relance pour le patient $this->_ref_patient";

    return $this->_ref_patient;
  }

  /**
   * Charge le séjour associé à la relance
   *
   * @return CSejour
   */
  function loadRefSejour() {
    return $this->_ref_sejour = $this->loadFwdRef("sejour_id", true);
  }

  /**
   * Charge le praticien associé à la relance
   *
   * @return CMediusers
   */
  function loadRefChir() {
    return $this->_ref_chir = $this->loadFwdRef("chir_id", true);
  }

  /**
   * @see parent::store()
   */
  function store() {
    $this->completeField("commentaire_med", "datetime_med");

    if (!$this->datetime_med && $this->commentaire_med && $this->fieldModified("commentaire_med")) {
      $this->datetime_med = "current";
    }
    elseif (!$this->commentaire_med && $this->fieldModified("commentaire_med")) {
      $this->datetime_med = "";
    }

    return parent::store();
  }

  /**
   * Charge les relances pour un praticien donné
   *
   * @param int $chir_id Identifiant du praticien
   *
   * @return self[]
   */
  static function loadRelances($chir_id) {
    $relance = new self();
    $user = CMediusers::get($chir_id);

    $where = array(
      "datetime_cloture" => "IS NULL",
      "chir_id"          => $user->getUserSQLClause()
    );

    return $relance->loadList($where);
  }

  /**
   * Compte les relances pour un praticien donné
   *
   * @param int $chir_id Identifiant du praticien
   *
   * @return int
   */
  static function countRelances($chir_id) {
    $relance = new self();

    $where = array(
      "datetime_cloture" => "IS NULL",
      "chir_id"          => "= '$chir_id'"
    );

    return $relance->countList($where);
  }
}
