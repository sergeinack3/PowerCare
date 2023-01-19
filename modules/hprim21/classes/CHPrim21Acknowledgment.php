<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Interop\Hprim21\Events\CHPREvent;
use Ox\Interop\Hprim21\Events\CHPREventERR;

/**
 * Class CHPrim21Acknowledgment 
 * Acknowledgment HPR
 */
class CHPrim21Acknowledgment implements IShortNameAutoloadable {
  public $event;
  public $event_err;
  
  public $message;
  public $dom_message;
  
  public $message_control_id;
  public $ack_code;
  public $errors;
  public $object;
  
  public $_ref_exchange_hpr;
  public $_errors;
  public $_row;
    
  function __construct(CHPREvent $event = null) {
    $this->event = $event;
  }

  function handle($ack_hpr) {
    $this->message = new CHPrim21Message();
    $this->message->parse($ack_hpr);
    $this->dom_message = $this->message->toXML();
    
    return $this->dom_message;
  }
  
  function generateAcknowledgment($ack_code, $errors, $object = null) {
    $this->ack_code = $ack_code;
    $this->errors   = $errors;
    $this->object   = $object;

    $this->event->_exchange_hpr = $this->_ref_exchange_hpr;
    $this->event_err = new CHPREventERR($this->event);
    $this->event_err->build($this);
    $this->event_err->flatten();
  
    $this->event_err->msg_hpr = utf8_encode($this->event_err->msg_hpr);
    
    return $this->event_err->msg_hpr;
  }
  
  function getStatutAcknowledgment() {
    
  }
}
