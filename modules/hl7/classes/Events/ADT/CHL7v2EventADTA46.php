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
 * Class CHL7v2EventADTA46
 * A46 - Change Patient ID
 */
class CHL7v2EventADTA46 extends CHL7v2EventADT implements CHL7EventADTA30 {

  /** @var string */
  public $code        = "A46";

  /** @var string */
  public $struct_code = "A30";

  /**
   * Build A46 event
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
    
    // Patient Additional Demographic
    $this->addPD1($patient);
    
    // Merge Patient Information
    $this->addMRG($patient->_patient_elimine);
  }
}