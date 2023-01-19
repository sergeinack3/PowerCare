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
use Ox\Mediboard\Cabinet\CConsultation;

/**
 * Class CHL7v2SegmentRGS
 * RGS - Represents an HL7 RGS message segment (Resource Group)
 */

class CHL7v2SegmentRGS extends CHL7v2Segment {

  /** @var string */
  public $name   = "RGS";

  /** @var null */
  public $set_id;
  

  /** @var CConsultation */
  public $appointment;

  /**
   * Build RGS segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);
        
    $data = array();
    
    // RGS-1: Set ID - RGS (SI) 
    $data[] = $this->set_id;
    
    // RGS-2: Segment Action Code (ID) (optional)
    $data[] = $this->getSegmentActionCode($event);
    
    // RGS-3: Resource Group ID (CE) (optional)
    $data[] = $this->appointment->_id;
    
    $this->fill($data);
  }  
} 