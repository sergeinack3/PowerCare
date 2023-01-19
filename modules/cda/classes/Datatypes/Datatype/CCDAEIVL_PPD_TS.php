<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Datatype;

use Ox\Interop\Cda\Datatypes\Base\CCDAEIVL_event;

/**
 * CCDAEIVL_PPD_TS class
 *
 * Note: because this type is defined as an extension of SXCM_T,
 * all of the attributes and elements accepted for T are also
 * accepted by this definition.  However, they are NOT allowed
 * by the normative description of this type.  Unfortunately,
 * we cannot write a general purpose schematron contraints to
 * provide that extra validation, thus applications must be
 * aware that instance (fragments) that pass validation with
 * this might might still not be legal.
 *
 */
class CCDAEIVL_PPD_TS extends CCDASXCM_PPD_TS
{

    /**
     * A code for a common (periodical) activity of daily
     * living based on which the event related periodic
     * interval is specified.
     *
     * @var CCDAEIVL_event
     */
    public $event;

    /**
     * An interval of elapsed time (duration, not absolute
     * point in time) that marks the offsets for the
     * beginning, width and end of the event-related periodic
     * interval measured from the time each such event
     * actually occurred.
     *
     * @var CCDAIVL_PPD_PQ
     */
    public $offset;

    /**
     * Getter Event
     *
     * @return CCDAEIVL_event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Setter Event
     *
     * @param CCDAEIVL_event $event \CCDAEIVL_event
     *
     * @return void
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * Getter offset
     *
     * @return CCDAIVL_PPD_PQ
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Setter offset
     *
     * @param CCDAIVL_PPD_PQ $offset \CCDAIVL_PPD_PQ
     *
     * @return void
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props           = parent::getProps();
        $props["event"]  = "CCDAEIVL_event xml|element max|1";
        $props["offset"] = "CCDAIVL_PPD_PQ xml|element max|1";

        return $props;
    }
}
