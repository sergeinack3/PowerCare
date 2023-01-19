<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

/**
 * Represent an IniVariable get from ini_get_all()
 */
class CPhpIniVariable extends CSimpleConfigurationVariable {
  public $globalValue = null;
  public $localValue = null;
  public $accessLevel = null;
  public $mustBeDefined = true;

  /**
   * @see parent::__construct()
   */
  public function __construct($varName) {
    parent::__construct($varName);
  }

  public function mustBeDefined() {
    return $this->mustBeDefined;
  }

  /**
   * Sets the ini var values (globalValue, localValue accessLevel
   *
   * @param mixed $value array that contains the values of the ini variable
   *
   * @return void
   */
  public function setIniVarValue($value) {
    $this->globalValue = $value["global_value"];
    $this->localValue  = $value["local_value"];
    $this->accessLevel = $value["access"];
  }

  public function getGlobalValue() {
    return $this->globalValue;
  }

  public function getLocalValue() {
    return $this->localValue;
  }

  public function getAccessLevel() {
    return $this->accessLevel;
  }
}