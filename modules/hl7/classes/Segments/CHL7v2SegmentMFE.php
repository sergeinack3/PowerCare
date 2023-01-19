<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\Core\CClassMap;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\Events\MFN\CHL7v2EventMFN;

/**
 * Class CHL7v2SegmentMFE
 * MFE - Represents an HL7 MFE message segment (Identifie chaque entité de la structure)
 */
class CHL7v2SegmentMFE extends CHL7v2Segment {

  /** @var string */
  public $name = "MFE";

  public $entity;
  /**
   * Build MFE segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);

    $entity = $this->entity;
    $primary_key = array_search(CClassMap::getSN($entity), CHL7v2EventMFN::$entities);
    $primary_key = $primary_key.$entity->_id;

    $data = array();

    // MFE-1: Record-Level Event Code - MFE (ID) (Requis)
    $data[] = "MAD";

    // MFE-2: MFN Control ID - MFE (ST) (Conditional)
    $data[] = $event->_exchange_hl7v2->_id;

    // MFE-3: Effective Date/Time - MFE (TS) (Optional)
    $data[] = null;

    // MFE-4: Primary Key Value - MFE - MFE (PL) (Requis)
    $data[] = $primary_key;

    // MFE-5: Primary Key Value Type - MFE (ID) (Requis)
    $data[] = "PL";

    $this->fill($data);
  }
}