<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use Ox\Interop\Eai\CExchangeDataFormatConfig;
use Ox\Interop\Eai\Manager\FileManager;
use Ox\Interop\Eai\Repository\PatientRepository;

/**
 * Class CHL7Config
 * Config HL7
 */
class CHL7Config extends CExchangeDataFormatConfig
{
    /**
     * @var array Config fields
     */
    static $config_fields = [
        // Options
        // => ITI-30
        'iti30_option_merge',
        'iti30_option_link_unlink',
        // => ITI-31
        'iti31_in_outpatient_management',
        'iti31_pending_event_management',
        'iti31_advanced_encounter_management',
        'iti31_temporary_patient_transfer_tracking',
        'iti31_historic_movement',

        // Assigning authority
        'check_receiving_application_facility',
        'receiving_application',
        'receiving_facility',
        'assigning_authority_namespace_id',
        'assigning_authority_universal_id',
        'assigning_authority_universal_type_id',
        'country_code',

        // Format
        'strict_segment_terminator',
        'segment_terminator',
        'ack_severity_mode',

        // Handle
        'control_identifier_type_code',
        'handle_mode',
        'search_master_IPP',
        'search_master_NDA',
        'retrieve_all_PI_identifiers',
        'handle_NDA',
        'manage_npa',
        'ins_integrated',
        'ignore_admit_with_field',
        'ignore_fields',
        'bypass_validating',
        'handle_patient_ITI_31',
        'check_similar_patient',
        'create_duplicate_patient',
        'handle_telephone_number',
        'handle_PID_31',
        'handle_NSS',
        'handle_PV1_3',
        'handle_PV1_3_null',
        'handle_PV1_4',
        'handle_PV1_7',
        'handle_PV1_8',
        'handle_PV1_9',
        'handle_PV1_10',
        'handle_PV1_14',
        'handle_PV1_20',
        'handle_PV1_36',
        'handle_PV1_50',
        'handle_PV2_12',
        'handle_ZBE_7',
        'handle_ZBE_8',
        'handle_IIM_6',
        'handle_OBX_photo_patient',
        'create_grossesse',
        'change_filler_placer',
        'handle_OBR_identity_identifier',
        'change_OBX_5',
        'associate_category_to_a_file',
        'handle_context_url',
        'define_name',
        'control_nda_target_document',
        'exclude_not_collide_exte',
        'object_attach_OBX',
        'id_category_patient',
        'mode_sas',
        'unqualified_identity',
        'handle_patient_SIU',
        'handle_patient_ORU',
        'search_patient_strategy',
        'handle_SIU_object',
        'generate_IPP_unqualified_identity',
        'search_patient',
        'creation_date_file_like_treatment',
        'ignore_the_patient_with_an_unauthorized_IPP',

        // Send
        'send_assigning_authority',
        'send_self_identifier',
        'send_area_local_number',

        // Purge
        'purge_idex_movements',

        // Auto repair
        'repair_patient',
        'control_date',

        // AppFine
        'create_user_to_patient',
        'handle_portail_patient',

        // Doctolib
        'handle_doctolib',

        // TAMM SIH
        'handle_tamm_sih',
    ];

    public $hl7_config_id;

    // Actor Options
    public $iti30_option_merge;
    public $iti30_option_link_unlink;
    public $iti31_in_outpatient_management;
    public $iti31_pending_event_management;
    public $iti31_advanced_encounter_management;
    public $iti31_temporary_patient_transfer_tracking;
    public $iti31_historic_movement;

    // Application
    public $check_receiving_application_facility;
    public $receiving_application;
    public $receiving_facility;
    public $assigning_authority_namespace_id;
    public $assigning_authority_universal_id;
    public $assigning_authority_universal_type_id;
    public $country_code;

    // Format
    public $strict_segment_terminator;
    public $segment_terminator;
    public $ack_severity_mode;

