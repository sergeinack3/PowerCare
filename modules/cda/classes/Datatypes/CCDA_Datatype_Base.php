<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes;

use Ox\Core\CClassMap;

/**
 * Classe dont hériteront les classes de base (real, int...)
 */
class CCDA_Datatype_Base extends CCDA_Datatype
{

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    public function getProps()
    {
        $props = parent::getProps();

        return $props;
    }

    /**
     * Retourne le nom du type utilisé dans le XSD
     *
     * @return string
     */
    public function getNameClass()
    {
        $name = CClassMap::getSN($this);

        $name = substr($name, strrpos($name, "_") + 1);

        return $name;
    }
}
