<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * The character string data type stands for text data,
 * primarily intended for machine processing (e.g.,
 * sorting, querying, indexing, etc.) Used for names,
 * symbols, and formal expressions.
 */
class CCDAST extends CCDAED
{

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props                            = parent::getProps();
        $props["reference"]               = "CCDATEL xml|element prohibited";
        $props["thumbnail"]               = "CCDAthumbnail xml|element prohibited";
        $props["mediaType"]               = "CCDACS xml|attribute default|text/plain";
        $props["compression"]             = "CCDACompressionAlgorithm xml|attribute prohibited";
        $props["integrityCheck"]          = "CCDbin xml|attribute prohibited";
        $props["integrityCheckAlgorithm"] = "CCDAintegrityCheckAlgorithm xml|attribute prohibited";

        return $props;
    }

    /**
     * Fonction permettant de tester la validité de la classe
     *
     * @return array()
     */
    function test()
    {
        $tabTest = parent::test();


        return $tabTest;
    }

}
