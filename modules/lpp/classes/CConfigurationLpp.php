<?php
/**
 * @package Mediboard\Lpp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Lpp;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * @codeCoverageIgnore
 */
class CConfigurationLpp extends AbstractConfigurationRegister
{

    /**
     * @return mixed
     */
    public function register()
    {
        CConfiguration::register(
            [
                "CGroups" => [
                    "lpp" => [
                        "General" => [
                            'cotation_lpp' => 'bool default|0',
                        ],
                    ],
                ],
            ]
        );
    }
}

