<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Pmsi;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * @codeCoverageIgnore
 */
class CConfigurationPmsi extends AbstractConfigurationRegister
{
    /**
     * @return mixed
     */
    public function register()
    {
        CConfiguration::register(
            [
                "CGroups" => [
                    "dPpmsi" => [
                        "display"       => [
                            "see_recept_dossier" => "bool default|0",
                        ],
                        "relances"      => [
                            "cra"      => "bool default|1",
                            "crana"    => "bool default|1",
                            "cro"      => "bool default|1",
                            "ls"       => "bool default|1",
                            "cotation" => "bool default|1",
                            "autre"    => "bool default|1",
                        ],
                        "type_document" => [
                            "cra"      => "custom tpl|inc_config_vue_list_categories_file_custom",
                            "crana"    => "custom tpl|inc_config_vue_list_categories_file_custom",
                            "cro"      => "custom tpl|inc_config_vue_list_categories_file_custom",
                            "ls"       => "custom tpl|inc_config_vue_list_categories_file_custom",
                            "cotation" => "custom tpl|inc_config_vue_list_categories_file_custom",
                            "autre"    => "custom tpl|inc_config_vue_list_categories_file_custom",
                        ],
                    ],
                ],
            ]
        );
    }
}