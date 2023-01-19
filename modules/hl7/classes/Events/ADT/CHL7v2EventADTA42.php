<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\ADT;

use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2EventADTA42
 * A46 - Change Patient ID
 */
class CHL7v2EventADTA42 extends CHL7v2EventADT implements CHL7EventADTA30 {

  /** @var string */
  public $code        = "A42";

  /** @var string */
  public $struct_code = "A39";

  /**
   * Build A42 event
   *
   * @param CSejour $sejour Sejour
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($sejour) {
    parent::build($sejour);
    $patient = $sejour->_ref_patient;

    // Patient Identification
    $this->addPID($patient);
    
    // Patient Additional Demographic
    $this->addPD1($patient);
    
    // Merge Patient Information
    $this->addMRG($sejour->_sejour_elimine);

    $this->addPV1($sejour);
  }
}