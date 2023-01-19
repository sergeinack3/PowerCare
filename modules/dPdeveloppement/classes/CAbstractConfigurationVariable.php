<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

use Ox\Core\Autoload\IShortNameAutoloadable;

/**
 * Abstract representation of a configuration variable
 */
abstract class CAbstractConfigurationVariable implements IShortNameAutoloadable {

  public $varName = null;

  public $exists = false;

  /**
   * CAbstractConfigurationVariable constructor.
   *
   * @param string $varName Name of the configuration variable
   */
  public function __construct($varName) {
    $this->varName = $varName;
  }


  public function setExists($exists) {
    $this->exists = $exists;
  }

  public function exists() {
    return $this->exists;
  }

  public function getVarName() {
    return $this->varName;
  }

}