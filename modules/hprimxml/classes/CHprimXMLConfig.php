<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Ox\Interop\Eai\CExchangeDataFormatConfig;

/**
 * Class CHprimXMLConfig
 */
class CHprimXMLConfig extends CExchangeDataFormatConfig
{
    /** @var string[] */
    public static $config_fields = [
        // Encoding
        'display_errors',

        // Digit
        'type_sej_hospi',
        'type_sej_ambu',
        'type_sej_urg',
        'type_sej_exte',
        'type_sej_scanner',
        'type_sej_chimio',
        'type_sej_dialyse',
        'type_sej_pa',

        // Handle
        'use_sortie_matching',
        'fully_qualified',
        'check_similar',
        'att_system',
        'insc_integrated',
        'frais_divers',
        'prestation',
        'force_birth_rank_if_null',

        // Purge
        'purge_idex_movements',

        // Repair
        'repair_patient',

        // AppFine
        'handle_appFine',

        // TAMM_SIH
        'handle_tamm_sih',
    ];

    /** @var int */
    public $hprimxml_config_id;

    // Digit
    /** @var string */
    public $type_sej_hospi;
    /** @var string */
    public $type_sej_ambu;
    /** @var string */
    public $type_sej_urg;
    /** @var string */
    public $type_sej_exte;
    /** @var string */
    public $type_sej_scanner;
    /** @var string */
    public $type_sej_chimio;
    /** @var string */
    public $type_sej_dialyse;
    /** @var string */
    public $type_sej_pa;

    // Handle
    /** @var bool */
    public $use_sortie_matching;
    /** @var bool */
    public $fully_qualified;
    /** @var bool */
    public $check_similar;
    /** @var string */
    public $att_system;
    /** @var bool */
    public $insc_integrated;
    /** @var string */
    public $frais_divers;
    /** @var string */
    public $prestation;
    /** $var bool */
    public $force_birth_rank_if_null;

    // Format
    /** @var bool */
    public $display_errors;

    // Purge
    /** @var bool */
    public $purge_idex_movements;

    // Repair
    /** @var bool */
    public $repair_patient;

    // AppFine
    /** @var bool */
    public $handle_appFine;

    // TAMM-SIH
    /** @var bool */
    public $handle_tamm_sih;

    /** @var string[][] */
    public $_categories = [
        // Format
        'format'      => [
            'display_errors',
        ],

        // Handle
        'handle'      => [
            'use_sortie_matching',
            'fully_qualified',
            'check_similar',
            'att_system',
            'insc_integrated',
            'frais_divers',
            'prestation',
            'force_birth_rank_if_null',
        ],

        // Digit
        'digit'       => [
            'type_sej_hospi',
            'type_sej_ambu',
            'type_sej_urg',
            'type_sej_exte',
            'type_sej_scanner',
            'type_sej_chimio',
            'type_sej_dialyse',
            'type_sej_pa',
        ],

        // Purge
        'purge'       => [
            'purge_idex_movements',
        ],

        // Repair
        'auto-repair' => [
            'repair_patient',
        ],

        // AppFine
        'appFine'     => [
            'handle_appFine',
        ],

        'TAMM-SIH' => [
            'handle_tamm_sih',
        ],
    ];

    /**
     * @inheritDoc
     */
    public function getSpec()
    {
        $spec                     = parent::getSpec();
        $spec->table              = 'hprimxml_config';
        $spec->key                = 'hprimxml_config_id';
        $spec->uniques['uniques'] = ['sender_id', 'sender_class'];

        return $spec;
    }

    /**
     * @inheritDoc
     */
    public function getProps()
    {
        $props = parent::getProps();

        $props['sender_id'] .= ' back|config_hprimxml';

        // Encoding
        $props['display_errors'] = 'bool default|1';

        // Digit
        $props['type_sej_hospi']   = 'str';
        $props['type_sej_ambu']    = 'str';
        $props['type_sej_urg']     = 'str';
        $props['type_sej_exte']    = 'str';
        $props['type_sej_scanner'] = 'str';
        $props['type_sej_chimio']  = 'str';
        $props['type_sej_dialyse'] = 'str';
        $props['type_sej_pa']      = 'str';

        // Handle
        $props['use_sortie_matching']      = 'bool default|1';
        $props['fully_qualified']          = 'bool default|1';
        $props['check_similar']            = 'bool default|0';
        $props['att_system']               = 'enum list|acteur|application|système|finessgeographique|finessjuridique default|système';
        $props['insc_integrated']          = 'bool default|0';
        $props['frais_divers']             = 'enum list|fd|presta default|fd';
        $props['prestation']               = 'enum list|nom|idex default|nom';
        $props['force_birth_rank_if_null'] = 'bool default|0';

        // Repair
        $props['repair_patient'] = 'bool default|1';

        // Purge
        $props['purge_idex_movements'] = 'bool default|0';

        // AppFine
        $props['handle_appFine'] = 'bool default|0';

        // TAMM-SIH
        $props['handle_tamm_sih'] = 'str';

        return $props;
    }

    /**
     * @inheritDoc
     */
    public function getConfigFields()
    {
        return $this->_config_fields = self::$config_fields;
    }
}
