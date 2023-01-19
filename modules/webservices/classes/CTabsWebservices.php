<?php

/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Webservices;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsWebservices extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $this->registerFile('vw_stats', TAB_READ);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
