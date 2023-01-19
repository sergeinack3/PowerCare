<?php

/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\MonitoringPatient;

use Ox\Core\Module\AbstractTabsRegister;

/**
 * @codeCoverageIgnore
 */
class CTabsMonitoringPatient extends AbstractTabsRegister
{

    public function registerAll(): void
    {
        $this->registerFile('vw_config_param_surveillance', TAB_ADMIN);
        $this->registerFile('vw_supervision_graph', TAB_ADMIN);

        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
