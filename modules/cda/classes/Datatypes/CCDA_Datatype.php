<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes;

use Ox\Core\CClassMap;
use Ox\Interop\Cda\CCDAClasseBase;

/**
 * Classe dont hériteront toutes les classes
 */
class CCDA_Datatype extends CCDAClasseBase
{

    public $data;

    /**
     * Getter Data
     *
     * @return mixed
     */
    function getData()
    {
        return $this->data;
    }

    /**
     * Setter Data
     *
     * @param String $data String
     *
     * @return void
     */
    function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Initialise les props
     *
     * @return array
     */
    function getProps()
    {
        $props = [];

        return $props;
    }

    /**
     * Retourne le nom du type utilisé dans le XSD
     *
     * @return string
     */
    function getNameClass()
    {
        $name = CClassMap::getSN($this);
        $name = substr($name, 4);

        if (strpos($name, "_") !== false) {
            $name = substr($name, 1);
        }

        return $name;
    }

    /**
     * Retourne le résultat de la validation par le xsd de la classe appellée
     *
     * @return bool
     */
    function validate()
    {
        $domDataType = $this->toXML(null, null);

        return $domDataType->schemaValidate("modules/cda/resources/TestClasses.xsd", false, false);
    }

}
