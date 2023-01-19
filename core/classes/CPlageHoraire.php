<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

/**
 * Classe utilitaire de gestion des plages horaires
 */
class CPlageHoraire extends CMbObject {
  // DB fields
  public $date;
  public $debut;
  public $fin;

  // Behaviour fields
  public $_skip_collisions;

  /**
   * @var self[]
   */
  public $_colliding_plages;

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                     = parent::getProps();
    $props["date"]             = "date notNull fieldset|default";
    $props["debut"]            = "time notNull fieldset|default";
    $props["fin"]              = "time notNull moreThan|debut fieldset|default";
    $props["_skip_collisions"] = "bool default|0";

    return $props;
  }

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();

    // Collision keys mandatory definition to determine which references identify collisions
    // Has to be an array, be it empty
    $spec->collision_keys = null;

    return $spec;
  }

  /**
   * Check collision with another plage regarding defined in class spec
   *
   * @return string Collision message
   */
  function hasCollisions() {
    if ($this->_skip_collisions) {
      return null;
    }

    // Check whether mandatory collision keys are defined
    $keys = $this->_spec->collision_keys;
    if (!is_array($keys)) {
      CModelObject::error("CPlageHoraire-collision_keys", $this->_class);
    }

    $this->completeField("date", "debut", "fin");
    $this->completeField($keys);

    // Get all other plages the same day
    $where[$this->_spec->key] = "!= '$this->_id'";
    $where["date"]            = "= '$this->date'";
    $where["debut"]           = "< '$this->fin'";
    $where["fin"]             = "> '$this->debut'";

    // Append collision keys clauses
    foreach ($keys as $_key) {
      if ($this->$_key) {
        $where[$_key] = "= '{$this->$_key}'";
      }
      else {
        $where[$_key] = "IS NULL";
      }
    }

    // Load collision
    /** @var CPlageHoraire $plage */
    $plage                   = new static;
    $this->_colliding_plages = $plage->loadList($where);

    // Build collision message
    $msgs = array();
    foreach ($this->_colliding_plages as $_plage) {
      /** @var CPlageHoraire $_plage */
      $msgs[] = "Collision avec la plage de '$_plage->debut' à '$_plage->fin'";
    }

    return count($msgs) ? implode(", ", $msgs) : null;
  }

  /**
   * @see parent::check()
   */
  function check() {
    if (!$this->_merging) {
      if ($msg = $this->hasCollisions()) {
        return $msg;
      }
    }

    return parent::check();
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = sprintf(
      CAppUI::tr("CPlageHoraire-of %s from %s to %s",
        CMbDT::transform($this->date, null, CAppUI::conf("date")),
        CMbDT::transform($this->debut, null, CAppUI::conf("time")),
        CMbDT::transform($this->fin, null, CAppUI::conf("time"))
      )
    );
  }

  /**
   * @see parent::updatePlainFields()
   */
  function updatePlainFields() {
    // Usefull for automatic plages coming from instant consult in emergency
    if ($this->fin && $this->fin == "00:00:00") {
      $this->fin = "23:59:59";
    }
  }
}
