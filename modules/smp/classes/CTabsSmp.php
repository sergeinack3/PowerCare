<?php

/**
 * @package Mediboard\Smp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Smp;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsSmp extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
