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
use Ox\Interop\Cda\Datatypes\Base\CCDAPQ;

/**
 * CCDAIVXB_PQ class
 */
class CCDAIVXB_PQ extends CCDAPQ
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

    /**
     * Fonction permettant de tester la classe
     *
     * @return array
     */
    function test()
    {
        $tabTest = parent::test();

        /**
         * Test avec un inclusive incorrecte
         */

        $this->setInclusive("TESTTEST");
        $tabTest[] = $this->sample("Test avec un inclusive incorrecte", "Document invalide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un inclusive correcte
         */

        $this->setInclusive("true");
        $tabTest[] = $this->sample("Test avec un inclusive correcte", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        return $tabTest;
    }
}
