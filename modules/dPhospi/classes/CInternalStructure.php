<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Core\CEntity;
use Ox\Core\CMbString;

/**
 * Description
 */
class CInternalStructure extends CEntity {

  // DB Fields
  public $typologie;

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();

    $props["typologie"] = "str";

    return $props;
  }

  /**
   * @see parent::store()
   */
  function store() {
    if (!$this->_id && !$this->code) {
      $this->mapEntityTo();
      $this->code = CMbString::makeInitials($this->_name);
    }

    return parent::store();
  }
}
