<?php
/**
 * @package Mediboard\Sa
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Sa;

use Ox\Core\Handlers\HandlerParameterBag;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * @codeCoverageIgnore
 */
class CConfigurationSa extends AbstractConfigurationRegister
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        $value = CSejour::$types;
        $value = implode("|", $value);

        CConfiguration::register(
            [
                "CGroups" => [
                    "sa" => [
                        "CSa" => [
                            "trigger_sejour"              => "enum list|facture|sortie_reelle|testCloture localize",
                            "trigger_operation"           => "enum list|facture|testCloture|sortie_reelle localize",
                            "trigger_consultation"        => "enum list|valide|facture|sortie_reelle localize",
                            "send_actes_consult"          => "bool default|0",
                            "send_actes_interv"           => "bool default|0",
                            "send_only_with_ipp_nda"      => "bool default|0",
                            "send_only_with_type"         => "enum list||$value",
                            "send_diags_with_actes"       => "bool default|0",
                            "facture_codable_with_sejour" => "bool default|0",
                            "send_rhs"                    => "bool default|0",
                            "send_acte_immediately"       => "bool default|0",
                            "send_igs_immediately"        => "bool default|0",
                            "send_diag_immediately"       => "bool default|0",
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
            ->register(CSaEventObjectHandler::class, false)
            ->register(CSaObjectHandler::class, false);
    }
}
