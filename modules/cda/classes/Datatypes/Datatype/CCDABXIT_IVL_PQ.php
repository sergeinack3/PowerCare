<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Datatype;

use Ox\Core\CClassMap;
use Ox\Interop\Cda\Datatypes\Base\CCDA_base_int;
use Ox\Interop\Cda\Datatypes\Voc\CCDASetOperator;

/**
 * CCDABXIT_IVL_PQ class
 */
class CCDABXIT_IVL_PQ extends CCDAIVL_PQ
{

    /**
     * The quantity in which the bag item occurs in its containing bag.
     *
     * @var CCDASetOperator
     */
    public $qty;

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
     * Getter qty
     *
     * @return CCDA_base_int
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * Setter qty
     *
     * @param String $qty String
     *
     * @return void
     */
    public function setQty($qty)
    {
        if (!$qty) {
            $this->qty = null;

            return;
        }
        $int = new CCDA_base_int();
        $int->setData($qty);
        $this->qty = $int;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props        = parent::getProps();
        $props["qty"] = "CCDA_base_int xml|attribute default|1";

        return $props;
    }
}
