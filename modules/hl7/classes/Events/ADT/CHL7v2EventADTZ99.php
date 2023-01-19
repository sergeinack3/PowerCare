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
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2EventADTZ99
 * Z99 - Change admit - HL7
 */
class CHL7v2EventADTZ99 extends CHL7v2EventADT implements CHL7EventADTA01
{

    /** @var string */
    public $code = "Z99";

    /** @var string */
    public $struct_code = "A01";

    /**
     * Get event planned datetime
     *
     * @param CMbObject $object Object
     *
     * @return DateTime Event occured
     */
    function getEVNOccuredDateTime(CMbObject $object)
    {
        return CMbDT::dateTime();
    }

    /**
     * Build Z99 event
     *
     * @param CMbObject $object Object
     *
     * @return void
     * @see parent::build()
     *
     */
    function build($object)
    {
        if ($object instanceof CAffectation) {
            $affectation = $object;

            $sejour                       = $affectation->_ref_sejour;
            $sejour->_ref_hl7_affectation = $affectation;

            parent::build($affectation);
        } else {
            $sejour = $object;

            parent::build($sejour);
        }

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

        // Observation/Result
        $this->addOBXs($patient);

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
        // Movement segment only used within the context of the "Historic Movement Management"
        if ($this->_receiver->_configs["iti31_historic_movement"]) {
            $this->addZBE($sejour);
        }
    }
}
