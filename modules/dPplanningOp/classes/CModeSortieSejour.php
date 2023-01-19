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
use Ox\Mediboard\Etablissement\CEtabExterne;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Urgences\CRPU;

/**
 * Mode de sortie
 */
class CModeSortieSejour extends CMbObject {
  // DB Table key
  public $mode_sortie_sejour_id;

  // DB Fields
  public $code;
  public $mode;
  public $group_id;
  public $libelle;
  public $actif;
  public $destination;
  public $orientation;
  public $etab_externe_id;

  // References
  /** @var CEtabExterne */
  public $_ref_etab_externe;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'mode_sortie_sejour';
    $spec->key   = 'mode_sortie_sejour_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $sejour = new CSejour();
    $rpu = new CRPU();

    $props["code"]        = "str notNull";
    $props["mode"]        = $sejour->getPropsWitouthFieldset("mode_sortie")." notNull";
    $props["group_id"]    = "ref notNull class|CGroups back|modes_sortie_sejour";
    $props["libelle"]     = "str seekable";
    $props["actif"]       = "bool default|1";
    $props['destination'] = $sejour->getPropsWitouthFieldset('destination');
    $props['orientation'] = $rpu->_props['orientation'];
    $props["etab_externe_id"] = "ref class|CEtabExterne back|modes_sortie";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view      = $this->libelle ? : $this->code;
    $this->_shortview = $this->code;
  }

  /**
   * @return CEtabExterne
   */
  function loadRefEtabExterne() {
    return $this->_ref_etab_externe = $this->loadFwdRef("etab_externe_id", true);
  }

  /**
   * Liste des modes de sortie actifs
   *
   * @param string $group_id Etablissement optionnel
   *
   * @return self[]
   */
  static function listModeSortie($group_id = null) {
    $list_mode_sortie = array();
    if (CAppUI::conf("dPplanningOp CSejour use_custom_mode_sortie")) {
      $where = array();
      $where["actif"] = "= '1'";
      $where["group_id"] = "= '" . ($group_id ? : CGroups::loadCurrent()->_id) . "'";
      $mode_sortie = new self;
      $list_mode_sortie = $mode_sortie->loadList($where);
    }
    return $list_mode_sortie;
  }
}
