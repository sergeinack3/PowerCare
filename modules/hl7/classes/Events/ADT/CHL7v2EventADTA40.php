<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\ADT;

use Ox\Mediboard\Patients\CPatient;

/**
 * Class CHL7v2EventADTA40
 * A40 - Merge patient
 */
class CHL7v2EventADTA40 extends CHL7v2EventADT implements CHL7EventADTA39
{

    /** @var string */
    public $code = "A40";

    /** @var string */
    public $struct_code = "A39";

    /**
     * Build A40 event
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

        // Merge Patient Information
        $this->addMRG($patient->_patient_elimine);
    }
}
