<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Datatype;

use Ox\Interop\Cda\Datatypes\Base\CCDA_base_int;
use Ox\Interop\Cda\Datatypes\CCDA_Datatype_Set;

/**
 * CCDAlist_int Class
 */
class CCDAlist_int extends CCDA_Datatype_Set
{

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props             = parent::getProps();
        $props["listData"] = "CCDA_base_int xml|data";

        return $props;
    }

    /**
     * ADD a class
     *
     * @param String $listData String
     *
     * @return void
     */
    function addData($listData)
    {
        $int = new CCDA_base_int();
        $int->setData($listData);
        $this->listData[] = $int;
    }
}
