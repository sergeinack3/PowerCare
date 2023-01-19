<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Datatype;

use Ox\Interop\Cda\Datatypes\Base\CCDASXCM_TS;

/**
 * CCDASXPR_TS class
 */
class CCDASXPR_TS extends CCDASXCM_TS
{

    /**
     * @var CCDASXCM_TS
     */
    public $comp = [];

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props         = parent::getProps();
        $props["comp"] = "CCDASXCM_TS xml|element min|2";

        return $props;
    }

    /**
     * ADD a class
     *
     * @param CCDASXCM_TS $listData \CCDASXCM_TS
     *
     * @return void
     */
    function addData($listData)
    {
        $this->comp[] = $listData;
    }

    /**
     * Reinitialise la variable
     *
     * @return void
     */
    function resetListData()
    {
        $this->comp = [];
    }
}
