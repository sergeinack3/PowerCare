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
 * The chapter class for the CISP base
 */
class CCISPChapitre extends CStoredObject
{
    // DB fields
    public $lettre;
    public $description;
    public $note;

    // Form fields
    /** @var CCISP[] */
    public $_ref_cisps;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->dsn      = "cisp";
        $spec->table    = "chapitre";
        $spec->key      = "lettre";
        $spec->loggable = false;

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                = parent::getProps();
        $props["description"] = "str";
        $props["note"]        = "str";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        $this->_view = $this->lettre;
    }

    /**
     * Get all chapters from CISP base
     *
     * @return self[]
     */
    static function getChapitres()
    {
        $cache = new Cache('CCISPChapitre.getChapitres', null, Cache::INNER_OUTER);

        if ($chapitres = $cache->get()) {
            return $chapitres;
        }

        $chapitre  = new self();
        $chapitres = $chapitre->loadList();

        foreach ($chapitres as $_chapitre) {
            $_chapitre->loadRefsCISPS();
        }

        return $cache->put($chapitres);
    }

    /**
     * Get all CISPs for a chapter
     *
     * @return CCISP[]
     */
    function loadRefsCISPS()
    {
        $cisp  = new CCISP();
        $where = [
            "code_cisp" => "LIKE '$this->lettre%'",
        ];

        return $this->_ref_cisps = $cisp->loadList($where);
    }
}
