<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * The Boolean type stands for the values of two-valued logic.
 * A Boolean value can be either true or
 * false, or, as any other value may be NULL.
 */
class CCDABL extends CCDAANY
{

    public $value;

    /**
     * Getter value
     *
     * @return CCDA_base_bl
     */
    function getValue()
    {
        return $this->value;
    }

    /**
     * Setter value
     *
     * @param String $value String
     *
     * @return void
     */
    function setValue($value)
    {
        if (!$value) {
            $this->value = null;

            return;
        }
        $val = new CCDA_base_bl();
        $val->setData($value);
        $this->value = $val;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props          = parent::getProps();
        $props["value"] = "CCDA_base_bl xml|attribute notNullFlavor";

        return $props;
    }
}
