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
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2EventADTA12
 * A12 - Cancel transfer
 */
class CHL7v2EventADTA12 extends CHL7v2EventADT implements CHL7EventADTA12
{

    /** @var string */
    public $code = "A12";

    /** @var string */
    public $struct_code = "A12";

    /**
     * Get event planned datetime
     *
     * @param CMbObject $object Affectation
     *
     * @return DateTime Event occured
     */
    function getEVNOccuredDateTime(CMbObject $object)
    {
        return CMbDT::dateTime();
    }

    /**
     * Build A12 event
     *
     * @param CAffectation $affectation Affectation
     *
     * @return void
     * @see parent::build()
     *
     */
    function build($affectation)
    {
        /** @var CSejour $sejour */
        $sejour                       = $affectation->_ref_sejour;
        $sejour->_ref_hl7_affectation = $affectation;

        parent::build($affectation);

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
