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
 * Class CHL7v2SegmentRCP
 * RCP - Represents an HL7 RCP message segment (Response Control Parameter)
 */

class CHL7v2SegmentRCP extends CHL7v2Segment {

  /** @var string */
  public $name    = "RCP";


  /** @var CPatient */
  public $patient;

  /**
   * Build RCP segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);

    $patient = $this->patient;

    $quantity_limited_request = isset($patient->_quantity_limited_request) ? $patient->_quantity_limited_request : null;

    // RCP-1: Query Priority (ID) (optional)
    $data[] = "I";
    
    // RCP-2: Quantity Limited Request (CQ) (optional)
    if ($quantity_limited_request) {
      $data[] =  array (
        array (
          $quantity_limited_request,
          "RD"
        )
      );
    }
    else {
      $data[] = null;
    }
    
    // RCP-3: Response Modality (CE) (optional)
    $data[] = null;

    // RCP-4: Execution and Delivery Time (TS) (optional)
    $data[] = null;

    // RCP-5: Modify Indicator (ID) (optional)
    $data[] = null;

    // RCP-6: Sort-by Field (SRT) (optional repeating)
    $data[] = null;

    // RCP-7: Segment group inclusion (ID) (optional repeating)
    $data[] = null;

    $this->fill($data);
  }
}