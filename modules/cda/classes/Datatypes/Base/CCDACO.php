<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * Coded data, where the domain from which the codeset comes
 * is ordered. The Coded Ordinal data type adds semantics
 * related to ordering so that models that make use of such
 * domains may introduce model elements that involve statements
 * about the order of the terms in a domain.
 */
class CCDACO extends CCDACV
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
