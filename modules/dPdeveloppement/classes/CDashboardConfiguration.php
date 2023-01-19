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
 * Class CDashboardConfiguration
 */
abstract class CDashboardConfiguration implements IShortNameAutoloadable {
  public $configuration = array();

  /**
   * Prepare the parameters data
   */
  public abstract function init();

  /**
   * Import a set of data inside $this->configuration variable
   *
   * @param string $jsonData serialize json data.
   *
   * @return mixed
   */
  public static function fromJson($jsonData) {

  }
}