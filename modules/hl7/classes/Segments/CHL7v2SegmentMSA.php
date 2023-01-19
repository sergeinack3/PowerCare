<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Acknowledgment;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Segment;

/**
 * Class CHL7v2SegmentMSA 
 * MSA - Represents an HL7 MSA message segment (Message Acknowledgment)
 */

class CHL7v2SegmentMSA extends CHL7v2Segment {

  /** @var string */
  public $name           = "MSA";
  

  /** @var CHL7v2Acknowledgment */
  public $acknowledgment;

  /**
   * Build MSA segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   * @throws CHL7v2Exception
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);
    
    $acknowledgment = $this->acknowledgment;
    
    $data = array();

    // MSA-1: Acknowledgment Code (ID)
    // Table - 0008
    // AA  - Original mode: Application Accept - Enhanced mode: Application acknowledgment: Accept   
    // AE  - Original mode: Application Error - Enhanced mode: Application acknowledgment: Error   
    // AR  - Original mode: Application Reject - Enhanced mode: Application acknowledgment: Reject   
    // CA  - Enhanced mode: Accept acknowledgment: Commit Accept   
    // CE  - Enhanced mode: Accept acknowledgment: Commit Error  
    // CR  - Enhanced mode: Accept acknowledgment: Commit Reject 
    $data[] = $acknowledgment->ack_code; 
    
    // MSA-2: Message Control ID
    $data[] = $acknowledgment->message_control_id; 
    
    // MSA-3: Text Message (ST) (optional)
    $data[] = null;
    
    // MSA-4: Expected Sequence Number (NM) (optional)
    $data[] = null;
    
    // MSA-5: Delayed Acknowledgment Type (ID) (optional)
    $data[] = null;
    
    // MSA-6: Error Condition (CE) (optional)
    $data[] = null;
    
    $this->fill($data);
  }
}