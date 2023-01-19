<?php

/**
 * @package Mediboard\Ucum
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ucum;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * Ucum
 */
class CConfigurationUcum extends AbstractConfigurationRegister
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        CConfiguration::register(
            [
                "CGroups" => [
                    "ucum" => [
                        "general" => [
                            "path_conversion" => "str default|/ucumtransform",
                            "path_validation" => "str default|/isValidUCUM",
                            "path_to_base"    => "str default|/toBaseUnits",
                        ],
                    ],
                ],
            ]
        );
    }
}
