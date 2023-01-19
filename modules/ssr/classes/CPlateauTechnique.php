<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\CMbArray;
use Ox\Core\CMbObject;

/**
 * Le plateaux techniques sont composés d'équipements et de techniciens référents
 */
class CPlateauTechnique extends CMbObject {
  // DB Table key
  public $plateau_id;

  // References
  public $group_id;

  // DB Fields
  public $nom;
  public $repartition;
  public $type;

  // Collections
  /** @var CEquipement[] */
  public $_ref_equipements;
  /** @var CTechnicien[] */
  public $_ref_techniciens;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'plateau_technique';
    $spec->key   = 'plateau_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                = parent::getProps();
    $props["group_id"]    = "ref notNull class|CGroups back|plateaux_techniques";
    $props["nom"]         = "str notNull";
    $props["repartition"] = "bool default|1";
    $props["type"]        = "enum list|ssr|psy";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->nom;
  }

  /**
   * Charge les équipements du plateau
   *
   * @param bool $actif Si oui, seulement les actifs
   *
   * @return CEquipement[]
   */
  function loadRefsEquipements($actif = true) {
    $order = "nom ASC";
    /** @var CEquipement[] $equipements */
    $equipements = $this->loadBackRefs("equipements", $order);
    foreach ($equipements as $_equipement) {
      if ($actif && !$_equipement->actif) {
        unset($equipements[$_equipement->_id]);
        continue;
      }
    }

    return $this->_ref_equipements = $equipements;
  }

  /**
   * Charge les techniciens du plateau
   *
   * @param bool $actif Si oui, seulement les actifs
   *
   * @return CTechnicien[]
   */
  function loadRefsTechniciens($actif = true) {
    /** @var CTechnicien[] $techniciens */
    $techniciens = $this->loadBackRefs("techniciens");
    foreach ($techniciens as $_technicien) {
      if ($actif && !$_technicien->actif) {
        unset($techniciens[$_technicien->_id]);
        continue;
      }
      $_technicien->loadRefKine();
    }

    $sorter_a = CMbArray::pluck($techniciens, "_ref_kine", "_user_last_name");
    $sorter_b = CMbArray::pluck($techniciens, "_ref_kine", "_user_first_name");
    array_multisort($sorter_a, SORT_ASC, $sorter_b, SORT_ASC, $techniciens);

    return $this->_ref_techniciens = $techniciens;
  }
}
