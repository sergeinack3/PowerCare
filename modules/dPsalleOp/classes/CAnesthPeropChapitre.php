<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp;

use Exception;
use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Chapitre de l'évenement per-opératoire
 */
class CAnesthPeropChapitre extends CMbObject {
  public $anesth_perop_chapitre_id;

  // DB fields
  public $group_id;
  public $libelle;
  public $description;
  public $actif;

  /** @var CGroups */
  public $_ref_group;
  /** @var CAnesthPeropCategorie[] */
  public $_ref_anesth_categories_perop;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'anesth_perop_chapitre';
    $spec->key   = 'anesth_perop_chapitre_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                = parent::getProps();
    $props["group_id"]    = "ref notNull class|CGroups back|anesth_perop_chapitres";
    $props["libelle"]     = "str notNull";
    $props["description"] = "text";
    $props["actif"]       = "bool default|1";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = "$this->libelle";
  }

  /**
   * Load categories gestures Perop
   *
   * @param array $where Clause where
   *
   * @return CAnesthPeropCategorie[]
   * @throws Exception
   */
  function loadRefsCategoriesGestes($where = array()) {
    $group             = CGroups::loadCurrent();
    $where["group_id"] = " = '$group->_id'";

    return $this->_ref_anesth_categories_perop = $this->loadBackRefs("anesth_perop_categories", "libelle ASC", null, null, null, null, "", $where);
  }

  /**
   * Load group forward reference
   *
   * @return CGroups
   * @throws Exception
   */
  function loadRefGroup() {
    /** @var CGroups */
    $this->_ref_group = $this->loadFwdRef("group_id", 1);

    return $this->_ref_group;
  }
}
