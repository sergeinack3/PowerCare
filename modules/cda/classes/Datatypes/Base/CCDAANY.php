<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

use Ox\Interop\Cda\Datatypes\CCDA_Datatype;
use Ox\Interop\Cda\Datatypes\Voc\CCDANullFlavor;

/**
 * Defines the basic properties of every data value. This
 * is an abstract type, meaning that no value can be just
 * a data value without belonging to any concrete type.
 * Every concrete type is a specialization of this
 * general abstract DataValue type.
 */
class CCDAANY extends CCDA_Datatype
{

    /**
     * An exceptional value expressing missing information
     * and possibly the reason why the information is missing.
     * @var CCDANullFlavor
     */
    public $nullFlavor;

    /**
     * Getter nullFlavor
     *
     * @return CCDANullFlavor
     */
    public function getNullFlavor()
    {
        return $this->nullFlavor;
    }

    /**
     * Setter nullFlavor
     *
     * @param String $nullFlavor String
     *
     * @return void
     */
    function setNullFlavor($nullFlavor)
    {
        if (!$nullFlavor) {
            $this->nullFlavor = null;

            return;
        }
        $null = new CCDANullFlavor();
        $null->setData($nullFlavor);
        $this->nullFlavor = $null;
    }

    /**
     * Getter props
     *
     * @return array
     */
    function getProps()
    {
        $props               = parent::getProps();
        $props["nullFlavor"] = "CCDANullFlavor xml|attribute";

        return $props;
    }
}
