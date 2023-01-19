<?php
/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda;

use Ox\Interop\Eai\CExchangeDataFormatConfig;
use Ox\Interop\Eai\Repository\PatientRepository;

/**
 * Class CCDAConfig
 */
class CCDAConfig extends CExchangeDataFormatConfig
{
    /** @var string[] */
    public static $config_fields = [
        'search_patient',
        'search_patient_strategy',
        'assigning_cda_to_patient',
        'handle_patient',
    ];

    // Format
    /** @var int */
    public $cda_config_id;
    /** @var string */
    public $search_patient; // old config not used yet
    /** @var string */
    public $assigning_cda_to_patient;
    /** @var string */
    public $search_patient_strategy;
    /** @var string */
    public $handle_patient;

    /** @var string[] */
    public $_categories = [
        // Format
        'handle' => [
            'search_patient_strategy',
            'assigning_cda_to_patient',
            'handle_patient'
        ],
    ];

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec = parent::getSpec();

        $spec->table              = "cda_config";
        $spec->key                = "cda_config_id";
        $spec->uniques["uniques"] = ["sender_id", "sender_class"];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props = parent::getProps();

        $props['sender_id']                .= ' back|config_cda';
        $props['sender_class']             = 'enum list|CSenderFTP|CSenderSFTP|CSenderSOAP|CSenderFileSystem|CSenderMSSante|CSenderHTTP';
        $props['assigning_cda_to_patient'] = 'bool default|0';
        $props['handle_patient']           = 'bool default|0';
        $props['search_patient_strategy']  = 'enum list|'
            . implode('|', PatientRepository::STRATEGIES) . ' default|' . PatientRepository::STRATEGY_BEST;

        return $props;
    }

    /**
     * @inheritdoc
     */
    function getConfigFields()
    {
        return $this->_config_fields = self::$config_fields;
    }
}
