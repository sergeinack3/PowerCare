<?php
/**
 * @package Mediboard\api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Api;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * @codeCoverageIgnore
 */
class CConfigurationAPI extends AbstractConfigurationRegister
{
    /**
     * @return mixed
     */
    public function register()
    {
        CConfiguration::register(
            [
                "CGroups" => [
                    "api" => [
                        "WithingsAPI" => [
                            "api_id"     => "str",
                            "api_secret" => "str",
                        ],
                        "FitbitAPI"   => [
                            "api_id"     => "str",
                            "api_secret" => "str",
                        ],
                    ],
                ],
            ]
        );
    }
}
