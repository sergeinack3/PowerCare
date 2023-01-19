<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Datatype;

use Ox\Core\CClassMap;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVL_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAPQ;

/**
 * CCDAHXIT_PQ class
 */
class CCDAHXIT_PQ extends CCDAPQ
{

    /**
     * The time interval during which the given information
     * was, is, or is expected to be valid. The interval can
     * be open or closed, as well as infinite or undefined on
     * either side.
     *
     * @var CCDAIVL_TS
     */
    public $validTime;

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
     * Getter validTime
     *
     * @return CCDAIVL_TS
     */
    public function getValidTime()
    {
        return $this->validTime;
    }

    /**
     * Setter validTime
     *
     * @param CCDAIVL_TS $validTime \CCDAIVL_TS
     *
     * @return void
     */
    public function setValidTime($validTime)
    {
        $this->validTime = $validTime;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props              = parent::getProps();
        $props["validTime"] = "CCDAIVL_TS xml|element max|1";

        return $props;
    }
}
