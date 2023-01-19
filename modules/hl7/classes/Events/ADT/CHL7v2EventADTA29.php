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

/**
 * Class CHL7v2EventADTA29
 * A29 - Delete person information
 */
class CHL7v2EventADTA29 extends CHL7v2EventADT implements CHL7EventADTA21
{

    /** @var string */
    public $code = "A29";

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
     * Build A29 event
     *
     * @param CPatient $patient Person
     *
     * @return void
     * @see parent::build()
     *
     */
    function build($patient)
    {
        parent::build($patient);

        // Patient Identification
        $this->addPID($patient);

        // Patient Additional Demographic
        $this->addPD1($patient);

        // Médecin traitant
        if ($this->version > "2.3.1") {
            $this->addMedecinTraitant($patient, $sejour);
        }

        // Next of Kin / Associated Parties
        $this->addNK1s($patient);

        // Patient Visit
        $this->addPV1();

        // Other doctors
        if ($this->version > "2.3.1") {
            $this->addROLs($patient);
        }
    }
}
