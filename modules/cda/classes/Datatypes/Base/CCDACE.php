<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * Coded data, consists of a coded value (CV)
 * and, optionally, coded value(s) from other coding systems
 * that identify the same concept. Used when alternative
 * codes may exist.
 */
class CCDACE extends CCDACD
{


    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props              = parent::getProps();
        $props["qualifier"] = "CCDACR xml|element prohibited";

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
         * Test avec un qualifier correct
         */

        $cr = new CCDACR();
        $cr->setInverted("true");
        $this->setQualifier($cr);

        $tabTest[] = $this->sample("Test avec un qualifier correcte, interdit dans ce contexte", "Document invalide");
        $this->resetListQualifier();

        /*-------------------------------------------------------------------------------------*/

        return $tabTest;
    }
}
