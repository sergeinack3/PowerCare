<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cim10\Cisp;

use Ox\Core\CMbArray;
use Ox\Core\CStoredObject;

/**
 * The base class for the CISP base
 */
class CCISP extends CStoredObject
{
    // DB fields
    public $code_cisp;
    public $libelle;
    public $codes_cim10;
    public $inclusion;
    public $exclusion;
    public $description;
    public $consideration;
    public $note;

    // Form fields
    public $_chapitre;
    public $_indice;
    public $_codes_cim10;
    public $_keywords;
    public $_ref_codes_cim10;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->dsn      = "cisp";
        $spec->table    = "cisp";
        $spec->loggable = false;

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                  = parent::getProps();
        $props["code_cisp"]     = "str";
        $props["libelle"]       = "str";
        $props["codes_cim10"]   = "str";
        $props["inclusion"]     = "str";
        $props["exclusion"]     = "str";
        $props["description"]   = "str";
        $props["consideration"] = "str";
        $props["note"]          = "str";
        $props["_keywords"]     = "str";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        $this->_view        = $this->libelle;
        $this->_chapitre    = substr($this->code_cisp, 0, 1);
        $this->_indice      = intval(substr($this->code_cisp, 1));
        $this->codes_cim10  = str_replace(".", "", $this->codes_cim10);
        $this->_codes_cim10 = explode(", ", $this->codes_cim10);

        CMbArray::removeValue("", $this->_codes_cim10);
    }
}
