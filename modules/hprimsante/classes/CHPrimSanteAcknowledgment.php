<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante;

use DOMDocument;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbObject;
use Ox\Interop\Hprimsante\Events\CHPrimSanteEvent;
use Ox\Interop\Hprimsante\Events\CHPrimSanteEventERR;

/**
 * Class CHPrimSanteAcknowledgment
 * Acknowledgment HPR
 */
class CHPrimSanteAcknowledgment implements IShortNameAutoloadable {
  public $event;
  public $event_err;

  /** @var  CHPrimSanteMessage */
  public $message;
  /** @var  CHPrimSanteMessageXML */
  public $dom_message;

  public $message_control_id;
  public $ack_code;
  public $errors;
  public $object;

  public $_ref_exchange_hpr;
  /** @var  CHPrimSanteError */
  public $_error;
  public $_row;
  public $_type;
  public $_node;

  /**
   * construct
   *
   * @param CHPrimSanteEvent $event event
   */
  function __construct(CHPrimSanteEvent $event = null) {
    $this->event = $event;
  }

  /**
   * handle
   *
   * @param String $ack_hpr ack message
   *
   * @return DOMDocument
   */
  function handle($ack_hpr) {
    $this->message = new CHPrimSanteMessage();
    $this->message->parse($ack_hpr);
    $this->dom_message = $this->message->toXML();

    return $this->dom_message;
  }

  /**
   * generate acknowledgment
   *
   * @param CHPrimSanteError[] $errors errors
   * @param CMbObject          $object object
   *
   * @return $this
   */
  function generateAcknowledgment($errors, CMbObject $object = null) {
    $this->errors               = $errors;
    $this->object               = $object;
    $this->event->_exchange_hpr = $this->_ref_exchange_hpr;

    $this->event_err = new CHPrimSanteEventERR($this->event);
    $this->event_err->build($this);
    $this->event_err->flatten();
    $this->ack_code = $this->event_err->_exchange_hpr->statut_acquittement;

    return $this;
  }

  /**
   * get status of Acknowledgment
   *
   * @return String
   */
  function getStatutAcknowledgment() {
    $xpath = new CHPrimSanteMessageXPath($this->dom_message);
    $nodes = $xpath->query("//ERR/ERR.4/CE.1[text()='T']");
    if ($nodes->length > 0) {
      return "T";
    }
    $nodes = $xpath->query("//ERR/ERR.4/CE.1[text()='P']");
    if ($nodes->length > 0) {
      return "P";
    }

    return "OK";
  }
}
