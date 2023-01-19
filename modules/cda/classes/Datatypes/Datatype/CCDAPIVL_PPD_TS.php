<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Datatype;

use Ox\Interop\Cda\Datatypes\Base\CCDA_base_bl;
use Ox\Interop\Cda\Datatypes\Voc\CCDACalendarCycle;

/**
 * CCDAPIVL_PPD_TS class
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
class CCDAPIVL_PPD_TS extends CCDASXCM_PPD_TS
{

    /**
     * A prototype of the repeating interval specifying the
     * duration of each occurrence and anchors the periodic
     * interval sequence at a certain point in time.
     *
     * @var CCDAIVL_PPD_TS
     */
    public $phase;

    /**
     * A time duration specifying a reciprocal measure of
     * the frequency at which the periodic interval repeats.
     *
     * @var CCDAPPD_PQ
     */
    public $period;

    /**
     * Specifies if and how the repetitions are aligned to
     * the cycles of the underlying calendar (e.g., to
     * distinguish every 30 days from "the 5th of every
     * month".) A non-aligned periodic interval recurs
     * independently from the calendar. An aligned periodic
     * interval is synchronized with the calendar.
     *
     * @var CCDACalendarCycle
     */
    public $alignment;

    /**
     * Indicates whether the exact timing is up to the party
     * executing the schedule (e.g., to distinguish "every 8
     * hours" from "3 times a day".)
     *
     * @var CCDA_base_bl
     */
    public $institutionSpecified;

    /**
     * Getter Alignment
     *
     * @return CCDACalendarCycle
     */
    public function getAlignment()
    {
        return $this->alignment;
    }

    /**
     * Setter Alignment
     *
     * @param String $alignment String
     *
     * @return void
     */
    public function setAlignment($alignment)
    {
        if (!$alignment) {
            $this->alignment = null;

            return;
        }
        $cale = new CCDACalendarCycle();
        $cale->setData($alignment);
        $this->alignment = $cale;
    }

    /**
     * Getter InstitutionSpecified
     *
     * @return CCDA_base_bl
     */
    public function getInstitutionSpecified()
    {
        return $this->institutionSpecified;
    }

    /**
     * Setter InstitutionSpecified
     *
     * @param String $institutionSpecified String
     *
     * @return void
     */
    public function setInstitutionSpecified($institutionSpecified)
    {
        if (!$institutionSpecified) {
            $this->institutionSpecified = null;

            return;
        }
        $bl = new CCDA_base_bl();
        $bl->setData($institutionSpecified);
        $this->institutionSpecified = $bl;
    }

    /**
     * Getter Period
     *
     * @return CCDAPPD_PQ
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Setter Period
     *
     * @param CCDAPPD_PQ $period \CCDAPPD_PQ
     *
     * @return void
     */
    public function setPeriod($period)
    {
        $this->period = $period;
    }

    /**
     * Getter Phase
     *
     * @return CCDAIVL_PPD_TS
     */
    public function getPhase()
    {
        return $this->phase;
    }

    /**
     * Setter Phase
     *
     * @param CCDAIVL_PPD_TS $phase \CCDAIVL_PPD_TS
     *
     * @return void
     */
    public function setPhase($phase)
    {
        $this->phase = $phase;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props                         = parent::getProps();
        $props["phase"]                = "CCDAIVL_PPD_TS xml|element max|1";
        $props["period"]               = "CCDAPPD_PQ xml|element max|1";
        $props["alignment"]            = "CCDACalendarCycle xml|attribute";
        $props["institutionSpecified"] = "CCDA_base_bl xml|attribute default|false";

        return $props;
    }
}
