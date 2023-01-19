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
 * Class CHL7v2EventADTA03
 * A03 - Discharge/end visit
 */
class CHL7v2EventADTA03 extends CHL7v2EventADT implements CHL7EventADTA03
{

    /** @var string */
    public $code = "A03";

    /** @var string */
    public $struct_code = "A03";

    /**
     * Get event planned datetime
     *
     * @param CSejour|CMbObject $object Admit
     *
     * @return DateTime Event occured
     */
    function getEVNOccuredDateTime(CMbObject $object)
    {
        return $object->sortie_reelle;
    }

    /**
     * Build A03 event
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

        if ($this->version > "2.4") {
            // Next of Kin / Associated Parties
            $this->addNK1s($patient);
        }

        // Patient Visit
        $this->addPV1($sejour);

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
            $this->addZBE($sejour);
        }
    }
}
