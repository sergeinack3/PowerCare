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
 * Class CHL7v2EventADTA24
 * A24 - Link patient information
 */
class CHL7v2EventADTA24 extends CHL7v2EventADT implements CHL7EventADTA24
{

    /** @var string */
    public $code = "A24";

    /** @var string */
    public $struct_code = "A24";

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
     * Build A24 event
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

        // Patient link Identification
        $this->addPID($patient->_patient_link);
    }
}
