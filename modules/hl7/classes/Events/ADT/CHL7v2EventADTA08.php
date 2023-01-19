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
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2EventADTA08
 * A08 - Update Patient Information
 */
class CHL7v2EventADTA08 extends CHL7v2EventADT implements CHL7EventADTA01
{

    /** @var string */
    public $code = "A08";

    /** @var string */
    public $struct_code = "A01";

    /**
     * Get event planned datetime
     *
     * @param CMbObject|CAffectation $object Object
     *
     * @return DateTime Event occured
     */
    function getEVNOccuredDateTime(CMbObject $object)
    {
        return (($object instanceof CAffectation) ? $object->entree : ($object instanceof CSejour)) ? $object->entree : CMbDT::dateTime(
        );
    }

    /**
     * Build A08 event
     *
     * @param CMbObject $object Object
     *
     * @return void
     * @throws CHL7v2Exception
     * @see parent::build()
     *
     */
    function build($object)
    {
        // Dans le cas où le A08 est dédié à la mise à jour des données du patient
        if ($object instanceof CPatient) {
            $patient = $object;

            /** @var CSejour $sejour */
            $sejour = isset($patient->_ref_sejour) ? $patient->_ref_sejour : null;

            if (!$sejour || !$sejour->_id) {
                parent::build($patient);

                // Patient Identification
                $this->addPID($patient);

                // Patient Additional Demographic
                $this->addPD1($patient);

                if ($this->version > "2.3.1") {
                    // Doctors
                    $this->addROLs($patient);
                }

                // Next of Kin / Associated Parties
                $this->addNK1s($patient);

                // Patient Visit
                $this->addPV1();

                return;
            } else {
                $object = $sejour;
                $object->loadRefPatient();
            }
        }

        if ($object instanceof CAffectation) {
            $affectation = $object;

            /** @var CSejour $sejour */
            $sejour                       = $affectation->_ref_sejour;
            $sejour->_ref_hl7_affectation = $affectation;

            /** @var CPatient $patient */
            $patient = $sejour->_ref_patient;

            parent::build($affectation);
        } else {
            $sejour  = $object;
            $patient = $sejour->_ref_patient;

            parent::build($sejour);
        }

        // Patient Identification
        $this->addPID($patient, $sejour);

        // Patient Additional Demographic
        $this->addPD1($patient);

        // Next of Kin / Associated Parties
        $this->addNK1s($patient);

        // Patient Visit
        $this->addPV1($sejour);

        // Patient Visit - Additionale Info
        $this->addPV2($sejour);
    }
}
