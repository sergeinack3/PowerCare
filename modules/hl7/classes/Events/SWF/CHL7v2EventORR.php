<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\SWF;

use Ox\Interop\Hl7\CHL7Acknowledgment;
use Ox\Interop\Hl7\CHL7v2Error;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\Events\CHL7Event;
use Ox\Interop\Hl7\Events\CHL7v2Event;

/**
 * Classe CHL7v2EventORR
 * Represents a ORR message structure (see chapter 2.14.1)
 */
class CHL7v2EventORR extends CHL7v2Event implements CHL7EventORR {
  /**
   * Construct
   *
   * @param CHL7Event $trigger_event Trigger event
   *
   * @return CHL7v2EventORR
   */
  function __construct(CHL7Event $trigger_event) {
    $this->profil      = "SWF";

    $this->event_type  = "ORR";
    $this->version     = $trigger_event->message->version;

    $this->code        = "O02";

    $this->msg_codes   = array (
      array(
        $this->event_type, $this->code, "{$this->event_type}_{$this->code}"
      )
    );

    $this->_exchange_hl7v2 = $trigger_event->_exchange_hl7v2;
    $this->_receiver       = $trigger_event->_exchange_hl7v2->_ref_receiver;
    $this->_sender         = $trigger_event->_exchange_hl7v2->_ref_sender;
  }

  /**
   * Build
   *
   * @param CHL7Acknowledgment $object Object
   *
   * @return void
   */
  function build($object) {
    // Création du message HL7
    $this->message          = new CHL7v2Message();
    $this->message->version = $this->version;
    $this->message->name    = $this->msg_codes;

    $message      = $this->_exchange_hl7v2->_message;

    $hl7_message_initiator = new CHL7v2Message();
    $hl7_message_initiator->parse($message);

    $this->message->_hl7_message_initiator = $hl7_message_initiator;

    // Message Header 
    $this->addMSH();
    
    // Message Acknowledgment
    $this->addMSA($object);

    // Error
    $trigger_event = $object->event;
    // Validation error
    if ($errors = $trigger_event->message->errors) {
      foreach ($errors as $_error) {
        $this->addERR($object, $_error);
      }
    }

    // ORC

    // OBR
  }

  /**
   * Get the message as a string
   *
   * @return string
   */
  function flatten() {
    $this->msg_hl7 = $this->message->flatten();
    $this->message->validate();
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
   * MSA - Represents an HL7 MSA message segment (Message Acknowledgment)
   *
   * @param CHL7Acknowledgment $acknowledgment Acknowledgment
   *
   * @return void
   */
  function addMSA(CHL7Acknowledgment $acknowledgment) {
    $MSA = CHL7v2Segment::create("MSA", $this->message);
    $MSA->acknowledgment = $acknowledgment;
    $MSA->build($this);
  }

  /**
   * ERR - Represents an HL7 ERR message segment (Error)
   *
   * @param CHL7Acknowledgment $acknowledgment Acknowledgment
   * @param CHL7v2Error|null   $error          Error HL7
   *
   * @return void
   */
  function addERR(CHL7Acknowledgment $acknowledgment, $error = null) {
    $ERR = CHL7v2Segment::create("ERR", $this->message);
    $ERR->acknowledgment = $acknowledgment;
    $ERR->error = $error;
    $ERR->build($this);
  }
}