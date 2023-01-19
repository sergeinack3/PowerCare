<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Datatype;

use Ox\Core\CClassMap;
use Ox\Interop\Cda\Datatypes\Base\CCDA_base_bl;

/**
 * CCDAIVXB_PPD_TS class
 */
class CCDAIVXB_PPD_TS extends CCDAPPD_TS
{

    /**
     * Specifies whether the limit is included in the
     * interval (interval is closed) or excluded from the
     * interval (interval is open).
     *
     * @var CCDA_base_bl
     */
    public $inclusive;

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
     * Getter inclusive
     *
     * @return CCDA_base_bl
     */
    public function getInclusive()
    {
        return $this->inclusive;
    }

    /**
     * Setter inclusive
     *
     * @param String $inclusive String
     *
     * @return void
     */
    public function setInclusive($inclusive)
    {
        if (!$inclusive) {
            $this->inclusive = null;

            return;
        }
        $bl = new CCDA_base_bl();
        $bl->setData($inclusive);
        $this->inclusive = $bl;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props              = parent::getProps();
        $props["inclusive"] = "CCDA_base_bl xml|attribute default|true";

        return $props;
    }
}
