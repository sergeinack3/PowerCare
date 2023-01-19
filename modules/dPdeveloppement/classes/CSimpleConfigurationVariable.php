<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

/**
 * Key Value class with notion of existence
 */
class CSimpleConfigurationVariable extends CAbstractConfigurationVariable {
  public $value = null;

  /**
   * @see parent::__construct()
   */
  public function __construct($varName, $value = null) {
    $this->varName = $varName;

    if ($value) {
      $this->value = $value;
    }
  }

  public function getValue() {
    return $this->value;
  }

  public function setValue($value) {
    $this->value = $value;
  }
}