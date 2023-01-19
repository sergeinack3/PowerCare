<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\Core\CMbDT;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Segment;

/**
 * Class CHL7v2SegmentMFI
 * MFI - Represents an HL7 MFI message segment (Identifie l'ensemble du catalogue)
 */

class CHL7v2SegmentMFI extends CHL7v2Segment {
  /** @var string */
  public $name   = "MFI";

  /**
   * Build MFI segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);

    // MFI-1: Master File Identifier - MFI (CE) (Requis)
    $data[] = "LOC";

    // MFI-2: Master File Application Identifier - MFI (HD) (Requis)
    $data[] = "Mediboard_LOC_FRA";

    // MFI-3: File-Level Event Code - MFI (ID) (Requis)
    $data[] = "REP";

    // MFI-4: Enterd Date/Time - MFI (TS) (Optional)
    $data[] = null;

    // MFI-5: Effective Date/Time - MFI (TS) (Requis)
    $data[] = CMbDT::dateTime();

    // MFI-6: Response Level Code - MFI (ID) (Requis)
    $data[] = "AL";

    $this->fill($data);
  }
}