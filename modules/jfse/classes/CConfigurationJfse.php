<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse;

use Ox\Core\Handlers\HandlerParameterBag;
use Ox\Mediboard\Jfse\Domain\MedicalAct\JfseActHandler;
use Ox\Mediboard\Jfse\Domain\Vital\JfseCPatientHandler;
use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * Jfse Configurations
 */
final class CConfigurationJfse extends AbstractConfigurationRegister
{
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        CConfiguration::register(
            [
                'CGroups' => [
                    'jfse' => [
                        'API'     => [
                            'editorName'  => 'str',
                        ],
                        'General' => [
                            'mode' => 'enum list|hidden|gui default|hidden localize',
                            'apcv' => 'bool default|0',
                            'apcv_date' => 'date default|2023-01-01',
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
            ->register(JfseCPatientHandler::class, true)
            ->register(JfseActHandler::class, true)
            ->register(JfseApCvConfigurationHandler::class, true);
    }
}
