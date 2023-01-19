<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * A representation of a physical quantity in a unit from
 * any code system. Used to show alternative representation
 * for a physical quantity.
 */
class CCDAPQR extends CCDACV
{

    /**
     * The magnitude of the measurement value in terms of
     * the unit specified in the code.
     *
     * @var CCDA_base_real
     */
    public $value;

    /**
     * Getter value
     *
     * @return CCDA_base_real
     */
    public function getValue()
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
    public function setValue($value)
    {
        if (!$value) {
            $this->value = null;

            return;
        }
        $val = new CCDA_base_real();
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
        $props["value"] = "CCDA_base_real xml|attribute";

        return $props;
    }
}
