<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\SWF;

use Exception;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Galaxie\CGalaxie;

/**
 * Class CHL7v2EventSIUS16
 * S16 - Appointment discontinuation
 */
class CHL7v2EventSIUS16 extends CHL7v2EventSIU implements CHL7EventSIUS12 {

  /** @var string */
  public $code = "S16";

  /**
   * Build S15 event
   *
   * @param CConsultation $appointment Appointment
   *
   * @return void
   * @throws CHL7v2Exception
   *
   */
  function build($appointment) {
    parent::build($appointment);

    $receiver = $this->_receiver;

    // Scheduling Activity Information
    $this->addSCH($appointment);
    
    $patient = $appointment->loadRefPatient();
    // Patient Identification
    $this->addPID($patient);

    // Patient Additional Demographic
    $this->addPD1($patient);

    // Patient Visit
    // Anaesthesia appointment
    if ($appointment->_is_anesth) {
      $consult_anesth = $appointment->loadRefConsultAnesth();

      $sejour = isset($consult_anesth->loadRefOperation()->loadRefSejour()->_id) ?
        $consult_anesth->_ref_operation->_ref_sejour : $consult_anesth->_ref_sejour;

      $this->addPV1($sejour);
    }
    else {
      if ($appointment->sejour_id) {
        $sejour = $appointment->loadRefSejour();
        $this->addPV1($sejour);
      }
    }

    // Resource Group
    $this->addRGS($appointment);

    // Appointment Information - General Resource
    $this->addAIG($appointment);

    // Appointment Information - Location Resource
    $this->addAIL($appointment);

    if (CModule::getActive("galaxie") && ($receiver->type === CInteropActor::ACTOR_GALAXIE)) {
      CGalaxie::addSegmentZTG($appointment, $this);
    }
  }
}
