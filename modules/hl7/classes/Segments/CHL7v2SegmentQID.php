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
 * Class CHL7v2SegmentQID
 * QID - Represents an HL7 QID message segment (Query Identification)
 */

class CHL7v2SegmentQID extends CHL7v2Segment {

  /** @var string */
  public $name    = "QID";


  /** @var CPatient */
  public $patient;

  /**
   * Build QID segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);

    // QID-1: Query Tag (ST) (optional)
    $data[] = $this->patient->_query_tag;
    
    // QID-2: Message Query Name (CE) (optional)
    $data[] = "IHE PDQ Query";

    $this->fill($data);
  }
}