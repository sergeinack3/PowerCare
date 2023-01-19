<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * A thumbnail is an abbreviated rendition of the full
 * data. A thumbnail requires significantly fewer
 * resources than the full data, while still maintaining
 * some distinctive similarity with the full data. A
 * thumbnail is typically used with by-reference
 * encapsulated data. It allows a user to select data
 * more efficiently before actually downloading through
 * the reference.
 */
class CCDAthumbnail extends CCDAED
{


    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props              = parent::getProps();
        $props["thumbnail"] = "CCDAthumbnail xml|element prohibited";

        return $props;
    }
}
