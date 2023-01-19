<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Core\CMbObject;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Description
 */
class CPrestationExpert extends CMbObject {
  public const RESOURCE_TYPE = 'prestation_expert';

  public const FIELDSET_CATEGORY = "category";

  // DB Fields
  public $nom;
  public $group_id;
  public $type_hospi;
  public $M;
  public $C;
  public $O;
  public $SSR;
  public $actif;

  // References
  /** @var CItemPrestation[] */
  public $_ref_items;

  // Form fields
  public $_count_items = 0;
  public $_types_pec;

  static $types_pec = array("M", "C", "O", "SSR");

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props               = parent::getProps();
    $props["nom"]        = "str notNull fieldset|default";
    $props["group_id"]   = "ref notNull class|CGroups fieldset|default";
    $props["type_hospi"] = "enum list|" . implode("|", CSejour::$types) . "| fieldset|category";
    $props["M"]          = "bool default|0 fieldset|category";
    $props["C"]          = "bool default|0 fieldset|category";
    $props["O"]          = "bool default|0 fieldset|category";
    $props["SSR"]        = "bool default|0 fieldset|category";
    $props["actif"]      = "bool default|1";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->nom;
  }

  function loadRefsItems() {
    return $this->_ref_items = $this->loadBackRefs("items");
  }
}
