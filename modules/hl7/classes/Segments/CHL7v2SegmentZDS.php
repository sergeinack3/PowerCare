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
 * Class CHL7v2SegmentZDS
 * ZDS - Represents an HL7 ZDS message segment (Study Instance UID)
 */
class CHL7v2SegmentZDS extends CHL7v2Segment {

  /** @var string */
  public $name = "ZDS";

  /**
   * BuildORC segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return void
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);

    // ZDS-1: Study Instance UID (RP)
    // 1 reference pointer^2 Application ID^3 Type of Data^4 Subtype
    $data[] = null;

    $this->fill($data);
  }
}