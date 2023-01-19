<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Position du patient lors de son passage au bloc
 */
class CPosition extends CMbObject {
  // DB Table key
  public $position_id;

  // DB Table key
  public $group_id;
  public $code;
  public $libelle;
  public $actif;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'position';
    $spec->key   = 'position_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["group_id"]    = "ref class|CGroups autocomplete|text back|postitions";
    $props["code"]        = "str";
    $props["libelle"]     = "str seekable notNull";
    $props["actif"]       = "bool default|1";
    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view      = $this->libelle ? $this->libelle : $this->code;
    $this->_shortview = $this->code;
  }

  /**
   * Charge toutes les positions
   *
   * @param bool $actif chargement des actifs uniquement
   *
   * @return CPosition[]
   */
  static function listPositions($actif = true, $use_group = true) {
    $position = new self;
    $where = array();
    if ($actif) {
      $where["actif"] = " = '1'";
    }
    /*
     * Dans le paramétrage nous affichons les positions de tous les établissement
     * Ailleurs uniquement ceux de l'établissement courant ou commun à tous
     */
    if ($use_group) {
      $group_id = CGroups::loadCurrent()->_id;
      $where[] = "group_id = '$group_id' OR group_id IS NULL";
    }
    return $position->loadList($where, "code");
  }
}
