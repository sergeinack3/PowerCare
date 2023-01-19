<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation;

use Ox\Core\CAppUI;
use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * Class CConfigurationFacturation
 */
class CConfigurationFacturation extends AbstractConfigurationRegister
{

    /**
     * @return mixed
     */
    public function register()
    {
        $configurations_group = [
            "CFactureEtablissement" => [
                "use_temporary_bill" => "bool default|0",
                "use_auto_cloture"   => "bool default|0",
                "view_bill"          => "bool default|1",
            ],
            "CFactureCabinet"       => [
                "use_auto_cloture" => "bool default|1",
                "view_bill"        => "bool default|1",
            ],
            "CReglement"            => [
                "use_debiteur"                => "bool default|0",
                "add_pay_not_close"           => "bool default|0",
                "use_lock_acquittement"       => "bool default|0",
                "use_mode_default"            => "enum list|none|cheque|CB|especes|virement|BVR|autre default|none localize",
                "use_echeancier"              => "bool default|0",
                "echeancier_default_nb_month" => "num default|5 min|1",
                "echeancier_default_interest" => "numchar default|5",
            ],
            "CRetrocession"         => [
                "use_retrocessions" => "bool default|0",
            ],
            "CJournalBill"          => [
                "use_journaux" => "bool default|0",
            ],
            "Other"                 => [
                "use_strict_cloture" => "bool default|0",
            ],
            "CRelance"              => [
                "use_relances"             => "bool default|0",
                "nb_days_first_relance"    => "num default|30 min|1",
                "nb_days_second_relance"   => "num default|60 min|1",
                "nb_days_third_relance"    => "num default|90 min|1",
                "add_first_relance"        => "num default|0",
                "add_second_relance"       => "num default|0",
                "add_third_relance"        => "num default|0",
                "nb_generate_pdf_relance"  => "num default|20",
                "message_relance1_patient" => "text",
                "message_relance2_patient" => "text",
                "message_relance3_patient" => "text",
            ],
        ];

        CConfiguration::register(
            [
                "CGroups"                     => [
                    "dPfacturation" => $configurations_group,
                ],
                'CFunctions CGroups.group_id' => [
                    "dPfacturation" => [
                        "CFactureCategory" => [
                            "use_category_bill"      => "enum list|hide|optionnal|obligatory default|hide localize",
                            "decalage_right_num_bvr" => "num min|-10 max|10",
                        ],
                    ],
                ],
            ]
        );
    }
}
