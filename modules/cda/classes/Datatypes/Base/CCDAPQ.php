<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * A dimensioned quantity expressing the result of a
 * measurement act.
 */
class CCDAPQ extends CCDAQTY
{

    /**
     * An alternative representation of the same physical
     * quantity expressed in a different unit, of a different
     * unit code system and possibly with a different value.
     *
     * @var array
     */
    public $translation = [];

    /**
     * The unit of measure specified in the Unified Code for
     * Units of Measure (UCUM)
     * [http://aurora.rg.iupui.edu/UCUM].
     *
     * @var CCDA_base_cs
     */
    public $unit;

    /**
     * Getter translation
     *
     * @return array
     */
    public function getTranslation()
    {
        return $this->translation;
    }

    /**
     * Getter unit
     *
     * @return CCDA_base_cs
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * Setter unit
     *
     * @param String $unit String
     *
     * @return void
     */
    public function setUnit($unit)
    {
        if (!$unit) {
            $this->unit = null;

            return;
        }
        $uni = new CCDA_base_cs();
        $uni->setData($unit);
        $this->unit = $uni;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props                = parent::getProps();
        $props["translation"] = "CCDAPQR xml|element";
        $props["value"]       = "CCDA_base_real xml|attribute";
        $props["unit"]        = "CCDA_base_cs xml|attribute default|1";

        return $props;
    }

    /**
     * Setter value
     *
     * @param String $value String
     *
     * @return void
     */
    public function setValue($value)
    {
        if ($value === null) {
            $this->value = null;

            return;
        }
        $val = new CCDA_base_real();
        $val->setData($value);
        $this->value = $val;
    }

    /**
     * Ajoute une instance de translation
     *
     * @param CCDAPQR $translation \CCDAPQR
     *
     * @return void
     */
    public function appendTranslation($translation)
    {
        $this->translation[] = $translation;
    }
}
