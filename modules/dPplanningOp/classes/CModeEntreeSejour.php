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
 * Mode d'entrée séjour
 */
class CModeEntreeSejour extends CMbObject {
  // DB Table key
  public $mode_entree_sejour_id;

  // DB Table key
  public $code;
  public $mode;
  public $group_id;
  public $libelle;
  public $actif;
  public $provenance;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'mode_entree_sejour';
    $spec->key   = 'mode_entree_sejour_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["code"]     = "str notNull";

    $sejour = new CSejour();
    $props["mode"]     = $sejour->getPropsWitouthFieldset("mode_entree") . " notNull";

    $props["group_id"] = "ref notNull class|CGroups back|modes_entree_sejour";
    $props["libelle"]  = "str seekable";
    $props["actif"]    = "bool default|1";
    $props["provenance"] = $sejour->getPropsWitouthFieldset("provenance");

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
   * Liste des modes d'entrée actifs
   *
   * @param string $group_id Etablissement optionnel
   *
   * @return self[]
   */
  static function listModeEntree($group_id = null) {
    $list_mode_entree = array();
    if (CAppUI::conf("dPplanningOp CSejour use_custom_mode_entree")) {
      $where = array();
      $where["actif"] = "= '1'";
      $where["group_id"] = "= '" . ($group_id ? : CGroups::loadCurrent()->_id) . "'";
      $mode_entree = new self;
      $list_mode_entree = $mode_entree->loadList($where);
    }
    return $list_mode_entree;
  }
}
