<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\SWF;

use Ox\Core\CMbObject;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\Events\CHL7v2Event;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentMSH;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentOBR;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentOBX;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentORC;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentPID;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentPV1;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentZDS;
use Ox\Interop\Ihe\CIHE;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Mpm\CPrisePosologie;

/**
 * Classe CHL7v2EventORM
 * Order Message
 */
class CHL7v2EventORM extends CHL7v2Event implements CHL7EventORM {

  /** @var string */
  public $event_type = "ORM";

  /**
   * Construct
   *
   */
  function __construct() {
    parent::__construct();
    
    $this->profil    = "SWF";
    $this->msg_codes = array ( 
      array(
        $this->event_type, $this->code, "{$this->event_type}_{$this->code}"
      )
    );
    $this->transaction = CIHE::getSWFTransaction($this->code);
  }

  /**
   * Build event
   *
   * @param CMbObject $object Object
   *
   * @see parent::build()
   *
   * @return void
   * @throws CHL7v2Exception
   */
  function build($object) {
    parent::build($object);
        
    // Message Header 
    $this->addMSH();
  }

  /**
   * MSH - Represents an HL7 MSH message segment (Message Header)
   *
   * @return void
   * @throws CHL7v2Exception
   */
  function addMSH() {
    /** @var CHL7v2SegmentMSH $MSH */
    $MSH = CHL7v2Segment::create("MSH", $this->message);
    $MSH->build($this);
  }

  /**
   * Represents an HL7 PID message segment (Patient Identification)
   *
   * @param CPatient $patient Patient
   * @param CSejour  $sejour  Admit
   *
   * @return void
   */
  function addPID(CPatient $patient, CSejour $sejour = null) {
    /** @var CHL7v2SegmentPID $PID */
    $segment_name = $this->_is_i18n ? "PID_FR" : "PID";
    $PID = CHL7v2Segment::create($segment_name, $this->message);
    $PID->patient = $patient;
    $PID->sejour = $sejour;
    $PID->set_id  = 1;
    $PID->build($this);
  }

  /**
   * Represents an HL7 PV1 message segment (Patient Visit)
   *
   * @param CSejour $sejour Admit
   * @param int     $set_id Set ID
   *
   * @return void
   */
  function addPV1(CSejour $sejour = null, $set_id = 1) {
    /** @var CHL7v2SegmentPV1 $PV1 */
    $segment_name = $this->_is_i18n ? "PV1_FR" : "PV1";
    $PV1          = CHL7v2Segment::create($segment_name, $this->message);
    $PV1->sejour  = $sejour;
    $PV1->set_id  = $set_id;
    if ($sejour) {
      $PV1->curr_affectation = $sejour->_ref_hl7_affectation;
    }
    $PV1->build($this);
  }

  /**
   * Represents an HL7 ORC message segment (Common order)
   *
   * @param CMbObject $object object
   *
   * @return void
   * @throws CHL7v2Exception
   */
  function addORC($object) {
    /** @var CHL7v2SegmentORC $ORC */
    $ORC = CHL7v2Segment::create("ORC", $this->message);
    $ORC->object = $object;
    $ORC->build($this);
  }

  /**
   * Represents an HL7 OBR message segment (Observation Request)
   *
   * @param CMbObject       $object     object
   * @param CPrisePosologie $prise_poso Prise posologie
   * @param int             $set_id     Set ID
   *
   * @return void
   * @throws CHL7v2Exception
   */
  function addOBR($object, $prise_poso = null, $set_id = 1) {
    /** @var CHL7v2SegmentOBR $OBR */
    $OBR = CHL7v2Segment::create("OBR", $this->message);
    $OBR->object     = $object;
    $OBR->prise_poso = $prise_poso;
    $OBR->set_id     = $set_id;
    $OBR->ordering_provider = null;
    $OBR->build($this);
  }

  /**
   * OBX - Represents an HL7 OBX message segment
   *
   * @param CMbObject $object object
   *
   * @return void
   */
  function addOBX(CMbObject $object) {
    /** @var CHL7v2SegmentOBX $OBX */
    $OBX         = CHL7v2Segment::create("OBX", $this->message);
    $OBX->object = $object;
    $OBX->build($this);
  }

  /**
   * Represents an HL7 ZDS message segment ()
   *
   * @return void
   */
  function addZDS() {
    /** @var CHL7v2SegmentZDS $ZDS */
    $ZDS = CHL7v2Segment::create("ZDS", $this->message);
    $ZDS->build($this);
  }
}
