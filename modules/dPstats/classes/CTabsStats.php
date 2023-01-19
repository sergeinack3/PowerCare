<?php

/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Stats;

use Ox\Core\Module\AbstractTabsRegister;
use Ox\Core\Module\CModule;

/**
 * @codeCoverageIgnore
 */
class CTabsStats extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $this->registerFile("vw_hospitalisation", TAB_READ);
        $this->registerFile("vw_bloc", TAB_READ);
        $this->registerFile("vw_cancelled_operations", TAB_READ);
        $this->registerFile("vw_bloc2", TAB_READ);
        $this->registerFile("vw_time_op", TAB_READ);
        $this->registerFile("vw_personnel_salle", TAB_READ);

        if (CModule::getActive("dPprescription")) {
            $this->registerFile("vw_prescriptions", TAB_READ);
        }

        $this->registerFile("vw_patients_provenance", TAB_READ);
        $this->registerFile("vw_prestations", TAB_READ);
        $this->registerFile("vw_user_logs", TAB_ADMIN);
        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
