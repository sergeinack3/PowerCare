<?php

/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsHprimxml extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $this->registerFile("vw_highlightHprimXML", TAB_READ);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
