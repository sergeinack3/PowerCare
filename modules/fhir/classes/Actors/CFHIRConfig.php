<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Actors;

use Ox\Interop\Eai\CExchangeDataFormatConfig;
use Ox\Interop\Eai\Manager\FileManager;
use Ox\Interop\Eai\Repository\PatientRepository;

/**
 * Class CFHIRConfig
 * Config FHIR
 */
class CFHIRConfig extends CExchangeDataFormatConfig
{
    /**
     * @var array Config fields
     */
    static $config_fields = [
        // Identifiers
        'encode_identifiers',

        // Patient
        'search_patient_strategy',

        // Document
        'associate_category_to_a_file',
        'define_name',
        'id_category_patient',
        'object_attach',
        'mode_sas',
        'creation_date_file_like_treatment',
    ];

    /** @var int PRIMARY KEY */
    public $fhir_config_id;

    // Identifiers
    /** @var string */
    public $encode_identifiers;

    // patient
    /** @var string */
    public $search_patient_strategy;

    // document
    /** @var string */
    public $associate_category_to_a_file;
    /** @var string */
    public $define_name;
    /** @var string */
    public $id_category_patient;
    /** @var string */
    public $object_attach;
    /** @var bool */
    public $mode_sas;
    /** @var bool */
    public $creation_date_file_like_treatment;

    /**
     * @var array Categories
     */
    public $_categories = [
        'identifiers' => [
            'encode_identifiers',
        ],
        'patient' => [
            'search_patient_strategy',
        ],
        'document' => [
            'associate_category_to_a_file',
            'define_name',
            'id_category_patient',
            'object_attach',
            'mode_sas',
            'creation_date_file_like_treatment',
        ],
    ];

    /**
     * @inheritDoc
     */
    public function getSpec()
    {
        $spec = parent::getSpec();

        $spec->table              = 'fhir_config';
        $spec->key                = 'fhir_config_id';
        $spec->uniques['uniques'] = ['sender_id', 'sender_class'];

        return $spec;
    }

    /**
     * @inheritDoc
     */
    public function getProps()
    {
        $props = parent::getProps();

        $props['sender_class'] = 'enum list|CSenderHTTP';
        $props['sender_id']    .= ' back|config_fhir';

        // Options
        // => Identifiers
        $props['encode_identifiers'] = 'bool default|1';

        // Patient
        $props['search_patient_strategy'] = 'enum list|'
            . implode('|', PatientRepository::STRATEGIES)
            . ' default|' . PatientRepository::STRATEGY_BEST;

        // Document
        $props['associate_category_to_a_file'] = "bool default|0";
        $props['define_name'] = 'enum list|'
            . implode('|', FileManager::STRATEGIES_FILENAME)
            . ' default|' . FileManager::STRATEGY_FILENAME_DEFAULT;
        $props['id_category_patient'] = 'num';
        $props['object_attach'] = 'enum list|CPatient|CSejour|COperation|CMbObject|CFilesCategory default|CMbObject';
        $props['mode_sas'] = 'bool default|0';
        $props['creation_date_file_like_treatment'] = 'bool default|0';

        return $props;
    }

    /**
     * Get config fields
     *
     * @return array
     */
    public function getConfigFields()
    {
        return $this->_config_fields = self::$config_fields;
    }
}
