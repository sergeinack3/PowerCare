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
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2SegmentACC
 * ACC - Represents an HL7 ACC message segment (Accident)
 */

class CHL7v2SegmentACC extends CHL7v2Segment {

  /** @var string */
  public $name   = "ACC";
  

  /** @var CSejour */
  public $sejour;

  /**
   * Build ACC segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);

    // ACC-1: Accident Date/Time (TS) <b>optional </b>
    $data[] = null;
    
    // ACC-2: Accident Code (CE) <b>optional </b>
    $data[] = null;
    
    // ACC-3: Accident Location (ST) <b>optional </b>
    $data[] = null;
    
    // ACC-4: Auto Accident State (CE) <b>optional </b>
    $data[] = null;
    
    // ACC-5: Accident Job Related Indicator (ID) <b>optional </b>
    $data[] = null;
    
    // ACC-6: Accident Death Indicator (ID) <b>optional </b>
    $data[] = null;
    
    // ACC-7: Entered By (XCN) <b>optional </b>
    $data[] = null;
    
    // ACC-8: Accident Description (ST) <b>optional </b>
    $data[] = null;
    
    // ACC-9: Brought In By (ST) <b>optional </b>
    $data[] = null;
    
    // ACC-10: Police Notified Indicator (ID) <b>optional </b>
    $data[] = null;
    
    // ACC-11: Accident Address (XAD) <b>optional </b>
    $data[] = null;
    
    $this->fill($data);
  }
}