    // Handle
    public $ignore_fields;
    public $ignore_admit_with_field;
    public $bypass_validating;
    public $handle_mode;
    public $handle_NDA;
    public $handle_telephone_number;
    public $handle_PID_31;
    public $handle_PV1_3;
    public $handle_PV1_3_null;
    public $handle_PV1_4;
    public $handle_PV1_7;
    public $handle_PV1_8;
    public $handle_PV1_9;
    public $handle_PV1_10;
    public $handle_PV1_14;
    public $handle_PV1_20;
    public $handle_PV1_36;
    public $handle_PV1_50;
    public $handle_PV2_12;
    public $handle_NSS;
    public $handle_ZBE_7;
    public $handle_ZBE_8;
    public $handle_IIM_6;
    public $handle_OBX_photo_patient;
    public $create_grossesse;
    public $search_master_IPP;
    public $search_master_NDA;
    public $ins_integrated;
    public $manage_npa;
    public $change_filler_placer;
    public $handle_OBR_identity_identifier;
    public $change_OBX_5;
    public $control_identifier_type_code;
    public $associate_category_to_a_file;
    public $handle_patient_ITI_31;
    public $check_similar_patient;
    public $create_duplicate_patient;
    public $handle_context_url;
    public $define_name;
    public $control_nda_target_document;
    public $exclude_not_collide_exte;
    public $object_attach_OBX;
    public $id_category_patient;
    public $mode_sas;
    public $retrieve_all_PI_identifiers;
    public $unqualified_identity;
    public $handle_patient_SIU;
    public $handle_patient_ORU;
    public $search_patient_strategy;
    public $handle_SIU_object;
    public $generate_IPP_unqualified_identity;
    public $search_patient;
    public $creation_date_file_like_treatment;
    public $ignore_the_patient_with_an_unauthorized_IPP;

    // AppFine
    public $create_user_to_patient;
    public $handle_portail_patient;

    // Doctolib
    public $handle_doctolib;

    // TAMM SIH
    public $handle_tamm_sih;

    // Send
    public $send_assigning_authority;
    public $send_self_identifier;
    public $send_area_local_number;

    // Purge
    public $purge_idex_movements;

    // Auto repair
    public $repair_patient;
    public $control_date;

    /**
     * @var array Categories
     */
    public $_categories = [
        'format' => [
            'strict_segment_terminator',
            'segment_terminator',
            'ack_severity_mode',
            'ignore_fields',
            'bypass_validating',
        ],

        'application' => [
            'check_receiving_application_facility',
            'receiving_application',
            'receiving_facility',
            'assigning_authority_namespace_id',
            'assigning_authority_universal_id',
            'assigning_authority_universal_type_id',
            'country_code',
        ],

        'actor options' => [
            'iti30_option_merge',
            'iti30_option_link_unlink',
            'iti31_in_outpatient_management',
            'iti31_pending_event_management',
            'iti31_advanced_encounter_management',
            'iti31_temporary_patient_transfer_tracking',
            'iti31_historic_movement',
        ],

        'identifiers' => [
            'control_identifier_type_code',
            'handle_mode',
            'search_master_IPP',
            'search_master_NDA',
            'handle_NDA',
            'manage_npa',
            'ins_integrated',
            'retrieve_all_PI_identifiers',
            'generate_IPP_unqualified_identity',

        ],

        'handle' => [
            'unqualified_identity',
            'check_similar_patient',
            'search_patient',
        ],

        'handle-pam' => [
            'handle_patient_ITI_31',
            'ignore_the_patient_with_an_unauthorized_IPP',
            'ignore_admit_with_field',
            'create_duplicate_patient',
            'handle_telephone_number',
            'handle_PID_31',
            'handle_PV1_3',
            'handle_PV1_3_null',
            'handle_PV1_4',
            'handle_PV1_7',
            'handle_PV1_8',
            'handle_PV1_9',
            'handle_PV1_10',
            'handle_PV1_14',
            'handle_PV1_20',
            'handle_PV1_36',
            'handle_PV1_50',
            'handle_PV2_12',
            'handle_NSS',
            'handle_ZBE_7',
            'handle_ZBE_8',
            'handle_OBX_photo_patient',
            'create_grossesse',
            'exclude_not_collide_exte',
        ],

        'handle-orm' => [
            'change_filler_placer',
        ],

        'handle-oru' => [
            'search_patient_strategy',
            'handle_OBR_identity_identifier',
            'change_OBX_5',
            'associate_category_to_a_file',
            'handle_context_url',
            'define_name',
            'control_nda_target_document',
            'id_category_patient',
            'object_attach_OBX',
            'mode_sas',
            'creation_date_file_like_treatment',
            'handle_patient_ORU'
        ],

        'handle-siu' => [
            'handle_SIU_object',
            'handle_patient_SIU',
        ],

        'handle-mfn' => [
            'handle_IIM_6',
        ],

        'send' => [
            'send_assigning_authority',
            'send_self_identifier',
            'send_area_local_number',
        ],

        'purge' => [
            'purge_idex_movements',
        ],

        'auto-repair' => [
            'repair_patient',
            'control_date',
        ],

        'appFine' => [
            'create_user_to_patient',
            'handle_portail_patient',
        ],

        'doctolib' => [
            'handle_doctolib',
        ],

        'TAMM-SIH' => [
            'handle_tamm_sih',
        ],
    ];

