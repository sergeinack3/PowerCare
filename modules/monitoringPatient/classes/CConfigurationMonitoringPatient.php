<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\MonitoringPatient;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * Monitoring patient
 */
class CConfigurationMonitoringPatient extends AbstractConfigurationRegister
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        $configs = [
            "CGroups" => [
                "monitoringPatient" => [
                    "General" => [
                        "frequency_automatic_graph" => "num min|1 default|1",
                    ],
                    'CConstantesMedicales' => [
                        "frequency_integration_constants" => "num min|0 default|1",
                    ],
                ],
            ],
        ];

        CConfiguration::register($configs);
    }
}
