<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * Integer numbers (-1,0,1,2, 100, 3398129, etc.) are precise
 * numbers that are results of counting and enumerating.
 * Integer numbers are discrete, the set of integers is
 * infinite but countable.  No arbitrary limit is imposed on
 * the range of integer numbers. Two NULL flavors are
 * defined for the positive and negative infinity.
 */
class CCDAINT extends CCDAQTY
{

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props          = parent::getProps();
        $props["value"] = "CCDA_base_int xml|attribute";

        return $props;
    }

    /**
     * Setter value
     *
     * @param mixed $value mixed
     *
     * @return void
     */
    public function setValue($value)
    {
        if (!$value && $value !== 0) {
            $this->value = null;

            return;
        }
        $int = new CCDA_base_int();
        $int->setData($value);
        $this->value = $int;
    }
}
