<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Urgences;

use Ox\Core\CStoredObject;

/**
 * Description
 */
class CRPULinkCat extends CStoredObject {
  /** @var integer Primary key */
  public $rpu_link_cat_id;

  // DB fields
  public $rpu_id;
  public $rpu_categorie_id;

  // References
  /** @var CRPUCategorie */
  public $_ref_cat;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "rpu_link_cat";
    $spec->key   = "rpu_link_cat_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                     = parent::getProps();
    $props["rpu_id"]           = "ref class|CRPU back|categories_rpu";
    $props["rpu_categorie_id"] = "ref class|CRPUCategorie back|links";

    return $props;
  }

  /**
   * Charge la catégorie associée à ce lien
   *
   * @return CRPUCategorie
   */
  function loadRefCategorie() {
    return $this->_ref_cat = $this->loadFwdRef("rpu_categorie_id", true);
  }
}
