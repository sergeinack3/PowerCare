<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21\Segments;

use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Segment;

/**
 * Class CHPRSegmentL
 * L - Represents an HPR L message segment (Message Footer)
 */

class CHPRSegmentL extends CHL7v2Segment {
  public $name = "L";

  /**
   * @inheritdoc
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);
        
    $data = array();

    // L-1 : Segment Row (optional)
    $data[] = null;
    
    // L-2 : Not Use (optional)
    $data[] = null;
    
    // L-3 : Number Segment P (optional)
    $data[] = null;
    
    // L-4 : Number Segment of Message (optional)
    $data[] = null;
    
    // L-5 : Lot Number (optional)
    $data[] = null;
    
    $this->fill($data);
  }
}
