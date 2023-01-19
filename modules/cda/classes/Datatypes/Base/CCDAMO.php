<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * A monetary amount is a quantity expressing the amount of
 * money in some currency. Currencies are the units in which
 * monetary amounts are denominated in different economic
 * regions. While the monetary amount is a single kind of
 * quantity (money) the exchange rates between the different
 * units are variable.  This is the principle difference
 * between physical quantity and monetary amounts, and the
 * reason why currency units are not physical units.
 */
class CCDAMO extends CCDAQTY
{

    /**
     * The currency unit as defined in ISO 4217.
     *
     * @var CCDA_base_cs
     */
    public $currency;

    /**
     * Getter currency
     *
     * @return CCDA_base_cs
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Setter currency
     *
     * @param String $currency String
     *
     * @return void
     */
    public function setCurrency($currency)
    {
        if (!$currency) {
            $this->currency = null;

            return;
        }
        $cs = new CCDA_base_cs();
        $cs->setData($currency);
        $this->currency = $cs;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props             = parent::getProps();
        $props["value"]    = "CCDA_base_real xml|attribute";
        $props["currency"] = "CCDA_base_cs xml|attribute";

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
        if (!$value) {
            $this->value = null;

            return;
        }
        $real = new CCDA_base_real();
        $real->setData($value);
        $this->value = $real;
    }
}