    /**
     * @inheritDoc
     */
    function getSpec()
    {
        $spec = parent::getSpec();

        $spec->table              = 'hl7_config';
        $spec->key                = 'hl7_config_id';
        $spec->uniques['uniques'] = ['sender_id', 'sender_class'];

        return $spec;
    }

    /**
     * @inheritDoc
     */
    function getProps()
    {
        $props = parent::getProps();

        $props['sender_class'] = 'enum list|CSenderFTP|CSenderSFTP|CSenderSOAP|CSenderMLLP|CSenderFileSystem|CSenderHTTP';
        $props['sender_id']    .= ' back|config_hl7';

        // Options
        // => ITI-30
        $props['iti30_option_merge']       = 'bool default|1';
        $props['iti30_option_link_unlink'] = 'bool default|0';
        // => ITI-31
        $props['iti31_in_outpatient_management']            = 'bool default|1';
        $props['iti31_pending_event_management']            = 'bool default|0';
        $props['iti31_advanced_encounter_management']       = 'bool default|1';
        $props['iti31_temporary_patient_transfer_tracking'] = 'bool default|0';
        $props['iti31_historic_movement']                   = 'bool default|1';

        $props['check_receiving_application_facility']  = 'bool default|0';
        $props['receiving_application']                 = 'str';
        $props['receiving_facility']                    = 'str';
        $props['assigning_authority_namespace_id']      = 'str';
        $props['assigning_authority_universal_id']      = 'str';
        $props['assigning_authority_universal_type_id'] = 'str';
        $props['country_code']                          = 'enum list|FRA|INT';

        // Encoding
        $props['strict_segment_terminator'] = 'bool default|0';
        $props['segment_terminator']        = 'enum list|CR|LF|CRLF';
        $props['ack_severity_mode']         = 'enum list|IWE|E|W|I';

        // Handle
        $props['handle_mode']                       = 'enum list|normal|simple default|normal';
        $props['handle_telephone_number']           = 'enum list|XTN_1|XTN_12 default|XTN_12';
        $props['ignore_fields']                     = 'str';
        $props['ignore_admit_with_field']           = 'str';
        $props['bypass_validating']                 = 'bool default|0';
        $props['create_grossesse']                  = 'bool default|1';
        $props['search_master_IPP']                 = 'bool default|0';
        $props['search_master_NDA']                 = 'bool default|0';
        $props['ins_integrated']                    = 'bool default|0';
        $props['manage_npa']                        = 'bool default|0';
        $props['retrieve_all_PI_identifiers']       = 'bool default|0';
        $props['change_filler_placer']              = 'bool default|0';
        $props['control_identifier_type_code']      = 'bool default|1';
        $props['handle_OBR_identity_identifier']    = 'str';
        $props['change_OBX_5']                      = 'str';
        $props['associate_category_to_a_file']      = 'bool default|0';
        $props['handle_patient_ITI_31']             = 'bool default|1';
        $props['check_similar_patient']             = 'bool default|1';
        $props['create_duplicate_patient']          = 'bool default|0';
        $props['handle_context_url']                = 'str';
        $props['define_name']                       = 'enum list|'
            . implode('|', FileManager::STRATEGIES_FILENAME)
            . ' default|' . FileManager::STRATEGY_FILENAME_DEFAULT;
        $props['control_nda_target_document']       = 'bool default|1';
        $props['creation_date_file_like_treatment'] = 'bool default|0';
        $props['exclude_not_collide_exte']          = 'bool default|0';
        $props['id_category_patient']               = 'num';
        $props['object_attach_OBX']                 = 'enum list|CPatient|CSejour|COperation|CMbObject|CFilesCategory default|CMbObject';
        $props['mode_sas'] = 'bool default|0';
        $props['unqualified_identity'] = 'bool default|0';
        $props['handle_SIU_object'] = 'enum list|consultation|intervention|element_prescription default|consultation';
        $props['handle_patient_SIU'] = 'bool default|0';
        $props['handle_patient_ORU']                = 'bool default|0';
        $props['generate_IPP_unqualified_identity'] = 'bool default|1';
        $props['search_patient']                    = 'bool default|1';
        $props['search_patient_strategy'] = 'enum list|'
            . implode('|', PatientRepository::STRATEGIES) . ' default|' . PatientRepository::STRATEGY_BEST;
        $props['ignore_the_patient_with_an_unauthorized_IPP'] = 'bool default|0';

        // => PID
        $props['handle_NDA']    = 'enum list|PID_18|PV1_19 default|PID_18';
        $props['handle_NSS']    = 'enum list|PID_3|PID_19 default|PID_3';
        $props['handle_PID_31'] = 'enum list|avs|none default|none';

        // =>PV1
        $props['handle_PV1_3']      = 'enum list|name|config_value|idex default|name';
        $props['handle_PV1_3_null'] = 'str';
        $props['handle_PV1_4']      = 'enum list|normal|charge_price_indicator default|normal';
        $props['handle_PV1_7']      = 'bool default|1';
        $props['handle_PV1_8']      = 'enum list|adressant|traitant default|adressant';
        $props['handle_PV1_9']      = 'enum list|null|famille default|null';
        $props['handle_PV1_10']     = 'enum list|discipline|service|finess default|discipline';
        $props['handle_PV1_14']     = 'enum list|admit_source|ZFM default|admit_source';
        $props['handle_PV1_20']     = 'enum list|old_presta|none default|none';
        $props['handle_PV1_36']     = 'enum list|discharge_disposition|ZFM default|discharge_disposition';
        $props['handle_PV1_50']     = 'enum list|sejour_id|none default|none';

        // => PV2
        $props['handle_PV2_12'] = 'enum list|libelle|none default|libelle';

        // => ZBE
        $props['handle_ZBE_7'] = 'enum list|medicale|soins default|medicale';
        $props['handle_ZBE_8'] = 'enum list|medicale|soins default|soins';

        // => OBX
        $props['handle_OBX_photo_patient'] = 'bool default|0';

        // => IIM
        $props['handle_IIM_6'] = 'enum list|group|service|sejour';

        // Send
        $props['send_assigning_authority'] = 'bool default|1';
        $props['send_self_identifier']     = 'bool default|0';
        // => XTN
        $props['send_area_local_number'] = 'bool default|0';

        // Purge
        $props['purge_idex_movements'] = 'bool default|0';

        // Auto repair
        $props['repair_patient'] = 'bool default|1';
        $props['control_date']   = 'enum list|permissif|strict default|strict';

        // AppFine
        $props['create_user_to_patient'] = 'bool default|0';
        $props['handle_portail_patient'] = 'bool default|0';

        // Doctolib
        $props['handle_doctolib'] = 'bool default|0';

        // TAMM-SIH
        $props['handle_tamm_sih'] = 'str';

        return $props;
    }

    /**
     * Get config fields
     *
     * @return array
     */
    function getConfigFields()
    {
        return $this->_config_fields = self::$config_fields;
    }
}
