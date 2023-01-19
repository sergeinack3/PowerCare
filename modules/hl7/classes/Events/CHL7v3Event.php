<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events;

use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Interop\Hl7\CExchangeHL7v3;
use Ox\Interop\Hl7\CHL7v3MessageXML;

/**
 * Class CHL7v3Event
 * Event HL7v3
 */
class CHL7v3Event extends CHL7Event {
  /** @var  CHL7v3MessageXML $dom */
  public $dom;

  /** @var string */
  public $interaction_id = null;
  public $version        = "2009";

  /** @var string */
  public $_event_name;

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
    // Traitement sur le mbObject
    $this->object   = $object;
    $this->last_log = $object->loadLastLog();

    // Génération de l'échange
    if (!isset($object->_not_generate_exchange) ||  (isset($object->_not_generate_exchange) && $object->_not_generate_exchange === false)) {
      $this->generateExchange();
    }

    // Dans le cas où l'on charge un message XML HL7v3 standard
    if (!$this->dom) {
      $this->dom = new CHL7v3MessageXML("utf-8", $this->version);
    }
  }

  /**
   * Generate exchange HL7v3
   *
   * @return CExchangeHL7v3
   */
  function generateExchange() {
    $exchange_hl7v3                  = new CExchangeHL7v3();
    $exchange_hl7v3->date_production = CMbDT::dateTime();
    $exchange_hl7v3->receiver_id     = $this->_receiver->_id;
    $exchange_hl7v3->group_id        = $this->_receiver->group_id;
    $exchange_hl7v3->sender_id       = $this->_sender ? $this->_sender->_id : null;
    $exchange_hl7v3->sender_class    = $this->_sender ? $this->_sender->_id : null;
    $exchange_hl7v3->type            = $this->event_type;
    $exchange_hl7v3->sous_type       = $this->interaction_id;
    $exchange_hl7v3->object_id       = $this->object->_id;
    $exchange_hl7v3->object_class    = $this->object->_class;
    $exchange_hl7v3->store();

    return $this->_exchange_hl7v3 = $exchange_hl7v3;
  }

  /**
   * Update exchange HL7v3 with
   *
   * @param Bool $validate Validate message
   *
   * @return CExchangeHL7v3
   */
  function updateExchange($validate = true) {
    $exchange_hl7v3                 = $this->_exchange_hl7v3;
    $exchange_hl7v3->_message       = $this->message;
    $exchange_hl7v3->message_valide = true;
    $exchange_hl7v3->store();

    return $exchange_hl7v3;
  }

  /**
   * @inheritdoc
   */
  function handle($msg_hl7) {
    $this->dom = new CHL7v3MessageXML("utf-8");
    $this->dom->loadXML($msg_hl7);

    return $this->dom;
  }
}
