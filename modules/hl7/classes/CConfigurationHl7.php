<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use Ox\Interop\Eai\ConfigurationActorInterface;
use Ox\Interop\Eai\Manager\FileManager;
use Ox\Interop\Eai\Repository\PatientRepository;
use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * @codeCoverageIgnore
 */
class CConfigurationHl7 extends AbstractConfigurationRegister implements ConfigurationActorInterface
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        CConfiguration::register(
            [
                "CGroups" => [
                    "hl7" => [
                        "CHL7" => [
                            "sending_application"                   => "str",
                            "sending_facility"                      => "str",
                            "assigning_authority_namespace_id"      => "str",
                            "assigning_authority_universal_id"      => "str",
                            "assigning_authority_universal_type_id" => "str",
                        ],
                        "ORU"  => [
                            "handle_file_name" => "str",
                            "verify_OBR_18"    => "bool default|1",
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
        return ['HL7', 'HL7v3', 'HL7v2'];
    }

    /**
     * Configurations for actor objects
     *
     * @return array
     */
    public function getConfigurationsActor(): array
    {
        return array_merge($this->getConfigurationsSender(), $this->getConfigurationsReceiver());
    }

    /**
     * Configurations for sender object
     *
     * @return string[][][]
     */
    private function getConfigurationsSender(): array
    {
        $common_configs = [
            'format-HL7' => [
                'strict_segment_terminator' => 'bool default|0',
                'segment_terminator'        => 'enum list|CR|LF|CRLF',
                'ack_severity_mode'         => 'enum list|IWE|E|W|I default|IWE localize',
                'ignore_fields'             => 'str',
                'bypass_validating'         => 'bool default|0',
            ],

            'application-HL7' => [
                'check_receiving_application_facility'  => 'bool default|0',
                'receiving_application'                 => 'str',
                'receiving_facility'                    => 'str',
                'assigning_authority_namespace_id'      => 'str',
                'assigning_authority_universal_id'      => 'str',
                'assigning_authority_universal_type_id' => 'str',
                'country_code'                          => 'enum list|FRA|INT',
            ],

            'actor-options-HL7' => [
                'iti30_option_merge'                        => 'bool default|1',
                'iti30_option_link_unlink'                  => 'bool default|0',
                'iti31_in_outpatient_management'            => 'bool default|1',
                'iti31_pending_event_management'            => 'bool default|0',
                'iti31_advanced_encounter_management'       => 'bool default|1',
                'iti31_temporary_patient_transfer_tracking' => 'bool default|0',
                'iti31_historic_movement'                   => 'bool default|1',
            ],

            'identifiers-HL7' => [
                'control_identifier_type_code'      => 'bool default|1',
                'handle_mode'                       => 'enum list|normal|simple default|normal localize',
                'search_master_IPP'                 => 'bool default|0',
                'search_master_NDA'                 => 'bool default|0',
                'handle_NDA'                        => 'enum list|PID_18|PV1_19 default|PID_18',
                'manage_npa'                        => 'bool default|0',
                'ins_integrated'                    => 'bool default|0',
                'retrieve_all_PI_identifiers'       => 'bool default|0',
                'generate_IPP_unqualified_identity' => 'bool default|1',
            ],

            'handle-HL7' => [
                'unqualified_identity'    => 'bool default|0',
                'check_similar_patient'   => 'bool default|1',
                'search_patient'          => 'bool default|1',
                'search_patient_strategy' => 'enum list|'
                    . implode('|', PatientRepository::STRATEGIES)
                    . ' default|' . PatientRepository::STRATEGY_BEST
                    . ' localize',
            ],

            'handle-pam-HL7' => [
                'handle_patient_ITI_31' => 'bool default|1',
                'ignore_admit_with_field' => 'str',
                'create_duplicate_patient' => 'bool default|0',
                'handle_telephone_number' => 'enum list|XTN_1|XTN_12 default|XTN_12',
                'handle_PID_31' => 'enum list|avs|none default|none localize',
                'handle_PV1_3' => 'enum list|name|config_value|idex default|name localize',
                'handle_PV1_3_null' => 'str',
                'handle_PV1_4' => 'enum list|normal|charge_price_indicator default|normal localize',
                'handle_PV1_7' => 'bool default|1 localize',
                'handle_PV1_8' => 'enum list|adressant|traitant default|adressant localize',
                'handle_PV1_9' => 'enum list|null|famille default|null localize',
                'handle_PV1_10' => 'enum list|discipline|service|finess default|discipline localize',
                'handle_PV1_14' => 'enum list|admit_source|ZFM default|admit_source localize',
                'handle_PV1_20' => 'enum list|old_presta|none default|none localize',
                'handle_PV1_36' => 'enum list|discharge_disposition|ZFM default|discharge_disposition localize',
                'handle_PV1_50' => 'enum list|sejour_id|none default|none localize',
                'handle_PV2_12' => 'enum list|libelle|none default|libelle localize',
                'handle_NSS' => 'enum list|PID_3|PID_19 default|PID_3',
                'handle_ZBE_7' => 'enum list|medicale|soins default|medicale localize',
                'handle_ZBE_8' => 'enum list|medicale|soins default|soins localize',
                'handle_OBX_photo_patient' => 'bool default|0',
                'create_grossesse' => 'bool default|1',
                'exclude_not_collide_exte' => 'bool default|0',
                'ignore_the_patient_with_an_unauthorized_IPP' => 'bool default|0',
            ],

            'handle-orm-HL7' => [
                'change_filler_placer' => 'bool default|0',
            ],

            'handle-oru-HL7' => [
                'handle_OBR_identity_identifier'    => 'str',
                'change_OBX_5'                      => 'str',
                'associate_category_to_a_file'      => 'bool default|0',
                'handle_context_url'                => 'str',
                'define_name'                       => 'enum list|'
                    . implode('|', FileManager::STRATEGIES_FILENAME)
                    . ' default|' . FileManager::STRATEGY_FILENAME_DEFAULT
                    . ' localize',
                'control_nda_target_document'       => 'bool default|1',
                'id_category_patient'               => 'num',
                'object_attach_OBX'                 => 'enum list|CPatient|CSejour|COperation|CMbObject|CFilesCategory default|CMbObject localize',
                'mode_sas'                          => 'bool default|0',
                'creation_date_file_like_treatment' => 'bool default|0',
                'handle_patient_ORU'                => 'bool default|0',
            ],

            'handle-siu-HL7' => [
                'handle_SIU_object'  => 'enum list|consultation|intervention default|consultation localize',
                'handle_patient_SIU' => 'bool default|0',
            ],

            'handle-mfn-HL7' => [
                'handle_IIM_6' => 'enum list|group|service|sejour localize',
            ],

            'send-HL7' => [
                'send_assigning_authority' => 'bool default|1',
                'send_self_identifier'     => 'bool default|0',
                'send_area_local_number'   => 'bool default|0',
            ],

            'purge-HL7' => [
                'purge_idex_movements' => 'bool default|0',
            ],

            'auto-repair-HL7' => [
                'repair_patient' => 'bool default|1',
                'control_date'   => 'enum list|permissif|strict default|strict localize',
            ],

            'appFine-HL7' => [
                'create_user_to_patient' => 'bool default|0',
                'handle_portail_patient' => 'bool default|0',
            ],

            'doctolib-HL7' => [
                'handle_doctolib' => 'bool default|0',
            ],

            'TAMM-SIH-HL7' => [
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
        $config_hl7v2 = [
            'receiver-format-HL7v2' => [
                'encoding'               => 'enum list|UTF-8|ISO-8859-1 default|UTF-8',
                'ER7_segment_terminator' => 'enum list|CR|LF|CRLF',
            ],

            'receiver-version-HL7v2' => [
                // HL7
                'HL7_version'       => 'enum list|2.1|2.2|2.3|2.3.1|2.4|2.5|2.5.1|2.6|2.7|2.7.1|2.8|2.8.1|2.8.2 default|2.5',

                // PAM
                'ITI30_HL7_version' => 'enum list|2.1|2.2|2.3|2.3.1|2.4|2.5|2.5.1|2.6|2.7|2.7.1|2.8|2.8.1|2.8.2|FRA_2.3|FRA_2.4|FRA_2.5|FRA_2.6|FRA_2.7|FRA_2.8|FRA_2.9|FRA_2.10 default|2.5',
                'ITI31_HL7_version' => 'enum list|2.1|2.2|2.3|2.3.1|2.4|2.5|2.5.1|2.6|2.7|2.7.1|2.8|2.8.1|2.8.2|FRA_2.3|FRA_2.4|FRA_2.5|FRA_2.6|FRA_2.7|FRA_2.8|FRA_2.9|FRA_2.10 default|2.5',

                // SWF
                'RAD3_HL7_version'  => 'enum list|2.1|2.2|2.3|2.3.1|2.4|2.5|2.5.1|2.6|2.7|2.7.1|2.8|2.8.1|2.8.2 default|2.5',
                'RAD28_HL7_version' => 'enum list|2.1|2.2|2.3|2.3.1|2.4|2.5|2.5.1|2.6|2.7|2.7.1|2.8|2.8.1|2.8.2 default|2.5',
                'RAD48_HL7_version' => 'enum list|2.1|2.2|2.3|2.3.1|2.4|2.5|2.5.1|2.6|2.7|2.7.1|2.8|2.8.1|2.8.2 default|2.5',

                // PDQ
                'ITI21_HL7_version' => 'enum list|2.1|2.2|2.3|2.3.1|2.4|2.5|2.5.1|2.6|2.7|2.7.1|2.8|2.8.1|2.8.2 default|2.5',
                'ITI22_HL7_version' => 'enum list|2.1|2.2|2.3|2.3.1|2.4|2.5|2.5.1|2.6|2.7|2.7.1|2.8|2.8.1|2.8.2 default|2.5',

                // PIX
                'ITI9_HL7_version'  => 'enum list|2.1|2.2|2.3|2.3.1|2.4|2.5|2.5.1|2.6|2.7|2.7.1|2.8|2.8.1|2.8.2 default|2.5',
            ],

            'receiver-application-HL7v2' => [
                'receiving_application'                 => 'str',
                'receiving_facility'                    => 'str',
                'assigning_authority_namespace_id'      => 'str',
                'assigning_authority_universal_id'      => 'str',
                'assigning_authority_universal_type_id' => 'str',
                'country_code'                          => 'enum list|FRA|INT',
            ],

            'receiver-actor-options-HL7v2' => [
                'iti30_option_merge'                        => 'str',
                'iti30_option_link_unlink'                  => 'str',
                'iti31_in_outpatient_management'            => 'str',
                'iti31_pending_event_management'            => 'str',
                'iti31_advanced_encounter_management'       => 'str',
                'iti31_temporary_patient_transfer_tracking' => 'str',
                'iti31_historic_movement'                   => 'str',
            ],

            'receiver-build-HL7v2'            => [
                'build_mode'                   => 'enum list|normal|simple default|normal localize',
                'build_empty_fields'           => 'enum list|yes|no|restricted default|no localize',
                'fields_allowed_to_empty'      => 'str',
                'build_NDA'                    => 'enum list|PID_18|PV1_19 default|PID_18',
                'build_telephone_number'       => 'enum list|XTN_1|XTN_12 default|XTN_12',
                'build_cellular_phone'         => 'enum list|PRN|ORN default|PRN localize',
                'build_other_residence_number' => 'enum list|ORN|WPN default|ORN localize',
                'build_identifier_authority'   => 'enum list|normal|PI_AN default|normal localize',
                'build_fields_format'          => 'enum list|normal|uppercase localize',
                'check_field_length'           => 'bool default|1',
            ],

            // choix des événements
            'receiver-send-event-HL7v2'       => [
                'modification_admit_code'            => 'enum list|A08|Z99 default|Z99',
                'send_change_medical_responsibility' => 'enum list|A02|Z80|Z99 default|Z80',
                'send_change_nursing_ward'           => 'enum list|A02|Z84|Z99 default|Z84',
                'send_change_attending_doctor'       => 'enum list|A54|Z99 default|A54',
                'send_first_affectation'             => 'enum list|A02|Z99 default|Z99',
                'send_transfer_patient'              => 'enum list|A02|Z99 default|A02',
                'send_update_patient_information'    => 'enum list|A08|A31 default|A31',
            ],

            // identifiants
            'receiver-send-identifier-HL7v2'  => [
                'send_assigning_authority' => 'bool default|1',
                'send_own_identifier'      => 'bool default|1',
                'send_self_identifier'     => 'bool default|0',
                'send_actor_identifier'    => 'bool default|0',
                'send_not_master_IPP'      => 'bool default|1',
                'send_not_master_NDA'      => 'bool default|1',
                'change_idex_for_admit'    => 'bool default|0',
            ],

            // trigger
            'receiver-send-trigger-HL7v2'     => [
                'modification_before_admit'                => 'bool default|1',
                'send_all_patients'                        => 'bool default|0',
                'send_default_affectation'                 => 'bool default|0',
                'send_provisional_affectation'             => 'bool default|0',
                'send_change_after_admit'                  => 'bool default|1',
                'send_patient_with_visit'                  => 'bool default|0',
                'send_patient_with_current_admit'          => 'bool default|0',
                'mode_identito_vigilance'                  => 'enum list|light|medium|strict default|light localize',
                'send_no_facturable'                       => 'enum list|0|1|2 default|1 localize',
                'send_a42_onmerge'                         => 'bool default|0',
                'send_expected_discharge_with_affectation' => 'bool default|1',
                'send_child_admit'                         => 'bool default|1',
                'send_insurance'                           => 'bool default|0',
                'send_not_price_indicator_for_birth'       => 'bool default|1',
                'exclude_admit_type'                       => 'str',
                'files_mode_sas'                           => 'bool default|0',
            ],

            // AppFine
            'receiver-send-appFine-HL7v2'     => [
                'send_evenement_to_mbdmp' => 'bool default|0',
            ],

            // SIH-Cabinet
            'receiver-send-sih-cabinet-HL7v2' => [
                'sih_cabinet_id' => 'num',
                'cabinet_sih_id' => 'num',
            ],


            'receiver-PID-HL7v2' => [
                'build_PID_3_4' => 'enum list|finess|actor|domain default|finess localize',
                'build_PID_5_2' => 'bool default|0',
                'build_PID_6'   => 'enum list|nom_naissance|none default|none localize',
                'build_PID_11'  => 'enum list|simple|multiple default|multiple localize',
                'build_PID_13'  => 'enum list|simple|multiple default|multiple localize',
                'build_PID_18'  => 'enum list|normal|simple|sejour_id|none default|normal localize',
                'build_PID_19'  => 'enum list|matricule|none default|none localize',
                'build_PID_31'  => 'enum list|avs|none default|none localize',
            ],

            'receiver-PV1-HL7v2' => [
                'build_PV1_3_1'                     => 'enum list|UF|service default|UF localize',
                'build_PV1_3_1_default'             => 'str',
                'build_PV1_3_2'                     => 'enum list|name|config_value|idex default|name localize',
                'build_PV1_3_3'                     => 'enum list|name|config_value|idex default|name localize',
                'build_PV1_3_5'                     => 'enum list|bed_status|null default|bed_status localize',
                'build_PV1_4'                       => 'enum list|normal|charge_price_indicator default|normal localize',
                'build_PV1_5'                       => 'enum list|NPA|sejour_id|none default|none localize',
                'build_PV1_7'                       => 'enum list|unique|repeatable default|unique localize',
                'build_PV1_10'                      => 'enum list|discipline|service|finess default|discipline localize',
                'build_PV1_11'                      => 'enum list|uf_medicale|none default|none localize',
                'build_PV1_14'                      => 'enum list|admit_source|ZFM default|admit_source localize',
                'build_PV1_17'                      => 'enum list|praticien|none default|praticien localize',
                'build_PV1_19'                      => 'enum list|normal|simple default|normal localize',
                'build_PV1_19_identifier_authority' => 'enum list|AN|RI|VN default|RI',
                'build_PV1_19_idex_tag'             => 'str',
                'build_PV1_26'                      => 'enum list|movement_id|none default|none localize',
                'build_PV1_36'                      => 'enum list|discharge_disposition|ZFM default|discharge_disposition localize',
            ],

            'receiver-PV2-HL7v2' => [
                'build_PV2_45' => 'enum list|operation|none default|none localize',
            ],

            'receiver-ZBE-HL7v2' => [
                'build_ZBE_7' => 'enum list|medicale|soins default|medicale localize',
                'build_ZBE_8' => 'enum list|medicale|soins default|soins localize',
            ],

            'receiver-OBX-HL7v2' => [
                'build_OBX_2' => 'enum list|AD|CE|CF|CK|CN|CP|CX|DT|ED|FT|MO|NM|PN|RP|SN|ST|TM|TN|TS|TX|XAD|XCN|XON|XPN|XTN default|ED localize',
                'build_OBX_3' => 'enum list|DMP|INTERNE default|INTERNE localize',
            ],

            'receiver-NTE-HL7v2' => [
                'send_NTE' => 'bool default|1',
            ],
        ];

        $config_hl7v3 = [
            'receiver-build-HL7v3' => [
                'use_receiver_oid' => 'bool default|0',
            ],
        ];

        return [
            'CReceiverHL7v2' => $config_hl7v2,
            'CReceiverHL7v3' => $config_hl7v3,
        ];
    }
}
