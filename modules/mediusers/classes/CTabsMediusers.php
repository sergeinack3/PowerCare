<?php

/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsMediusers extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $this->registerFile("vw_idx_mediusers", TAB_READ);
        $this->registerFile("vw_idx_functions", TAB_READ);
        $this->registerFile("vw_idx_disciplines", TAB_READ);
        $this->registerFile('vw_api', TAB_ADMIN);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
