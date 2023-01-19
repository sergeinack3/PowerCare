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
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CProtocole;

/**
 * Besoin en ressource materielle
 * Class CBesoinRessource
 */
class CBesoinRessource extends CMbObject {
  public $besoin_ressource_id;

  // DB References
  public $type_ressource_id;
  public $protocole_id;
  public $operation_id;
  public $commentaire;

  /** @var CTypeRessource */
  public $_ref_type_ressource;

  /** @var COperation */
  public $_ref_operation;

  /** @var CProtocole */
  public $_ref_protocole;

  /** @var CUsageRessource */
  public $_ref_usage;

  // Form Fields
  public $_color;
  public $_width;
  public $_debut_offset;
  public $_fin_offset;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'besoin_ressource';
    $spec->key   = 'besoin_ressource_id';
    $spec->xor["owner"] = array("operation_id", "protocole_id");
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["type_ressource_id"] = "ref class|CTypeRessource notNull back|besoins";
    $props["operation_id"]      = "ref class|COperation cascade back|besoins_ressources";
    $props["protocole_id"]      = "ref class|CProtocole cascade back|besoins_ressources";
    $props["commentaire"]       = "text helped";
    return $props;
  }

  /**
   * Check if the ressource is available or not, and set the color
   *
   * @return bool
   */
  function isAvailable() {
    $this->loadRefOperation();
    $deb_op = $this->_ref_operation->_datetime_best;
    $fin_op  = CMbDT::addDateTime($this->_ref_operation->temp_operation, $deb_op);
    $type_ressource = $this->loadRefTypeRessource();
    $nb_ressources = $type_ressource->countBackRefs("ressources_materielles");
    $_usage = $this->loadRefUsage();
    $this->_color = '0a0';
    // S'il y a un usage, alors on peut vérifier si conflit avec :
    // - un autre usage
    // - une indispo
    // - un besoin
    // Dans ce cas, on passe en rouge
    if ($_usage->_id) {
      $ressource = $_usage->loadRefRessource();

      $_usages = $ressource->loadRefsUsages($deb_op, $fin_op);
      unset($_usages[$_usage->_id]);

      $_indispos = $ressource->loadRefsIndispos($deb_op, $fin_op);

      $_besoins = $ressource->loadRefsBesoins($deb_op, $fin_op);
      unset($_besoins[$this->_id]);

      if (count($_usages) + count($_indispos) + count($_besoins) >= $nb_ressources) {
        $this->_color = 'a00';
        return false;
      }

      return true;
    }

    // Sinon, on parcourt les ressources associées au type de ressource du besoin.
    $ressources = $type_ressource->loadRefsRessources();
    $_usages   = 0;
    $_indispos = 0;
    $_besoins  = 0;

    foreach ($ressources as $_ressource) {
      $_usages += count($_ressource->loadRefsUsages($deb_op, $fin_op));
      $_indispos += count($_ressource->loadRefsIndispos($deb_op, $fin_op));
    }

    // Pour compter les besoins, on ne le fait qu'une fois.
    // Car un besoin cible un type de ressource.
    // On décrémente d'une unité, car le besoin de la boucle est compté
    $_ressource = new CRessourceMaterielle;
    $_ressource->type_ressource_id = $type_ressource->_id;
    $_besoins = count($_ressource->loadRefsBesoins($deb_op, $fin_op)) - 1;

    if ($_usages + $_indispos + $_besoins >= $nb_ressources) {
      $this->_color = 'a00';
      return false;
    }

    return true;
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
   * Chargement de l'intervention correspondante
   *
   * @return COperation
   */
  function loadRefOperation() {
    return $this->_ref_operation = $this->loadFwdRef("operation_id", true);
  }

  /**
   * Chargement du protocole correspondant
   *
   * @return CProtocole
   */
  function loadRefProtocole() {
    return $this->_ref_protocole = $this->loadFwdRef("protocole_id", true);
  }

  /**
   * Chargement de l'utilisation de la ressource correspondante
   *
   * @return CUsageRessource
   */
  function loadRefUsage() {
    return $this->_ref_usage = $this->loadUniqueBackRef("usages", true);
  }
}
