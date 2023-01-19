<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21\Events;

use Ox\Interop\Hl7\CHL7v2SegmentGroup;
use Ox\Interop\Hprim21\CHPrim21Acknowledgment;
use Ox\Interop\Hprim21\CHPrim21Message;

/**
 * Classe CHPREventERR
 * Represents a ERR message structure (see chapter 2.14.1)
 */
class CHPREventERR extends CHPREvent {
  function __construct(CHPREvent $trigger_event) { 
    $this->event_type  = "ERR";
    $this->version     = $trigger_event->message->version;
    
    $this->msg_codes   = array ( 
      array(
        $trigger_event->type, $trigger_event->type_liaison
      )
    );

    $this->_exchange_hpr = $trigger_event->_exchange_hpr;
    $this->_receiver     = $trigger_event->_exchange_hpr->_ref_receiver;
    $this->_sender       = $trigger_event->_exchange_hpr->_ref_sender;
  }
  
  function build($object) {
    // Création du message HPR
    $this->message          = new CHPrim21Message();
    $this->message->version = $this->version;
    $this->message->name    = $this->event_type;
    
    // Message Header 
    $this->addH();
    
    $i = 0;
    // Errors
    foreach ($object->errors as $_error) {
      $object->_error = $_error;
      $object->_row   = ++$i;
      
      $this->addERR($object);
    }
    
    // Validation error
    
    
    // Message Footer 
    $this->addL();
  }
  
  function createSegment($name, CHL7v2SegmentGroup $parent) {
    $class = "CHPRSegment$name";
      $segment = new $class($parent);
      $segment->name = substr($name, 0, 3);
    return $segment;
  }
  
  /**
   * H - Represents an HPR H message segment (Message Header)
   *
   * @return void
   */
  function addH() {
    $H = $this->createSegment("H", $this->message);
    $H->build($this);
  }

  /**
   * ERR - Represents an HPR ERR message segment (Error)
   *
   * @return void
   */
  function addERR(CHPrim21Acknowledgment $acknowledgment, $error = null) {
    $ERR = $this->createSegment("ERR", $this->message);
    $ERR->acknowledgment = $acknowledgment;
    $ERR->error = $error ? $error : $acknowledgment->_error;
    $ERR->build($this);
  }
  
  /**
   * L - Represents an HPR L message segment (Message Footer)
   *
   * @return void
   */
  function addL() {
    $L = $this->createSegment("L", $this->message);
    $L->build($this);
  }
  
  function flatten() {
    $this->msg_hpr = $this->message->flatten();
    $this->message->validate();
  }
}

