<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Transformations;

/**
 * Class CTransformationRuleSet
 * EAI transformation ruleset
 */

use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;

class CTransformationRuleSet extends CMbObject
{
    // DB Table key
    public $transformation_ruleset_id;

    // DB fields
    public $name;
    public $description;

    // Form fields
    public $_ref_transformation_rule_sequences;


    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec = parent::getSpec();

        $spec->table = 'transformation_ruleset';
        $spec->key   = 'transformation_ruleset_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props = parent::getProps();

        $props["name"]        = "str notNull seekable autocomplete";
        $props["description"] = "text";

        return $props;
    }

    /**
     * Load rules sequences
     *
     * @return CTransformationRuleSequence[]|CStoredObject[]
     */
    function loadRefsTransformationRuleSequences()
    {
        return $this->_ref_transformation_rule_sequences = $this->loadBackRefs("transformation_rule_sequences");
    }
}
