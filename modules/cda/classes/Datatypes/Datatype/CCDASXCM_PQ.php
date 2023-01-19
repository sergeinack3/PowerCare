<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Datatype;

use Ox\Core\CClassMap;
use Ox\Interop\Cda\Datatypes\Base\CCDAPQ;
use Ox\Interop\Cda\Datatypes\Voc\CCDASetOperator;

/**
 * CCDASXCM_PQ class
 */
class CCDASXCM_PQ extends CCDAPQ
{

    /**
     *  A code specifying whether the set component is included
     * (union) or excluded (set-difference) from the set, or
     * other set operations with the current set component and
     * the set as constructed from the representation stream
     * up to the current point.
     *
     * @var CCDASetOperator
     */
    public $operator;

    /**
     * retourne le nom du type CDA
     *
     * @return string
     */
    function getNameClass()
    {
        $name = CClassMap::getSN($this);
        $name = substr($name, 4);

        return $name;
    }

    /**
     * Getter Operator
     *
     * @return CCDASetOperator
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Setter Operator
     *
     * @param String $operator String
     *
     * @return void
     */
    public function setOperator($operator)
    {
        if (!$operator) {
            $this->operator = null;

            return;
        }
        $op = new CCDASetOperator();
        $op->setData($operator);
        $this->operator = $op;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props             = parent::getProps();
        $props["operator"] = "CCDASetOperator xml|attribute default|I";

        return $props;
    }
}
