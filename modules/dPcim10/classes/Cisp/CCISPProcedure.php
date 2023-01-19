<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cim10\Cisp;

use Ox\Core\Cache;
use Ox\Core\CStoredObject;

/**
 * The procedure class for the CISP base
 */
class CCISPProcedure extends CStoredObject
{
    // DB fields
    public $identifiant;
    public $description;

    // Form fields
    public $_indice;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->dsn      = "cisp";
        $spec->table    = "procedure";
        $spec->loggable = false;

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                = parent::getProps();
        $props["lettre"]      = "str";
        $props["description"] = "str";
        $props["note"]        = "str";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view   = $this->description;
        $this->_indice = substr($this->identifiant, 1);
    }

    /**
     * Get all procedures from CISP base
     *
     * @return self[]
     */
    static function getProcedures()
    {
        $cache = new Cache('CCISPProcedure.getProcedures', null, Cache::INNER_OUTER);

        if ($procedures = $cache->get()) {
            return $procedures;
        }

        $procedure = new self();

        return $cache->put($procedure->loadList());
    }
}
