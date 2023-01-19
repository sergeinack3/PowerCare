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

/**
 * Mode de destination
 */
class CModeDestinationSejour extends CMbObject {
  // DB Table key
  public $mode_destination_sejour_id;

  // DB Table key
  public $group_id;
  public $code;
  public $libelle;
  public $actif;
  public $default;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'mode_destination_sejour';
    $spec->key   = 'mode_destination_sejour_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["group_id"]    = "ref notNull class|CGroups back|modes_destination";
    $props["code"]        = "str notNull";
    $props["libelle"]     = "str seekable";
    $props["actif"]       = "bool default|1";
    $props["default"]     = "bool default|0";
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
   * @see parent::check()
   */
  function check() {
    $this->completeField("default", "group_id", "actif");

    //Une seule destination doit être par défault
    if ($this->default && $this->actif) {
      $where["group_id"] = " = '$this->group_id'";
      $where["default"]  = " = '1'";
      $where["actif"]    = " = '1'";
      if ($this->_id) {
        $where[] = "mode_destination_sejour_id <> '$this->_id'";
      }
      $destination = new self;
      $destination->loadObject($where);
      if ($destination->_id) {
        return CAppUI::tr("CModeDestinationSejour-failed-uniq_actif");
      }
    }

    return parent::check();
  }

  /**
   * Charge tous les modes de destination
   *
   * @param bool $actif chargement des actifs uniquement
   *
   * @return CModeDestinationSejour[]
   */
  static function listModes($actif = true) {
    $mode_dest = new self;
    $where = array();
    if ($actif) {
      $where["actif"] = " = '1'";
    }
    return $mode_dest->loadGroupList($where, "code");
  }
}
