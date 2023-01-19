<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cim10;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * @codeCoverageIgnore
 */
class CConfigurationCim10 extends AbstractConfigurationRegister
{

    /**
     * @return mixed
     */
    public function register()
    {
        CConfiguration::register(
            [
                'CGroups' => [
                    'dPcim10' => [
                        'diagnostics' => [
                            'restrict_code_usage' => 'bool default|0',
                        ],
                    ],
                ],
            ]
        );
    }
}
