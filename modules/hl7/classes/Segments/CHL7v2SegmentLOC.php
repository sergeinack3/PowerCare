<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\Core\CClassMap;
use Ox\Core\CEntity;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\Events\MFN\CHL7v2EventMFN;

/**
 * Class CHL7v2SegmentLOC
 * LOC - Represents an HL7 LOC message segment ()
 */
class CHL7v2SegmentLOC extends CHL7v2Segment {

  /** @var string */
  public $name = "LOC";

  /** @var CEntity */
  public $entity;

  /**
   * Build LOC segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return null
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);

    $entity = $this->entity;
    $localisation_type = array_search(CClassMap::getSN($entity), CHL7v2EventMFN::$entities);
    $primary_key = $localisation_type.$entity->_id;

    // LOC-1: Primary Key Value-LOC - LOC (PL) (Requis)
    $data[] = $primary_key;

    // LOC-2: Location Description - LOC (ST) (Optional)
    $data[] = null;

    // LOC-3: Location Type - LOC (IS) (Requis)
    $data[] = $localisation_type;

    // LOC-4: Organization Name - LOC (XON) (Optional)
    $data[] = $entity->_name;

    // LOC-5: Location Address - LOC (XAD) (Optional)
    $data[] = null;

    // LOC-6: Location Phone - LOC (XTN) (Optional)
    $data[] = null;

    // LOC-7: License Number - LOC (CE) (Optional)
    $data[] = null;

    // LOC-8: Location Equipment - LOC (IS) (Optional)
    $data[] = null;

    // LOC-9: Location Service Code - LOC (IS) (Optional)
    $data[] = null;

    $this->fill($data);
  }
}