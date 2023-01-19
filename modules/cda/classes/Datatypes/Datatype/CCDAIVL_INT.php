<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Datatype;

use Ox\Interop\Cda\Datatypes\Base\CCDAINT;

/**
 * CCDAIVL_INT class
 * Choix entre une séquence(low(1.1), [width(0.1)|high(0.1)]), element high(1.1), séquence(width(1.1), high(0.1)),
 * séquence(center(1.1), width(0.1))
 */
class CCDAIVL_INT extends CCDASXCM_INT
{

    /**
     * The low limit of the interval.
     *
     * @var CCDAIVXB_INT
     */
    public $low;
    /**
     * The difference between high and low boundary. The
     * purpose of distinguishing a width property is to
     * handle all cases of incomplete information
     * symmetrically. In any interval representation only
     * two of the three properties high, low, and width need
     * to be stated and the third can be derived.
     *
     * @var CCDAINT
     */
    public $width;
    /**
     * The high limit of the interval.
     *
     * @var CCDAIVXB_INT
     */
    public $high;
    /**
     * The arithmetic mean of the interval (low plus high
     * divided by 2). The purpose of distinguishing the center
     * as a semantic property is for conversions of intervals
     * from and to point values.
     *
     * @var CCDAINT
     */
    public  $center;
    private $propsHigh   = "CCDAIVXB_INT xml|element max|1";
    private $propsWidth  = "CCDAINT xml|element max|1";
    private $propsLow    = "CCDAIVXB_INT xml|element max|1";
    private $propsCenter = "CCDAINT xml|element max|1";
    private $_order      = null;

    /**
     * Getter center
     *
     * @return CCDAINT
     */
    public function getCenter()
    {
        return $this->center;
    }

    /**
     * Setter center
     *
     * @param CCDAINT $center \CCDAINT
     *
     * @return void
     */
    public function setCenter($center)
    {
        $this->setOrder("center");
        $this->center = $center;
    }

    /**
     * Getter high
     *
     * @return CCDAIVXB_INT
     */
    public function getHigh()
    {
        return $this->high;
    }

    /**
     * Setter High
     *
     * @param CCDAIVXB_INT $high \CCDAIVXB_INT
     *
     * @return void
     */
    public function setHigh($high)
    {
        $this->setOrder("high");
        $this->high = $high;
    }

    /**
     * Getter low
     *
     * @return CCDAIVXB_INT
     */
    public function getLow()
    {
        return $this->low;
    }

    /**
     * Setter low
     *
     * @param CCDAIVXB_INT $low \CCDAIVXB_INT
     *
     * @return void
     */
    public function setLow($low)
    {
        $this->setOrder("low");
        $this->low = $low;
    }

    /**
     * Getter width
     *
     * @return CCDAINT
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Setter width
     *
     * @param CCDAINT $width \CCDAINT
     *
     * @return void
     */
    public function setWidth($width)
    {
        $this->setOrder("width");
        $this->width = $width;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props = parent::getProps();
        switch ($this->_order) {
            case "low":
                $props["low"]   = $this->propsLow;
                $props["width"] = $this->propsWidth;
                $props["high"]  = $this->propsHigh;
                break;
            case "high":
                $props["high"] = $this->propsHigh;
                break;
            case "width":
                $props["width"] = $this->propsWidth;
                $props["high"]  = $this->propsHigh;
                break;
            case "center":
                $props["center"] = $this->propsCenter;
                $props["width"]  = $this->propsWidth;
                break;
            default:
                $props["low"]    = $this->propsLow;
                $props["width"]  = $this->propsWidth;
                $props["high"]   = $this->propsHigh;
                $props["center"] = $this->propsCenter;
        }

        return $props;
    }

    /**
     * Affecte la séquence choisi
     *
     * @param String $nameVar String
     *
     * @return void
     */
    function setOrder($nameVar)
    {
        if (empty($this->_order) || empty($nameVar)) {
            $this->_order = $nameVar;
        }
    }
}
