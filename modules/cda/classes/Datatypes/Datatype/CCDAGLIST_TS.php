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
use Ox\Interop\Cda\Datatypes\Base\CCDAANY;
use Ox\Interop\Cda\Datatypes\Base\CCDAPQ;
use Ox\Interop\Cda\Datatypes\Base\CCDATS;

/**
 * CCDAGLIST_TS class
 */
class CCDAGLIST_TS extends CCDAANY
{

    /**
     * This is the start-value of the generated list.
     *
     * @var CCDATS
     */
    public $head;

    /**
     * The difference between one value and its previous
     * different value. For example, to generate the sequence
     * (1; 4; 7; 10; 13; ...) the increment is 3; likewise to
     * generate the sequence (1; 1; 4; 4; 7; 7; 10; 10; 13;
     * 13; ...) the increment is also 3.
     *
     * @var CCDAPQ
     */
    public $increment;

    /**
     * If non-NULL, specifies that the sequence alternates,
     * i.e., after this many increments, the sequence item
     * values roll over to start from the initial sequence
     * item value. For example, the sequence (1; 2; 3; 1; 2;
     * 3; 1; 2; 3; ...) has period 3; also the sequence
     * (1; 1; 2; 2; 3; 3; 1; 1; 2; 2; 3; 3; ...) has period
     * 3 too.
     *
     * @var CCDA_base_int
     */
    public $period;

    /**
     * The integer by which the index for the sequence is
     * divided, effectively the number of times the sequence
     * generates the same sequence item value before
     * incrementing to the next sequence item value. For
     * example, to generate the sequence (1; 1; 1; 2; 2; 2; 3; 3;
     * 3; ...)  the denominator is 3.
     *
     * @var CCDA_base_int
     */
    public $denominator;

    /**
     * Getter denominator
     *
     * @return CCDA_base_int
     */
    public function getDenominator()
    {
        return $this->denominator;
    }

    /**
     * Setter denominator
     *
     * @param String $denominator String
     *
     * @return void
     */
    public function setDenominator($denominator)
    {
        if (!$denominator) {
            $this->denominator = null;

            return;
        }
        $int = new CCDA_base_int();
        $int->setData($denominator);
        $this->denominator = $int;
    }

    /**
     * Getter head
     *
     * @return CCDATS
     */
    public function getHead()
    {
        return $this->head;
    }

    /**
     * Setter head
     *
     * @param CCDATS $head \CCDATS
     *
     * @return void
     */
    public function setHead($head)
    {
        $this->head = $head;
    }

    /**
     * Getter increment
     *
     * @return CCDAPQ
     */
    public function getIncrement()
    {
        return $this->increment;
    }

    /**
     * Setter increment
     *
     * @param CCDAPQ $increment \CCDAPQ
     *
     * @return void
     */
    public function setIncrement($increment)
    {
        $this->increment = $increment;
    }

    /**
     * Getter period
     *
     * @return CCDA_base_int
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Setter period
     *
     * @param String $period String
     *
     * @return void
     */
    public function setPeriod($period)
    {
        if (!$period) {
            $this->period = null;

            return;
        }
        $int = new CCDA_base_int();
        $int->setData($period);
        $this->period = $int;
    }

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
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props                = parent::getProps();
        $props["head"]        = "CCDATS xml|element required";
        $props["increment"]   = "CCDAPQ xml|element required";
        $props["period"]      = "CCDA_base_int xml|attribute";
        $props["denominator"] = "CCDA_base_int xml|attribute";

        return $props;
    }
}
