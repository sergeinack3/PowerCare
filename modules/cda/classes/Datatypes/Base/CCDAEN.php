<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * A name for a person, organization, place or thing. A
 * sequence of name parts, such as given name or family
 * name, prefix, suffix, etc. Examples for entity name
 * values are "Jim Bob Walton, Jr.", "Health Level Seven,
 * Inc.", "Lake Tahoe", etc. An entity name may be as simple
 * as a character string or may consist of several entity name
 * parts, such as, "Jim", "Bob", "Walton", and "Jr.", "Health
 * Level Seven" and "Inc.", "Lake" and "Tahoe".
 */
class CCDAEN extends CCDAANY
{

    public $delimiter = [];
    public $family    = [];
    public $given     = [];
    public $prefix    = [];
    public $suffix    = [];

    /**
     * An interval of time specifying the time during which
     * the name is or was used for the entity. This
     * accomodates the fact that people change names for
     * people, places and things.
     *
     * @var CCDAIVL_TS
     */
    public $validTime;

    /**
     * A set of codes advising a system or user which name
     * in a set of like names to select for a given purpose.
     * A name without specific use code might be a default
     * name useful for any purpose, but a name with a specific
     * use code would be preferred for that respective purpose.
     *
     * @var CCDAset_EntityNameUse
     */
    public $use;

    /**
     * Getter use
     *
     * @return CCDAset_EntityNameUse
     */
    public function getUse()
    {
        return $this->use;
    }

    /**
     * Setter use
     *
     * @param String[] $use String[]
     *
     * @return void
     */
    public function setUse($use)
    {
        $setEn = new CCDAset_EntityNameUse();
        foreach ($use as $_use) {
            $setEn->addData($_use);
        }
        $this->use = $setEn;
    }

    /**
     * Getter validTime
     *
     * @return CCDAIVL_TS
     */
    public function getValidTime()
    {
        return $this->validTime;
    }

    /**
     * Setter validTime
     *
     * @param CCDAIVL_TS $validTime \CCDAIVL_TS
     *
     * @return void
     */
    public function setValidTime($validTime)
    {
        $this->validTime = $validTime;
    }

    /**
     * retourne le tableau d'instance du champ spécifié
     *
     * @param String $name String
     *
     * @return mixed
     */
    function get($name)
    {
        return $this->$name;
    }

    /**
     * Efface le tableau d'instance du champ spécifié
     *
     * @param String $name String
     *
     * @return void
     */
    function resetListdata($name)
    {
        $this->$name = [];
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props              = parent::getProps();
        $props["delimiter"] = "CCDA_en_delimiter xml|element";
        $props["family"]    = "CCDA_en_family xml|element";
        $props["given"]     = "CCDA_en_given xml|element";
        $props["prefix"]    = "CCDA_en_prefix xml|element";
        $props["suffix"]    = "CCDA_en_suffix xml|element";
        $props["validTime"] = "CCDAIVL_TS xml|element max|1";
        $props["use"]       = "CCDAset_EntityNameUse xml|attribute";
        $props["data"]      = "str xml|data";

        return $props;
    }

    /**
     * Ajoute l'instance dans le champ spécifié
     *
     * @param String $name  String
     * @param mixed  $value mixed
     *
     * @return void
     */
    function append($name, $value)
    {
        array_push($this->$name, $value);
    }
}
