<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Mediboard\Patients\CPatient;

/**
 * Class CHL7v2SegmentDSC
 * DSC - Represents an HL7 DSC message segment (Continuation Pointer)
 */

class CHL7v2SegmentDSC extends CHL7v2Segment {

  /** @var string */
  public $name    = "DSC";


  /** @var CPatient */
  public $patient;

  /**
   * Build DSC segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);

    $patient = $this->patient;

    // DSC-1: Continuation Pointer (ST) (optional)
    $data[] = $patient->_pointer;
    
    // DSC-2: Continuation Style (ID) (optional)
    $data[] =  "I";

    $this->fill($data);
  }
}