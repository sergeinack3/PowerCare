<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\QBP;

use Ox\Core\CMbObject;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\Events\CHL7v2Event;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentQID;
use Ox\Interop\Ihe\CPDQ;
use Ox\Mediboard\Patients\CPatient;

/**
 * Class CHL7v2EventQCN
 * Patient Demographics Query Cancel Query
 */
class CHL7v2EventQCN extends CHL7v2Event implements CHL7EventQCN {

  /** @var string */
  public $event_type = "QCN";

  /**
   * Construct
   *
   * @return CHL7v2EventQCN
   */
  function __construct() {
    parent::__construct();

    $this->profil      = "PDQ";
    $this->msg_codes   = array (
      array(
        $this->event_type, $this->code, "{$this->event_type}_{$this->code}"
      )
    );

    $this->transaction = CPDQ::getPDQTransaction($this->code);
  }

  /**
   * Build event
   *
   * @param CMbObject $object Object
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($object) {
    parent::build($object);

    $this->addMSH($object);
  }

  /**
   * MSH - Represents an HL7 MSH message segment (Message Header)
   *
   * @return void
   */
  function addMSH() {
    $MSH = CHL7v2Segment::create("MSH", $this->message);
    $MSH->build($this);
  }

  /**
   * QID - Represents an HL7 Query Identification Segment
   *
   * @param CPatient $patient
   *
   * @return void
   */
  function addQID(CPatient $patient) {
    /** @var CHL7v2SegmentQID $QID */
    $QID = CHL7v2Segment::create("QID", $this->message);
    $QID->patient = $patient;
    $QID->build($this);
  }


}