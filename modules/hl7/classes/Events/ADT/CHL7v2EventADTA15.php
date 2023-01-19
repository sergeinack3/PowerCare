<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\ADT;

use DateTime;
use Ox\Core\CMbObject;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2EventADTA15
 * A15 - Pending Transfer
 */
class CHL7v2EventADTA15 extends CHL7v2EventADT implements CHL7EventADTA15
{

    /** @var string */
    public $code = "A15";

    /** @var string */
    public $struct_code = "A15";

    /**
     * Get event planned datetime
     *
     * @param CMbObject $object Admit
     *
     * @return DateTime Event planned
     */
    function getEVNPlannedDateTime(CMbObject $object)
    {
        return null;
    }

    /**
     * Build A15 event
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

        // Médecin traitant
        if ($this->version > "2.3.1") {
            $this->addMedecinTraitant($patient, $sejour);
        }

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
    }
}
