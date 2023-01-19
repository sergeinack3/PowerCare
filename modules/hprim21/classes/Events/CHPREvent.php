<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21\Events;

use Ox\Core\CApp;
use Ox\Core\CClassMap;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hprim21\CDestinataireHprim21;
use Ox\Interop\Hprim21\CHPrim21Message;

/**
 * Class CHPREvent 
 * Event H'2.1
 */
class CHPREvent extends CHEvent {
  public $type;
  public $type_liaison;
  
  /** @var CHPrim21Message */
  public $message;
  
  public $msg_hpr;
  
  /** @var CDestinataireHprim21 */
  public $_receiver;
  
  /** @var CInteropSender */
  public $_sender;
  
  public $_exchange_hpr;

  /**
   * @inheritdoc
   */
  function build($object) {
  }

  /**
   * @inheritdoc
   */
  function handle($msg) {
    $this->message = new CHPrim21Message();
    
    $this->message->parse($msg);
    
    return $this->message->toXML(CClassMap::getSN($this), false, CApp::$encoding);
  }

  /**
   * Get event class
   *
   * @param self $event Event object
   *
   * @return string
   */
  static function getEventClass($event) {
    return "CHPrim21".$event->type.$event->type_liaison;
  }

  /**
   * Get event object
   *
   * @param String $message_name name message
   *
   * @return mixed
   */
  static function getEvent($message_name) {
    $event_class = "CHPrim21{$message_name}";

    return new $event_class;
  }
}

