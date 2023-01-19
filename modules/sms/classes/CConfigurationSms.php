<?php
/**
 * @package Mediboard\Sms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sms;

use Ox\Core\Handlers\HandlerParameterBag;
use Ox\Mediboard\System\AbstractConfigurationRegister;

/**
 * @codeCoverageIgnore
 */
class CConfigurationSms extends AbstractConfigurationRegister {
  /**
   * @inheritDoc
   */
  public function getObjectHandlers(HandlerParameterBag $parameter_bag): void {
      $parameter_bag
          ->register(CSmsObjectHandler::class, false);
  }
}
