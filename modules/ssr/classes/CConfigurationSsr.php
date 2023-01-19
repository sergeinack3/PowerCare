<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\Handlers\HandlerParameterBag;
use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * @codeCoverageIgnore
 */
class CConfigurationSsr extends AbstractConfigurationRegister
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        CConfiguration::register(
            [
                "CGroups" => [
                    "ssr" => [
                        "seance_collective" => [
                            "nb_patient_interv_auto" => "bool default|0",
                        ],
                        "validation"        => [
                            "validation_actes_futur" => "bool default|1",
                        ],
                        "soins"             => [
                            "generation_plan_soins" => "bool default|1",
                            "delete_evts_line_stop" => "bool default|0",
                        ],
                        "general"           => [
                            "modifiy_evt_everybody"     => "bool default|0",
                            "create_evt_user_can_edit"  => "bool default|0",
                            "hide_reeduc_evts_sejour"   => "bool default|0",
                            "as_can_view_planif"        => "bool default|0",
                            "see_name_element_planning" => "bool default|0",
                            "use_acte_presta"           => "enum list|aucun|csarr|presta default|csarr localize",
                            "lock_add_evt_conflit"      => "bool default|0",
                            "see_collective_planif_psy" => "bool default|1",
                        ],
                        "print_week"        => [
                            "see_contrat"              => "bool default|1",
                            "print_paysage_sejour_ssr" => "bool default|0",
                            "new_format_pdf"           => "bool default|0",
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getObjectHandlers(HandlerParameterBag $parameter_bag): void
    {
        $parameter_bag
            ->register(CEvenementSSRHandler::class, true);
    }
}

