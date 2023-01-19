<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * A restriction of entity name that is effectively a simple string used
 * for a simple name for things and places.
 */
class CCDATN extends CCDAEN
{

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props              = parent::getProps();
        $props["delimiter"] = "CCDA_en_delimiter xml|element prohibited";
        $props["family"]    = "CCDA_en_family xml|element prohibited";
        $props["given"]     = "CCDA_en_given xml|element prohibited";
        $props["prefix"]    = "CCDA_en_prefix xml|element prohibited";
        $props["suffix"]    = "CCDA_en_suffix xml|element prohibited";

        return $props;
    }
}
