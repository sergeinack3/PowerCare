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
 * Class CHL7v2SegmentQPD
 * QPD - Represents an HL7 QPD message segment (Query Parameter Definition)
 */

class CHL7v2SegmentQPD_RESP extends CHL7v2Segment {
  public $name   = "QPD_RESP";

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

    $hl7_message_initiator = $event->message->_hl7_message_initiator;

    /** @var CHL7v2SegmentQPD $QPD_request */
    $QPD_request           = $hl7_message_initiator->getSegmentByName("QPD");

    $this->fill($QPD_request->getStruct());
  }
}