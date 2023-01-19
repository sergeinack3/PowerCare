<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Core\CStoredObject;

class CLitLiaisonItem extends CStoredObject {
  // DB Table key
  public $lit_liaison_item_id;

  // DB Fields
  public $lit_id;
  public $item_prestation_id;

  /** @var CLit */
  public $_ref_lit;

  /** @var CItemPrestation */
  public $_ref_item_prestation;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "lit_liaison_item";
    $spec->key   = "lit_liaison_item_id";

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                       = parent::getProps();
    $props["lit_id"]             = "ref notNull class|CLit cascade back|liaisons_items";
    $props["item_prestation_id"] = "ref notNull class|CItemPrestation cascade back|liaisons_lits";

    return $props;
  }

  function loadRefLit() {
    return $this->_ref_lit = $this->loadFwdRef("lit_id", true);
  }

  function loadRefItemPrestation() {
    return $this->_ref_item_prestation = $this->loadFwdRef("item_prestation_id", true);
  }
}
