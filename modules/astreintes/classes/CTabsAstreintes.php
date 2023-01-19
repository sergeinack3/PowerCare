<?php

/**
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Astreintes;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsAstreintes extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile("calendrierAstreinte", TAB_READ);
        $this->registerFile("listAstreintes", TAB_EDIT);
        $this->registerFile("listCategorieAstreintes", TAB_EDIT, self::TAB_SETTINGS);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
