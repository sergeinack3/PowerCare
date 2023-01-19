<?php

/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\OpenData;

use Ox\Core\CMbObject;

/**
 * Description
 */
class CCommuneFrance extends CMbObject
{
    /** @var int */
    public $commune_france_id;

    /** @var string */
    public $INSEE;

    /** @var string */
    public $commune;

    /** @var string */
    public $departement;

    /** @var string */
    public $code_departement;

    /** @var string */
    public $region;

    /** @var string */
    public $code_region;

    /** @var string */
    public $code_postal;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->dsn      = 'INSEE';
        $spec->loggable = false;
        $spec->table    = "communes_france";
        $spec->key      = "commune_france_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                     = parent::getProps();
        $props["INSEE"]            = "str minLength|3 maxLength|5 notNull";
        $props["commune"]          = "str notNull seekable";
        $props["departement"]      = "str";
        $props["code_departement"] = "str";
        $props["region"]           = "str";
        $props["code_region"]      = "str";
        $props["code_postal"]      = "str minLength|3 maxLength|5 notNull";


        return $props;
    }

    /**
     * @param string $insee_code INSEE code of the commune
     *
     * @return CCommuneFrance
     */
    public function loadByInsee(string $insee_code): self
    {
        $this->INSEE = $insee_code;

        $this->loadMatchingObject();

        return $this;
    }

    /**
     * @param string $insee_code
     *
     * @return array
     */
    public function loadListByInsee(string $insee_code): array
    {
        $this->INSEE = $insee_code;

        return $this->loadMatchingList();
    }

    /**
     * @param string $commune
     * @param string $cp
     *
     * @return self
     */
    public function loadByNomAndCP(string $commune, string $cp): self
    {
        $this->commune     = $commune;
        $this->code_postal = $cp;

        $this->loadMatchingObject();

        return $this;
    }

    /**
     * @param string $cp
     *
     * @return $this
     */
    public function loadFirstByCP(string $cp): self
    {
        $this->code_postal = $cp;

        $this->loadMatchingObject();

        return $this;
    }
}
