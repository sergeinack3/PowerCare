<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * A name for an organization. A sequence of name parts.
 */
class CCDAON extends CCDAEN
{

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props           = parent::getProps();
        $props["family"] = "CCDA_en_family xml|element prohibited";
        $props["given"]  = "CCDA_en_given xml|element prohibited";

        return $props;
    }
}
