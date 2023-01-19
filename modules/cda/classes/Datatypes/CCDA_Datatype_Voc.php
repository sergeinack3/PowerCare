<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes;

use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Interop\Cda\Datatypes\Base\CCDA_base_cs;

/**
 * Classe dont hériteront les classes vocabulaires
 */
class CCDA_Datatype_Voc extends CCDA_base_cs
{
    public $_enumeration     = [];
    public $_all_enumeration = [];
    public $_union           = [];

    /**
     * construit la classe
     */
    function __construct()
    {
        $this->_enumeration     = $this->getEnumeration();
        $this->_all_enumeration = $this->getEnumeration(true);
    }

    /**
     * Getter enumeration
     *
     * @param bool $all bool
     *
     * @return array
     */
    public function getEnumeration($all = false)
    {
        if (!$all) {
            return $this->_enumeration;
        }

        $enumerations = [];
        $enumerations = array_merge($this->_enumeration, $enumerations);
        foreach ($this->_union as $_union) {
            $_union = "CCDA" . $_union;
            /** @var CCDA_Datatype_voc $_truc */
            $_truc        = new $_union();
            $enumerations = array_merge($enumerations, $_truc->getEnumeration());
        }

        return $enumerations;
    }

    /**
     * Retourne le nom de la classe
     *
     * @return mixed|string
     */
    public function getNameClass()
    {
        $name = CClassMap::getSN($this);
        $name = substr($name, 4);

        if (CClassMap::getSN($this) === "CCDA_base_cs") {
            $name = CMbArray::get(explode("_", $name), 1);
        }

        return $name;
    }

    /**
     * Getter props
     *
     * @return array
     */
    function getProps()
    {
        $props = parent::getProps();

        return $props;
    }

    /**
     * Getter union
     *
     * @return array
     */
    function getUnion()
    {
        return $this->_union;
    }
}
