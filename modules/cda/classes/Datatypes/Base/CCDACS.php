<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 *  Coded data, consists of a code, display name, code system,
 * and original text. Used when a single code value must be sent.
 */
class CCDACS extends CCDACV
{

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props                      = parent::getProps();
        $props["originalText"]      = "CCDAED xml|element max|1 prohibited";
        $props["qualifier"]         = "CCDACR xml|element prohibited";
        $props["translation"]       = "CCDACD xml|element prohibited";
        $props["codeSystem"]        = "CCDA_base_uid xml|attribute prohibited";
        $props["codeSystemName"]    = "CCDA_base_st xml|attribute prohibited";
        $props["codeSystemVersion"] = "CCDA_base_st xml|attribute prohibited";
        $props["displayName"]       = "CCDA_base_st xml|attribute prohibited";

        return $props;
    }
}
