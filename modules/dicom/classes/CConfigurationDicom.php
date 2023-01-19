<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Dicom;

use Ox\Interop\Eai\ConfigurationActorInterface;
use Ox\Mediboard\System\AbstractConfigurationRegister;

/**
 * @codeCoverageIgnore
 */
class CConfigurationDicom extends AbstractConfigurationRegister implements ConfigurationActorInterface
{
    /**
     * @return void
     */
    public function register(): void
    {
    }

    /**
     * @return string[]
     */
    public function getSuffixesSectionActor(): array
    {
        return ['dicom'];
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
        $config_dicom = [
            'fields-dicom' => [
                'send_0032_1032'      => 'bool default|0',
                'physician_separator' => 'str',
            ],
            'values-dicom' => [
                'value_0008_0060' => 'str',
                'uid_0020_000d'   => 'bool default|1',
            ],
        ];

        return [
            'CDicomSender' => $config_dicom,
        ];
    }
}
