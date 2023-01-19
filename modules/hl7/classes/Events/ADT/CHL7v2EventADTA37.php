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
 * Class CHL7v2EventADTA37
 * A37 - Unlink patient information
 */
class CHL7v2EventADTA37 extends CHL7v2EventADT implements CHL7EventADTA37 {

  /** @var string */
  public $code        = "A37";

  /** @var string */
  public $struct_code = "A37";

  /**
   * Get event planned datetime
   *
   * @param CMbObject $object Admit
   *
   * @return DateTime Event occured
   */
  function getEVNOccuredDateTime(CMbObject $object) {
    return CMbDT::dateTime();
  }

  /**
   * Build A37 event
   *
   * @param CPatient $patient Person
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($patient) {
    parent::build($patient);
    
    // Patient Identification
    $this->addPID($patient);

    /* @toto old ? */
    $patient_link = new CPatient();
    $patient_link->load($patient->_old->patient_link_id);
    
    // Patient link Identification
    $this->addPID($patient_link);
  }
}