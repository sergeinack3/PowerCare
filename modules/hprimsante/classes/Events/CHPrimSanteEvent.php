<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante\Events;

use Ox\Core\CApp;
use Ox\Core\CClassMap;
use Ox\Core\CMbDT;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7v2Error;
use Ox\Interop\Hprimsante\CExchangeHprimSante;
use Ox\Interop\Hprimsante\CHPrimSanteMessage;
use Ox\Interop\Hprimsante\CHPrimSanteSegment;
use Ox\Interop\Hprimsante\CReceiverHprimSante;
use Ox\Interop\Hprimsante\Segments\CHPrimSanteSegmentP;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHPrimSanteEvent
 * Event H'sante
 */
class CHPrimSanteEvent extends CHEvent {
  public $type;
  public $type_liaison;

  /** @var CHPrimSanteMessage */
  public $message;

  public $msg_hpr;

  /** @var CReceiverHprimSante */
  public $_receiver;

  /** @var CInteropSender */
  public $_sender;

  public $_exchange_hpr;

  /**
   * @inheritdoc
   */
  function build($object) {
    // Traitement sur le mbObject
    $this->object       = $object;
    $this->last_log     = $object->loadLastLog();
    $this->version      = "H".$this->_receiver->_configs["{$this->type}_version"];
    $this->type_liaison = $this->_receiver->_configs["{$this->type}_sous_type"];

    // Génération de l'échange
    $this->generateExchange();

    // Création du message HL7
    $message = new CHPrimSanteMessage($this->version);
    $message->name = $this->msg_codes;

    $this->message = $message;

    $this->addH();
  }

  /**
   * Create a header segment
   *
   * @return void
   */
  function addH() {
    $H = CHPrimSanteSegment::create("H", $this->message);
    $H->build($this);
  }

  /**
   * Create a patient segment
   *
   * @param CPatient $patient patient
   * @param CSejour  $sejour  sejour
   *
   * @return void
   */
  function addP($patient, $sejour = null) {
    /** @var CHPrimSanteSegmentP $P */
    $P = CHPrimSanteSegment::create("P", $this->message);
    $P->patient = $patient;
    $P->sejour  = $sejour;
    $P->build($this);
  }

  /**
   * Create a end segment
   *
   * @return void
   */
  function addL() {
    $L = CHPrimSanteSegment::create("L", $this->message);
    $L->build($this);
  }

  /**
   * @inheritdoc
   */
  function handle($msg_hpr) {
    $this->message = new CHPrimSanteMessage();

    $this->message->parse($msg_hpr);

    return $this->message->toXML(CClassMap::getSN($this), true, CApp::$encoding);
  }

  /**
   * Get the message as a string
   *
   * @return string
   */
  function flatten() {
    $this->msg_hpr = $this->message->flatten();

    $this->message->validate();

    $this->updateExchange();

    return $this->msg_hpr;
  }

  /**
   * Generate exchange HPrim santé
   *
   * @return CExchangeHprimSante
   */
  function generateExchange() {
    $exchange_hpr                  = $this->_exchange_hpr ? $this->_exchange_hpr : new CExchangeHprimSante();
    $exchange_hpr->date_production = CMbDT::dateTime();
    $exchange_hpr->receiver_id     = $this->_receiver->_id;
    $exchange_hpr->group_id        = $this->_receiver->group_id;
    $exchange_hpr->sender_id       = $this->_sender ? $this->_sender->_id : null;
    $exchange_hpr->sender_class    = $this->_sender ? $this->_sender->_id : null;
    $exchange_hpr->version         = $this->version;
    $exchange_hpr->type            = $this->type;
    $exchange_hpr->sous_type       = $this->type_liaison;
    $exchange_hpr->object_id       = $this->object->_id;
    $exchange_hpr->object_class    = $this->object->_class;
    $exchange_hpr->store();

    return $this->_exchange_hpr = $exchange_hpr;
  }

  /**
   * Update exchange HPrim sante
   *
   * @return CExchangeHprimSante
   */
  function updateExchange() {
    /** @var CExchangeHprimSante $exchange_hpr */
    $exchange_hpr                 = $this->_exchange_hpr;

    $exchange_hpr->_message       = $this->msg_hpr;
    $exchange_hpr->message_valide = $this->message->isOK(CHL7v2Error::E_ERROR) ? 1 : 0;

    $exchange_hpr->store();

    return $exchange_hpr;
  }

  /**
   * Get event class
   *
   * @param self $event Event object
   *
   * @return string
   */
  static function getEventClass($event) {
    return "CHPrimSante".$event->type.$event->type_liaison;
  }

  /**
   * Get event object
   *
   * @param String $message_name name message
   *
   * @return self
   */
  static function getEvent($message_name) {
    $event_class = "CHPrimSante{$message_name}";

    return new $event_class;
  }
}

