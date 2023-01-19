<?php

/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante;

use Ox\Interop\Eai\ConfigurationActorInterface;
use Ox\Interop\Eai\Manager\FileManager;
use Ox\Interop\Eai\Repository\PatientRepository;
use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * @codeCoverageIgnore
 */
class CConfigurationHPrimSante extends AbstractConfigurationRegister implements ConfigurationActorInterface
{
    /**
     * @return void
     */
    public function register(): void
    {
        CConfiguration::register(
            [
                "CGroups" => [
                    "hprimsante" => [
                        "search_interval" => [
                            "search_min_admit" => "num default|1 min|0",
                            "search_max_admit" => "num default|1 min|0",
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getSuffixesSectionActor(): array
    {
        return ['hprimsante'];
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
        $config_hprim_sante = [
            'receiver-ADM-hprimsante' => [
                'ADM_version' => 'enum list|2.1|2.2|2.3|2.4 default|2.1',
                'ADM_sous_type' => 'enum list|C|L|R default|C'
            ]
        ];

        return [
            'CReceiverHprimSante' => $config_hprim_sante
        ];
    }

    /**
     * Configurations for sender object
     *
     * @return array
     */
    private function getConfigurationsSender(): array
    {
        $common_configs = [
            "format-hprimsante" => [
                "strict_segment_terminator" => 'bool default|0',
                "segment_terminator"        => 'enum list|CR|LF|CRLF',
            ],
            "handle-hprimsante" => [
                "action"                  => 'enum list|IPP_NDA|Patient|Sejour|Patient_Sejour default|IPP_NDA localize',
                "notifier_entree_reelle"  => 'bool default|1',
                'search_patient_strategy' => 'enum list|'
                    . implode('|', PatientRepository::STRATEGIES)
                    . ' default|' . PatientRepository::STRATEGY_BEST
                    . ' localize',
            ],
            "hande-oru-hprimsante" => [
                'handle_patient_ORU'                => "bool default|0",
                'associate_category_to_a_file'      => "bool default|0",
                'define_name'                       => 'enum list|'
                    . implode('|', FileManager::STRATEGIES_FILENAME)
                    . ' default|' . FileManager::STRATEGY_FILENAME_DEFAULT
                    . ' localize',
                'id_category_patient'               => 'num',
                'object_attach_OBX'                 => 'enum list|CPatient|CSejour|COperation|CMbObject|CFilesCategory default|CMbObject localize',
                'mode_sas'                          => 'bool default|0',
                'creation_date_file_like_treatment' => 'bool default|0',
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
