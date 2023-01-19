<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\ADT;

use DateTime;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2EventADTA27
 * A26 - Cancel Pending Transfer
 */
class CHL7v2EventADTA26 extends CHL7v2EventADT implements CHL7EventADTA21
{

    /** @var string */
    public $code = "A26";

    /** @var string */
    public $struct_code = "A21";

    /**
     * Get event planned datetime
     *
     * @param CMbObject $object Admit
     *
     * @return DateTime Event occured
     */
    function getEVNOccuredDateTime(CMbObject $object)
    {
        return CMbDT::dateTime();
    }

    /**
     * Build A27 event
     *
     * @param CSejour $sejour Admit
     *
     * @return void
     * @see parent::build()
     *
     */
    function build($sejour)
    {
        parent::build($sejour);

        /** @var CPatient $patient */
        $patient = $sejour->_ref_patient;
        // Patient Identification
        $this->addPID($patient, $sejour);

        // Patient Additional Demographic
        $this->addPD1($patient);

        // Patient Visit
        $this->addPV1($sejour);

        // Patient Visit - Additionale Info
        $this->addPV2($sejour);

        // Build specific segments (i18n)
        $this->buildI18nSegments($sejour);

        // Other doctors
        if ($this->version > "2.3.1") {
            $this->addROLs($patient);
        }
    }

    /**
     * Build i18n segements
     *
     * @param CSejour $sejour Admit
     *
     * @return void
     * @see parent::buildI18nSegments()
     *
     */
    function buildI18nSegments($sejour)
    {
        // Movement segment only used within the context of the "Historic Movement Management"
        if ($this->_receiver->_configs["iti31_historic_movement"]) {
            //$this->addZBE($sejour);
        }
    }
}
