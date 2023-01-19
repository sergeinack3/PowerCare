<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante\Events;

use Ox\Interop\Hl7\CHL7v2SegmentGroup;
use Ox\Interop\Hprimsante\CHPrimSanteAcknowledgment;
use Ox\Interop\Hprimsante\CHPrimSanteError;
use Ox\Interop\Hprimsante\CHPrimSanteMessage;

/**
 * Classe CHPREventERR
 * Represents a ERR message structure (see chapter 2.14.1)
 */
class CHPrimSanteEventERR extends CHPrimSanteEvent {
  /**
   * construct
   *
   * @param CHPrimSanteEvent $trigger_event trigger event
   */
  function __construct(CHPrimSanteEvent $trigger_event) {
    $this->event_type  = "ERR";
    $this->type        = "ERR";
    $this->version     = $trigger_event->message->version;
    $this->type_liaison = $trigger_event->type_liaison;
    $this->msg_codes   = array (
      array(
        $trigger_event->type, $trigger_event->type_liaison
      )
    );

    $this->_exchange_hpr = $trigger_event->_exchange_hpr;
    $this->_receiver     = $trigger_event->_exchange_hpr->_ref_receiver;
    $this->_sender       = $trigger_event->_exchange_hpr->_ref_sender;
  }

  /**
   * @see parent::build
   */
  function build($object) {
    // Création du message HPR
    $this->message          = new CHPrimSanteMessage();
    $this->message->version = $this->version;
    $this->message->name    = $this->event_type;

    // Message Header
    $this->addH();

    // Errors
    foreach ($object->errors as $_error) {

      $object->_error = $_error;

      if ($_error->type_error == "T" && $this->_exchange_hpr->statut_acquittement != "T") {
        $this->_exchange_hpr->statut_acquittement = $_error->type_error;
      }
      else if ($_error->type_error == "P") {
        $this->_exchange_hpr->statut_acquittement = $_error->type_error;
      }

      $this->addERR($object);
    }

    // Validation error

    // Message Footer
    $this->addL();
  }

  /**
   * create the segment
   *
   * @param String             $name   name
   * @param CHL7v2SegmentGroup $parent parent
   *
   * @return CHPrimSanteEventERR
   */
  function createSegment($name, CHL7v2SegmentGroup $parent) {
    $class = "CHPrimSanteSegment$name";
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
   * @param CHPrimSanteAcknowledgment $acknowledgment acknowledgment
   * @param CHPrimSanteError          $error          error
   *
   * @return void
   */
  function addERR(CHPrimSanteAcknowledgment $acknowledgment, $error = null) {
    $ERR = $this->createSegment("ERR", $this->message);
    $ERR->acknowledgment = $acknowledgment;
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

  /**
   * flatten
   *
   * @return void
   */
  function flatten() {
    $this->msg_hpr = $this->message->flatten();
    $this->message->validate();
  }
}

