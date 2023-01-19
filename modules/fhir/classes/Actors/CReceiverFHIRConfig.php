<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Actors;

use Ox\Core\CMbObjectConfig;
use Ox\Core\CMbObjectSpec;

/**
 * FHIR receiver config
 */
class CReceiverFHIRConfig extends CMbObjectConfig
{
    /** @var int PRIMARY KEY */
    public $receiver_fhir_config_id;

    /** @var int */
    public $object_id; // CReceiverFHIR

    // Format
    /** @var bool */
    public $encode_identifiers;

    /** @var string[][] */
    public $_categories = [
        'identifiers' => [
            'encode_identifiers',
        ],
    ];

    /**
     * Initialize object specification
     *
     * @return CMbObjectSpec the spec
     */
    public function getSpec()
    {
        $spec = parent::getSpec();

        $spec->table              = 'receiver_fhir_config';
        $spec->key                = 'receiver_fhir_config_id';
        $spec->uniques['uniques'] = ['object_id'];

        return $spec;
    }

    /**
     * Get properties specifications as strings
     *
     * @return array
     */
    public function getProps()
    {
        $props              = parent::getProps();
        $props['object_id'] = 'ref class|CReceiverFHIR back|object_configs';

        // Format
        $props['encode_identifiers'] = 'bool default|1';

        return $props;
    }
}
