<?php

/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * Class CConfigurationCompteRendu
 */
class CConfigurationCompteRendu extends AbstractConfigurationRegister
{

    /**
     * @return mixed
     */
    public function register()
    {
        CConfiguration::register(
            [
                "CGroups" => [
                    "dPcompteRendu" => [
                        "CCompteRenduPrint" => [
                            "same_print"         => "bool default|1",
                            "time_before_thumbs" => "num default|0 min|0",
                        ],
                        "CCompteRendu"      => [
                            "default_size"           => "enum list|xx-small|x-small|small|medium|large|x-large|xx-large|8pt|9pt|10pt|11pt|12pt|14pt|16pt|18pt|20pt|22pt|24pt|26pt|28pt|36pt|48pt|72pt default|small localize",
                            "header_footer_fly"      => "bool default|0",
                            "dompdf_host"            => "bool default|0",
                            "unlock_doc"             => "bool default|1",
                            "shrink_pdf"             => "enum list|0|1|2 default|0 localize",
                            "purge_lifetime"         => "num default|1000 min|0",
                            "purge_limit"            => "num default|100 min|0",
                            "private_owner_func"     => "enum list|function|owner default|function localize",
                            "probability_regenerate" => "num default|100 min|0",
                            "days_to_lock"           => "num default|30",
                            'cancel_and_replace'     => 'str',
                        ],
                        "CCompteRenduAcces" => [
                            "access_group"    => "bool default|1",
                            "access_function" => "bool default|1",
                        ],
                        "CAideSaisie"       => [
                            "access_group"    => "bool default|1",
                            "access_function" => "bool default|1",
                        ],
                        "CListeChoix"       => [
                            "access_group"    => "bool default|1",
                            "access_function" => "bool default|1",
                        ],
                    ],
                ],
            ]
        );
    }
}
