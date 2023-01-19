<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * A name for a person. A sequence of name parts, such as
 * given name or family name, prefix, suffix, etc. PN differs
 * from EN because the qualifier type cannot include LS
 * (Legal Status).
 */
class CCDAPN extends CCDAEN
{
    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props = parent::getProps();

        return $props;
    }
}
