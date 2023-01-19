<?php

/**
 * @package Mediboard\Sms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sms;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsSms extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
