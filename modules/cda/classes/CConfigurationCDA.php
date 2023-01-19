<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda;

use Ox\Interop\Eai\ConfigurationActorInterface;
use Ox\Interop\Eai\Repository\PatientRepository;
use Ox\Mediboard\System\AbstractConfigurationRegister;

/**
 * @codeCoverageIgnore
 */
class CConfigurationCDA extends AbstractConfigurationRegister implements ConfigurationActorInterface
{
    /**
     * @inheritDoc
     */
    public function register()
    {
    }

    /**
     * @return string[]
     */
    public function getSuffixesSectionActor(): array
    {
        return ['cda'];
    }

    /**
     * Configurations for object actor
     *
     * @return array
     */
    public function getConfigurationsActor(): array
    {
        return array_merge($this->getConfigurationsSender(), $this->getConfigurationsReceiver());
    }

    /**
     * Configurations for Receiver object
     *
     * @return array
     */
    private function getConfigurationsReceiver(): array
    {
        return [];
    }

    /**
     * Configurations for sender object
     *
     * @return array
     */
    private function getConfigurationsSender(): array
    {
        $common_configs = [
            'handle-cda' => [
                'handle_patient'          => 'bool default|0',
                'search_patient_strategy' => 'enum list|'
                    . implode('|', PatientRepository::STRATEGIES)
                    . ' default|' . PatientRepository::STRATEGY_BEST
                    . ' localize',
            ],
        ];

        return [
            'CSenderSOAP'       => $common_configs,
            'CSenderSFTP'       => $common_configs,
            'CSenderFTP'        => $common_configs,
            'CSenderHTTP'       => $common_configs,
            'CSenderFileSystem' => $common_configs,
            'CSenderMLLP'       => $common_configs,
        ];
    }
}
