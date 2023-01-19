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
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2EventADTA44
 * A44 - Move account information - patient account number
 */
class CHL7v2EventADTA44 extends CHL7v2EventADT implements CHL7EventADTA43 {

  /** @var string */
  public $code        = "A44";

  /** @var string */
  public $struct_code = "A43";

  /**
   * Get event planned datetime
   *
   * @param CMbObject $object Admit
   *
   * @return DateTime Event occured
   */
  function getEVNPlannedDateTime(CMbObject $object) {
    return CMbDT::dateTime();
  }

  /**
   * Build A44 event
   *
   * @param CSejour $sejour Admit
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($sejour) {
    parent::build($sejour);

    $patient = $sejour->_ref_patient;
    // Patient Identification
    $this->addPID($patient, $sejour);

    // Patient Additional Demographic
    $this->addPD1($patient);

    $old_patient = new CPatient();
    $old_patient->load($sejour->_old->patient_id);
    // Merge Patient Information
    $this->addMRG($old_patient);
  }
}