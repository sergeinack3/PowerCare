<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * The quantity data type is an abstract generalization
 * for all data types (1) whose value set has an order
 * relation (less-or-equal) and (2) where difference is
 * defined in all of the data type's totally ordered value
 * subsets.  The quantity type abstraction is needed in
 * defining certain other types, such as the interval and
 * the probability distribution.
 */
class CCDAQTY extends CCDAANY
{

    /**
     * The magnitude of the measurement value in terms of
     * the unit specified in the code.
     */
    public $value;

    /**
     * Getter value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array()
     */
    function getProps()
    {
        $props = parent::getProps();

        return $props;
    }
}
