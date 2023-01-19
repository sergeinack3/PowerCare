<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * Coded data, consists of a code, display name, code system,
 * and original text. Used when a single code value must be sent.
 */
class CCDACV extends CCDACE
{

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props                = parent::getProps();
        $props["translation"] = "CCDACD xml|element prohibited";

        return $props;
    }
}
