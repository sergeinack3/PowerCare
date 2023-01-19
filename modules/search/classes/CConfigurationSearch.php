<?php
/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Search;

use Ox\Core\Handlers\HandlerParameterBag;
use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * @codeCoverageIgnore
 */
class CConfigurationSearch extends AbstractConfigurationRegister
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        CConfiguration::register(
            [
                "CGroups" => [
                    "search" => [
                        "active_handler" => [
                            "active_handler_search" => "bool default|0",
                            "active_handler_search_types" => "set list|CCompteRendu|CTransmissionMedicale|CObservationMedicale|CConsultation|CConsultAnesth|CFile|CExObject|CPrescriptionLineElement|CPrescriptionLineMix|CPrescriptionLineMedicament|COperation|CDossierMedical default|",
                        ],
                        "indexing"       => [
                            "active_indexing" => "bool default|0",
                        ],
                        "history"        => [
                            "active_search_history" => "bool default|1",
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
            ->register(CSearchObjectHandler::class, false);
    }
}
