<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir;

use Exception;
use Ox\Interop\Eai\ConfigurationActorInterface;
use Ox\Interop\Eai\Manager\FileManager;
use Ox\Interop\Eai\Repository\PatientRepository;
use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;
use Ox\Mediboard\System\ConfigurationManager;

/**
 * @codeCoverageIgnore
 */
class CConfigurationFHIR extends AbstractConfigurationRegister implements ConfigurationActorInterface
{
    public function register(): void
    {
        CConfiguration::register(
            [
                "CGroups" => [
                    'fhir' => [],
                ],
                "CMessageSupported" => [
                    'fhir' => [
                        'delegated_objects' =>
                        [
                            'delegated_mapper'   => "custom tpl|inc_config_fhir_list_object_delegated object_type|" . CExchangeFHIR::DELEGATED_OBJECT_MAPPER,
                            'delegated_handle'   => "custom tpl|inc_config_fhir_list_object_delegated object_type|" . CExchangeFHIR::DELEGATED_OBJECT_HANDLE ,
                            'delegated_searcher' => "custom tpl|inc_config_fhir_list_object_delegated object_type|" . CExchangeFHIR::DELEGATED_OBJECT_SEARCHER,
                        ]
                    ]
                ]
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getSuffixesSectionActor(): array
    {
        return ['fhir'];
    }

    /**
     * @param ConfigurationManager $manager
     *
     * @throws Exception
     */
    public function registerStatic(ConfigurationManager $manager): void
    {
        $manager->registerStatic(
            [
                'general' => [
                    'version' => 'enum list|4.0|4.1 default|4.0',
                ],
            ]
        );
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
        $config_fhir = [
            'receiver-identifiers-fhir' => [
                'encode_identifiers' => 'bool default|1',
            ],
        ];

        return [
            'CReceiverFHIR' => $config_fhir,
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
            'identifiers-fhir' => [
                'encode_identifiers' => 'bool default|1',
            ],

            'handle-patient-fhir' => [
                'search_patient_strategy' => 'enum list|'
                    . implode('|', PatientRepository::STRATEGIES)
                    . ' default|' . PatientRepository::STRATEGY_BEST
                    . ' localize',
            ],

            'handle-document-fhir' => [
                'associate_category_to_a_file'      => "bool default|0",
                'define_name'                       => 'enum list|'
                    . implode('|', FileManager::STRATEGIES_FILENAME)
                    . ' default|' . FileManager::STRATEGY_FILENAME_DEFAULT
                    . ' localize',
                'id_category_patient'               => 'num',
                'object_attach'                     => 'enum list|CPatient|CSejour|COperation|CMbObject|CFilesCategory default|CMbObject localize',
                'mode_sas'                          => 'bool default|0',
                'creation_date_file_like_treatment' => 'bool default|0',
            ]
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
