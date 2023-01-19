<?php
/**
 * @package Mediboard\soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins;

use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Description
 */
class CObjectifSoinCategorie extends CMbObject {
  /** @var integer Primary key */
  public $objectif_soin_categorie_id;

  // DB Fields
  public $libelle;
  public $description;
  public $group_id;
  public $actif;

  // References
  /** @var CGroups */
  public $_ref_group;

  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->libelle;
  }

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "objectif_soin_categorie";
    $spec->key   = "objectif_soin_categorie_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props["libelle"]     = "str";
    $props["description"] = "text";
    $props["group_id"]    = "ref class|CGroups back|objectifs_soin";
    $props["actif"]       = "bool";

    return $props;
  }

  /**
   * Chargement de l'établissement
   *
   * @return CGroups
   */
  function loadRefGroup() {
    return $this->_ref_group = $this->loadFwdRef("group_id", true);
  }

  /**
   * Chargement des catégories actives
   *
   * @return CObjectifSoinCategorie[]
   */
  function loadActiveList($where = array(), $order_by = "libelle ASC") {
    $where["actif"] = "= '1'";

    return $this->loadList($where, $order_by);
  }
}
