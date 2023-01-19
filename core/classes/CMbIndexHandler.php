<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Ox\Core\Autoload\IShortNameAutoloadable;

/**
 * Event handler class for Mediboard index main dispatcher
 */
abstract class CMbIndexHandler implements IShortNameAutoloadable {
  /**
   * Before main.php inclusion
   *
   * @return void
   */
  function onBeforeMain() {

  }

  /**
   * After main.php inclusion
   *
   * @return void
   */
  function onAfterMain() {

  }
}
