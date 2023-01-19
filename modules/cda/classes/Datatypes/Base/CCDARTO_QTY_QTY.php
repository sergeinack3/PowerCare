<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

use Ox\Core\CClassMap;

/**
 * Classe pour répésenter des ratios de quantité
 */
class CCDARTO_QTY_QTY extends CCDAQTY
{

    /**
     * The quantity that is being divided in the ratio.  The
     * default is the integer number 1 (one).
     *
     * @var CCDAQTY
     */
    public $numerator;

    /**
     * The quantity that devides the numerator in the ratio.
     * The default is the integer number 1 (one).
     * The denominator must not be zero.
     *
     * @var CCDAQTY
     */
    public $denominator;

    /**
     * retourne le nom de la classe
     *
     * @return string
     */
    function getNameClass()
    {
        $name = CClassMap::getSN($this);
        $name = substr($name, 4);

        return $name;
    }

    /**
     * Getter denominator
     *
     * @return CCDAQTY
     */
    public function getDenominator()
    {
        return $this->denominator;
    }

    /**
     * Setter denominator
     *
     * @param CCDAQTY $denominator \CCDAQTY
     *
     * @return void
     */
    public function setDenominator($denominator)
    {
        $this->denominator = $denominator;
    }

    /**
     * Getter numerator
     *
     * @return CCDAQTY
     */
    public function getNumerator()
    {
        return $this->numerator;
    }

    /**
     * Setter numerator
     *
     * @param CCDAQTY $numerator \CCDAQTY
     *
     * @return void
     */
    public function setNumerator($numerator)
    {
        $this->numerator = $numerator;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props                = parent::getProps();
        $props["numerator"]   = "CCDAQTY xml|element default|1 abstract";
        $props["denominator"] = "CCDAQTY xml|element default|1 abstract";

        return $props;
    }
}
