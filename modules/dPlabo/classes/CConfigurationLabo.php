<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Labo;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * @codeCoverageIgnore
 */
class CConfigurationLabo extends AbstractConfigurationRegister
{
    /**
     * @return mixed
     */
    public function register()
    {
        CConfiguration::register(
            [
                "CGroups" => [
                    "dPlabo" => [
                        "CCatalogueLabo" => [
                            "remote_name" => "str default|LABO",
                            "remote_url"  => "str default|http://localhost/mediboard/modules/dPlabo/remote/catalogue.xml",
                        ],

                        "CPackExamensLabo" => [
                            "remote_url" => "str default|http://localhost/mediboard/modules/dPlabo/remote/pack.xml",
                        ],
                    ],
                ],
            ]
        );
    }
}
