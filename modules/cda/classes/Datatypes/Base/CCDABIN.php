<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * Binary data is a raw block of bits. Binary data is a
 * protected type that MUST not be used outside the data
 * type specification.
 */
class CCDABIN extends CCDAANY
{

    /**
     * Specifies the representation of the binary data that
     * is the content of the binary data value.
     * @var CCDA_base_BinaryDataEncoding
     */
    public $representation;
    /**
     * Get the properties of our class as strings
     *
     * @return array
     */

    /**
     * Modifie la representation
     *
     * @param String $representation Representation
     *
     * @return void
     */
    function setRepresentation($representation)
    {
        if (!$representation) {
            $this->representation = null;

            return;
        }
        $binary = new CCDA_base_BinaryDataEncoding();
        $binary->setData($representation);
        $this->representation = $binary;
    }

    /**
     * Props
     *
     * @return array
     */
    function getProps()
    {
        $props                   = parent::getProps();
        $props["representation"] = "CCDA_base_BinaryDataEncoding xml|attribute default|TXT";
        $props["data"]           = "str xml|data";

        return $props;
    }
}
