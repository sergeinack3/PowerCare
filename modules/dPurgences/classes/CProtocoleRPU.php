<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Urgences;

use Ox\Core\CMbObject;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CChargePriceIndicator;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Protocole de RPU
 */
class CProtocoleRPU extends CMbObject {
  /** @var integer Primary key */
  public $protocole_rpu_id;

  // DB Fields
  public $group_id;
  public $libelle;
  public $actif;
  public $default;
  public $responsable_id;
  public $uf_soins_id;
  public $charge_id;
  public $box_id;
  public $mode_entree;
  public $mode_entree_id;
  public $transport;
  public $provenance;
  public $pec_transport;

  // References
  /** @var CMediusers */
  public $_ref_responsable;
  /** @var CUniteFonctionnelle */
  public $_ref_uf_soins;
  /** @var CChargePriceIndicator */
  public $_ref_charge;
  /** @var CLit */
  public $_ref_box;
  /** @var CModeEntreeSejour */
  public $_ref_mode_entree;

  // Form fields
  public $_mode_entree_id_view;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "protocole_rpu";
    $spec->key   = "protocole_rpu_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $sejour = new CSejour();
    $rpu    = new CRPU();

    $props["group_id"]       = "ref class|CGroups back|protocoles_rpu";
    $props["libelle"]        = "str notNull seekable";
    $props["actif"]          = "bool default|0";
    $props["default"]        = "bool default|0";
    $props["responsable_id"] = "ref class|CMediusers back|protocoles_rpu";
    $props["uf_soins_id"]    = "ref class|CUniteFonctionnelle back|protocoles_rpu";
    $props["charge_id"]      = "ref class|CChargePriceIndicator back|protocoles_rpu";
    $props["box_id"]         = "ref class|CLit back|protocoles_rpu";
    $props["mode_entree"]    = $rpu->_props["_mode_entree"];
    $props["mode_entree_id"] = "ref class|CModeEntreeSejour autocomplete|libelle|true back|protocoles_rpu";
    $props["transport"]      = $sejour->getPropsWitouthFieldset("transport");
    $props["provenance"]     = $rpu->_props["_provenance"];
    $props["pec_transport"]  = $rpu->_props["pec_transport"];

    // Retrait du notNull sur le mode d'entrée
    $props["mode_entree"]    = str_replace(" notNull", "", $props["mode_entree"]);
    $props["mode_entree_id"] = str_replace(" notNull", "", $props["mode_entree_id"]);

    // Form fields
    $props["_mode_entree_id_view"] = "str";
    $props["_docitems_guid"]       = "str";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->libelle;
  }

  /**
   * Charge le responsable
   *
   * @return CMediusers
   */
  function loadRefResponsable() {
    return $this->_ref_responsable = $this->loadFwdRef("responsable_id", true);
  }

  /**
   * Charge l'uf de soins
   *
   * @return CUniteFonctionnelle
   */
  function loadRefUfSoins() {
    return $this->_ref_uf_soins = $this->loadFwdRef("uf_soins_id", true);
  }

  /**
   * Charge le mode de traitement
   *
   * @return CChargePriceIndicator
   */
  function loadRefCharge() {
    return $this->_ref_charge = $this->loadFwdRef("charge_id", true);
  }

  /**
   * Charge le box
   *
   * @return CLit
   */
  function loadRefBox() {
    return $this->_ref_box = $this->loadFwdRef("box_id", true);
  }

  /**
   * Charge le mode d'entrée personnalisé
   *
   * @return CModeEntreeSejour
   */
  function loadRefModeEntree() {
    return $this->_ref_mode_entree = $this->loadFwdRef("mode_entree_id", true);
  }

  static function loadProtocoles() {
    $protocole_rpu = new self();

    return $protocole_rpu->loadGroupList(array("actif" => "= '1'"));
  }
}
