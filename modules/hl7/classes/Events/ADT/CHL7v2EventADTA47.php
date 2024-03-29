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
 * Class CHL7v2EventADTA47
 * A47 - Change patient identifier list
 */
class CHL7v2EventADTA47 extends CHL7v2EventADT implements CHL7EventADTA30
{

    /** @var string */
    public $code = "A47";

    /** @var string */
    public $struct_code = "A30";

    /**
     * Build A47 event
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
        if (isset($patient->_disable_insi_identity_source)) {
            $this->addMRG($patient);
        }
        if (isset($patient->_patient_elimine)) {
            $this->addMRG($patient->_patient_elimine);
        }
    }
}
