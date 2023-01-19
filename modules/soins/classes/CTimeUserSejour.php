<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins;

use Ox\Core\CMbObject;

/**
 * Class CTimeUserSejour
 */
class CTimeUserSejour extends CMbObject {
  public $sejour_timing_id;

  // DB Fields
  public $group_id;
  public $name;
  public $description;
  public $time_debut;
  public $time_fin;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'sejour_timing_personnel';
    $spec->key   = 'sejour_timing_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                = parent::getProps();
    $props["group_id"]    = "ref class|CGroups notNull back|horaires_affect_personnel";
    $props["name"]        = "str notNull";
    $props["description"] = "text";
    $props['time_debut']  = 'time notNull';
    $props['time_fin']    = 'time notNull';

    return $props;
  }

  /**
   * @see  parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->name;
  }
}
