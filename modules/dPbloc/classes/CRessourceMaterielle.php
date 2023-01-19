<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Bloc;

use Ox\Core\CMbDT;
use Ox\Core\CMbObject;

/**
 * Ressource materielle utilisée pour les interventions
 * Class CRessourceMaterielle
 */
class CRessourceMaterielle extends CMbObject {
  public $ressource_materielle_id;

  // DB References
  public $type_ressource_id;
  public $group_id;

  // DB Fields
  public $libelle;
  public $deb_activite;
  public $fin_activite;
  public $retablissement;

  /** @var CTypeRessource */
  public $_ref_type_ressource;

  /** @var CUsageRessource[] */
  public $_ref_usages;

  /** @var CIndispoRessource[] */
  public $_ref_indispos;

  /** @var CBesoinRessource[] */
  public $_ref_besoins;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'ressource_materielle';
    $spec->key   = 'ressource_materielle_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props['group_id']          = "ref class|CGroups notNull back|ressources_materielles";
    $props["type_ressource_id"] = "ref class|CTypeRessource notNull autocomplete|libelle back|ressources_materielles";
    $props["libelle"]           = "str notNull seekable";
    $props["deb_activite"]      = "date";
    $props["fin_activite"]      = "date";
    $props["retablissement"]    = "time";
    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->libelle;
  }

  /**
   * @see parent::updatePlainFields()
   */
  function updatePlainFields() {
    parent::updatePlainFields();

    if (!$this->retablissement) {
      $this->retablissement = "00:00:00";
    }
  }

  /**
   * Chargement du type de ressource correspondant
   *
   * @return CTypeRessource
   */
  function loadRefTypeRessource() {
    return $this->_ref_type_ressource = $this->loadFwdRef("type_ressource_id", true);
  }

  /**
   * Chargement des utilisations reliées
   *
   * @param string $from Date min
   * @param string $to   Date max
   *
   * @return CUsageRessource[]
   */
  function loadRefsUsages($from = null, $to = null) {
    if (!$from || !$to) {
      return $this->_ref_usages = $this->loadBackRefs("usages");
    }

    $usage = new CUsageRessource();
    $where = array();
    $ljoin = array();

    $ljoin["besoin_ressource"] = "usage_ressource.besoin_ressource_id = besoin_ressource.besoin_ressource_id";
    $ljoin["operations"] = "operations.operation_id = besoin_ressource.operation_id";
    $from_date = CMbDT::date($from);
    $to_date   = CMbDT::date($to);
    $where["operations.date"] = "BETWEEN '$from_date' AND '$to_date'";

    // Sur les interventions non annulées
    $where["operations.annulee"] = "= '0'";

    // Sur la ressource instanciée
    if ($this->_id) {
      $where["usage_ressource.ressource_materielle_id"] = " = '$this->_id'";
    }
    elseif ($this->type_ressource_id) {
      // Ou sur son type si nouvel objet
      $ljoin["ressource_materielle"] = "ressource_materielle.type_ressource_id = besoin_ressource.type_ressource_id";
      $where["ressource_materielle.type_ressource_id"] = "= '$this->type_ressource_id'";
    }

    /** @var CUsageRessource[] $usages */
    $usages = $usage->loadList($where, null, null, "usage_ressource_id", $ljoin);
    $besoins = CMbObject::massLoadFwdRef($usages, "besoin_ressource_id");
    CMbObject::massLoadFwdRef($besoins, "operation_id");
    CMbObject::massLoadFwdRef($usages, "ressource_materielle_id");

    // Prendre en compte le temps de réhabilitation des ressources
    foreach ($usages as $_usage) {
      $ressource = $_usage->loadRefRessource();
      $operation = $_usage->loadRefBesoin()->loadRefOperation();
      $operation->loadRefPlageOp();
      $deb_op = $operation->_datetime;
      $fin_op = CMbDT::addDateTime($operation->temp_operation, $deb_op);
      $fin_op_reha = CMbDT::addDateTime($ressource->retablissement, $fin_op);
      if ($deb_op >= $to || $fin_op_reha <= $from) {
        unset($usages[$_usage->_id]);
      }
    }

    return $this->_ref_usages = $usages;
  }

  /**
   * Chargement des périodes d'indisponibilité
   *
   * @param string $from date de début
   * @param string $to   date de fin
   *
   * @return CIndispoRessource[]
   */
  function loadRefsIndispos($from = null, $to = null) {
    if ($from && $to) {
      $indispo = new CIndispoRessource();
      $where = array();
      $ljoin = array();

      $where["deb"] = " <= '$to'";
      $where["fin"] = " >= '$from'";

      // Sur la ressource instanciée
      if ($this->_id) {
        $where["ressource_materielle_id"] = "= '$this->_id'";
      }
      elseif ($this->type_ressource_id) {
        // Ou sur son type si nouvel objet
        $ljoin["ressource_materielle"] = "ressource_materielle.ressource_materielle_id = indispo_ressource.ressource_materielle_id";
        $where["ressource_materielle.type_ressource_id"] = "= '$this->type_ressource_id'";
      }
      return $this->_ref_indispos = $indispo->loadList($where, null, null, null, $ljoin);
    }

    return $this->_ref_indispos = $this->loadBackRefs("indispos");
  }

  /**
   * Chargement des besoins
   *
   * @param string $from Date de début
   * @param string $to   Date de fin
   *
   * @return CBesoinRessource[]
   */
  function loadRefsBesoins($from, $to) {
    if (!$from && !$to) {
      return $this->_ref_besoins = array();
    }

    $besoin = new CBesoinRessource();
    $where = array();
    $ljoin = array();
    $from_date = CMbDT::date($from);
    $to_date   = CMbDT::date($to);

    $ljoin["operations"] = "besoin_ressource.operation_id = operations.operation_id";
    $ljoin["usage_ressource"] = "usage_ressource.besoin_ressource_id = besoin_ressource.besoin_ressource_id";

    // On ne charge que les besoins qui n'ont pas d'usage 
    $where[] = "usage_ressource.usage_ressource_id IS NULL";

    $where["operations.date"] = "BETWEEN '$from_date' AND '$to_date'";

    // Sur les interventions non annulées
    $where["operations.annulee"] = "= '0'";

    if ($this->type_ressource_id) {
      $ljoin["ressource_materielle"] = "ressource_materielle.type_ressource_id = besoin_ressource.type_ressource_id";
      $where["ressource_materielle.type_ressource_id"] = "= '$this->type_ressource_id'";
    }
    /** @var CBesoinRessource[] $besoins */
    $besoins = $besoin->loadList($where, null, null, "besoin_ressource_id", $ljoin);
    CMbObject::massLoadFwdRef($besoins, "operation_id");

    foreach ($besoins as $_besoin) {
      $operation = $_besoin->loadRefOperation();
      $operation->loadRefPlageOp();
      $deb_op = $operation->_datetime;
      $fin_op = CMbDT::addDateTime($operation->temp_operation, $deb_op);
      if ($deb_op >= $to || $fin_op <= $from) {
        unset($besoins[$_besoin->_id]);
      }
    }

    return $this->_ref_besoins = $besoins;
  }
}
