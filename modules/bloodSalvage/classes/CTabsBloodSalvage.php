<?php

/**
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\BloodSalvage;

use Ox\Core\Module\AbstractTabsRegister;
use Ox\Core\Module\CModule;

/**
 * @codeCoverageIgnore
 */
class CTabsBloodSalvage extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        $this->registerFile("vw_bloodSalvage", TAB_READ);
        $this->registerFile("vw_bloodSalvage_sspi", TAB_READ);
        $this->registerFile("vw_stats", TAB_READ);
        $this->registerFile("vw_cellSaver", TAB_EDIT);
        if (CModule::getActive("dPqualite")) {
            $this->registerFile("vw_typeEi_manager", TAB_EDIT);
        }
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}


