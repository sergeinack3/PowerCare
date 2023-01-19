<?php

/**
 * @package Mediboard\Printing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Printing;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsPrinting extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $this->registerFile('vw_idx_sources', TAB_READ);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
