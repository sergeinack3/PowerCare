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
 * Class CHL7v2EventADTA16
 * A16 - Pending Discharge
 */
class CHL7v2EventADTA16 extends CHL7v2EventADT implements CHL7EventADTA16
{

    /** @var string */
    public $code = "A16";

    /** @var string */
    public $struct_code = "A16";

    /**
     * Get event planned datetime
     *
     * @param CSejour|CMbObject $object Admit
     *
     * @return DateTime Event planned
     */
    function getEVNPlannedDateTime(CMbObject $object)
    {
        return $object->sortie_reelle;
    }

    /**
     * Build A16 event
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

        // Next of Kin / Associated Parties
        $this->addNK1s($patient);

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

        // Guarantor
        $this->addGT1($patient);
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
