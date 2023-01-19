<?php
/**
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Drawing;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * @codeCoverageIgnore
 */
class CConfigurationDrawing extends AbstractConfigurationRegister
{

    /**
     * @return void
     */
    public function register()
    {
        CConfiguration::register(
            [
                "CGroups" => [
                    "drawing" => [
                        "General" => [
                            "drawing_allow_external_ressource" => "bool default|0",
                            "drawing_square"                   => "bool default|0",
                        ],
                    ],
                ],
            ]
        );
    }
}