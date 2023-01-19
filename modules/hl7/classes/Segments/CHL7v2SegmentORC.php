<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Segments;

use Ox\AppFine\Client\CAppFineClient;
use Ox\AppFine\Client\CAppFineClientOrderItem;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;

/**
 * Class CHL7v2SegmentORC
 * ORC - Represents an HL7 ORC message segment (Common Order)
 */
class CHL7v2SegmentORC extends CHL7v2Segment {

  /** @var string */
  public $name = "ORC";

  public $object;

  /**
   * BuildORC segement
   *
   * @param CHEvent $event Event
   * @param string  $name  Segment name
   *
   * @return void
   * @throws CHL7v2Exception
   */
  function build(CHEvent $event, $name = null) {
    parent::build($event);
    /** @var CConsultation $object */
    $object = $this->object;

    if (CModule::getActive("appFineClient") && $object instanceof CAppFineClientOrderItem) {
      return $this->fill(CAppFineClient::generateSegmentORC($object));
    }

    // ORC-1: Order Control (ID)
    //NW - add; CA - delete; XO - modify;
    $log  = $object->_ref_last_log;
    $orc5 = "SC";

    switch ($log->type) {
      case "create":
        $orc1 = "NW";
        break;
      case "store":
        $orc1 = "XO";
        if (!$object instanceof CPrescriptionLineElement && $object->fieldModified("annule", "1")) {
          $orc5 = "CA";
          $orc1 = "CA";
        }
        if (!$object instanceof CPrescriptionLineElement && $object->fieldModified("annule", "0")) {
          $orc1 = "NW";
        }
        break;
      case "delete":
        $orc5 = "CA";
        $orc1 = "CA";
        break;
      default:
        $orc1 = null;
    }

    $data[] = $orc1;

    // ORC-2: Placer Order Number (EI)
    $data[] = $object->_id;

    // ORC-3: Filler Order Number (EI) (optional)
    $data[] = null;

    // ORC-4: Placer Group Number (EI) (optional)
    $data[] = null;

    // ORC-5: Order Status (ID) (optional table 0038)
    $data[] = $orc5;

    // ORC-6: Response Flag (ID) (optional table 0121)
    $data[] = null;

    // ORC-7: Quantity/Timing (TQ)
    $datetime = null;
    $quantity = "1";
    if ($object instanceof CSejour) {
      $datetime = $object->entree;
    }
    if ($object instanceof CConsultation) {
      $datetime = $object->_datetime;
    }
    if ($object instanceof CPrescriptionLineElement) {
      $datetime = $object->_debut_reel;
    }

    $data[] = array(
      array(
        $quantity,
        null,
        null,
        $datetime
      )
    );

    // ORC-8: Parent (CM) (optional)
    //shall be valued only if the current order is a child order (i.e., if the field ORC 1 Order Control has a value of CH).
    $data[] = null;

    // ORC-9: date/time od Transaction (TS)
    $data[] = CMbDT::dateTime();

    // ORC-10: Entered By (XCN) (optional)
    $data[] = null;

    // ORC-11: Verified By (XCN) (optional)
    $data[] = null;

    // ORC-12: Ordering Provider (XCN)
    $data[] = $this->getXCN($object->_ref_praticien, $event->_receiver);

    // ORC-13: Enterer's Location (PL) (optional)
    $data[] = null;

    // ORC-14: Call Back Phone Number (XTN) (optional repeating)
    $data[] = null;

    // ORC-15: Order Effective Date/Time (TS) (optional)
    $data[] = null;

    // ORC-16: Order Control Code reason (CE) (optional)
    $data[] = null;

    // ORC-17: Entering Organization (CE)
    $group  = $event->_receiver->_ref_group;
    $data[] = array(
      array(
        $group->_id,
        $group->raison_sociale,
      )
    );

    // ORC-18: Entering Device (CE) (optional)
    $data[] = null;

    // ORC-19: Action By (XCN) (optional)
    $data[] = null;

    $this->fill($data);
  }
}
