<?php
/**
 * @package Mediboard\Smp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Smp;

use Ox\Core\Handlers\HandlerParameterBag;
use Ox\Mediboard\System\AbstractConfigurationRegister;

/**
 * @codeCoverageIgnore
 */
class CConfigurationSmp extends AbstractConfigurationRegister {
  /**
   * @inheritDoc
   */
  public function getObjectHandlers(HandlerParameterBag $parameter_bag): void {
      $parameter_bag
          ->register(CSmpObjectHandler::class, false);
  }
}
