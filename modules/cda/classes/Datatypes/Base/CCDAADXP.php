<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

use Ox\Interop\Cda\Datatypes\Voc\CCDAAddressPartType;

/**
 * A character string that may have a type-tag signifying its
 * role in the address. Typical parts that exist in about
 * every address are street, house number, or post box,
 * postal code, city, country but other roles may be defined
 * regionally, nationally, or on an enterprise level (e.g. in
 * military addresses). Addresses are usually broken up into
 * lines, which are indicated by special line-breaking
 * delimiter elements (e.g., DEL).
 */
class CCDAADXP extends CCDAST
{

    /**
     * Specifies whether an address part names the street,
     * city, country, postal code, post box, etc. If the type
     * is NULL the address part is unclassified and would
     * simply appear on an address label as is.
     *
     * @var CCDAAddressPartType
     */
    public $partType;

    /**
     * Getter partType
     *
     * @return CCDAAddressPartType
     */
    public function getPartType()
    {
        return $this->partType;
    }

    /**
     * Setter partType
     *
     * @param String $partType String
     *
     * @return void
     */
    public function setPartType($partType)
    {
        if (!$partType) {
            $this->partType = null;

            return;
        }
        $part = new CCDAAddressPartType();
        $part->setData($partType);
        $this->partType = $part;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props             = parent::getProps();
        $props["partType"] = "CCDAAdressPartType xml|attribute";

        return $props;
    }
}
