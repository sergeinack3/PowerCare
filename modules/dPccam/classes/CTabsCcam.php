<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsCcam extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile("searchCcamCodes", TAB_READ);
        $this->registerFile("viewCcamCode", TAB_READ);
        $this->registerFile("viewFavoris", TAB_READ);
        $this->registerFile("viewSearchCodeCcamHistory", TAB_READ);
        $this->registerFile('ngapIndex', TAB_READ);
        $this->registerFile("viewFraisDiversTypes", TAB_ADMIN);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
