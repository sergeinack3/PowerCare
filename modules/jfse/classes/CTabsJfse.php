<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse;

use Ox\Core\CAppUI;
use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsJfse extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile('cps', TAB_READ);
        $this->registerFile('stats', TAB_READ);
        $this->registerFile('userManagement', TAB_ADMIN, self::TAB_SETTINGS);
        $this->registerFile('adminSettings', TAB_ADMIN, self::TAB_SETTINGS);
        $this->registerFile('manageStsFormula', TAB_ADMIN, self::TAB_SETTINGS);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
