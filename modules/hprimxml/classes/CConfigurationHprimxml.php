<?php

/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Ox\Interop\Eai\ConfigurationActorInterface;
use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * Class CConfigurationHprimxml
 *
 * @codeCoverageIgnore
 */
class CConfigurationHprimxml extends AbstractConfigurationRegister implements ConfigurationActorInterface
{
    /**
     * @return void
     */
    public function register(): void
    {
        CConfiguration::register(
            [
                "CGroups" => [
                    "hprimxml" => [
                        "CHPrimXMLDocument" => [
                            "emetteur_application_code"    => "str default|Mediboard",
                            "emetteur_application_libelle" => "str default|Mediboard SIH",
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
        return ['hprimxml'];
    }

    /**
     * Get configurations actor object
     *
     * @return array
     */
    public function getConfigurationsActor(): array
    {
        return array_merge($this->getConfigurationsSender(), $this->getConfigurationsReceiver());
    }

    /**
     * Get configurations for sender object
     *
     * @return string[][][]
     */
    private function getConfigurationsSender(): array
    {
        $common_configs = [
            // Format
            'format-hprimxml'      => [
                'display_errors' => 'bool default|1',
            ],

            // Handle
            'handle-hprimxml'      => [
                'use_sortie_matching'      => 'bool default|1',
                'fully_qualified'          => 'bool default|1',
                'check_similar'            => 'bool default|0',
                'att_system'               => 'enum list|acteur|application|système|finessgeographique|finessjuridique default|système localize',
                'insc_integrated'          => 'bool default|0',
                'frais_divers'             => 'enum list|fd|presta default|fd localize',
                'prestation'               => 'enum list|nom|idex default|nom localize',
                'force_birth_rank_if_null' => 'bool default|0',
            ],

            // Digit
            'digit-hprimxml'       => [
                'type_sej_hospi'   => 'str',
                'type_sej_ambu'    => 'str',
                'type_sej_urg'     => 'str',
                'type_sej_exte'    => 'str',
                'type_sej_scanner' => 'str',
                'type_sej_chimio'  => 'str',
                'type_sej_dialyse' => 'str',
                'type_sej_pa'      => 'str',
            ],

            // Purge
            'purge-hprimxml'       => [
                'purge_idex_movements' => 'bool default|0',
            ],

            // Repair
            'auto-repair-hprimxml' => [
                'repair_patient' => 'bool default|1',
            ],

            // AppFine
            'appFine-hprimxml'     => [
                'handle_appFine' => 'bool default|0',
            ],

            'TAMM-SIH-hprimxml' => [
                'handle_tamm_sih' => 'str',
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

    /**
     * Configurations for receiver object
     *
     * @return array
     */
    private function getConfigurationsReceiver(): array
    {
        return [];
    }
}
