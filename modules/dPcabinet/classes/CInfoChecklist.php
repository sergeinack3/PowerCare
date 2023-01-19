<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Ox\Core\CMbObject;

/**
 * Liste des informations à transmettre au patient par établissement
 */
class CInfoChecklist extends CMbObject {
  public $info_checklist_id;

  // DB fields
  public $group_id;
  public $function_id;
  public $libelle;
  public $actif;

  public $_item_id;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'info_checklist';
    $spec->key   = 'info_checklist_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["group_id"]    = "ref class|CGroups notNull back|checklist_consult";
    $props["function_id"] = "ref class|CFunctions back|ftcs_infos_checklist";
    $props["libelle"]     = "str notNull seekable";
    $props["actif"]       = "bool default|1";

    $props["_item_id"]  = "num";
    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->libelle;
  }

  static function loadListWithFunction($function_id) {
    $where = array();
    $where["actif"] = " = '1'";
    $where[] = " function_id IS NULL OR function_id = '$function_id'";
    $info = new CInfoChecklist();
    return $info->loadGroupList($where, "libelle");
  }
}