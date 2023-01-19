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

/**
 * Class CHL7v2SegmentQAK
 * QAK - Represents an HL7 QAK message segment (Query Parameter Definition)
 */

class CHL7v2SegmentQAK extends CHL7v2Segment {

  /** @var string */
  public $name   = "QAK";


  /** @var array */
  public $objects;

  /**
   * Build QPD segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);

    $objects = $this->objects;

    // QAK-1: Query Tag (ST) (optional)
    $hl7_message_initiator = $event->message->_hl7_message_initiator;
    $QPD_request           = $hl7_message_initiator->getSegmentByName("QPD")->getStruct();

    $data[] = isset($QPD_request[1][0]) ? $QPD_request[1][0] : "PDQPDC_$event->code";

    // QAK-2: Query Response Status (ID) (optional)
    if ($objects == null) {
      $data[] = "AE";
    }
    elseif (count($objects) == 0) {
      $data[] = "NF";
    }
    else {
      $data[] = "OK";
    }

    // QAK-3: User Parameters (in successive fields) (Varies) (optional)
    $data[] = null;

    // QAK-4: Hit Count (NM) (optional)
    $data[] = null;

    // QAK-5: This payload (NM) (optional)
    $data[] = null;

    // QAK-6: Hits remaining (NM) (optional)
    $data[] = null;

    $this->fill($data);
  }
}